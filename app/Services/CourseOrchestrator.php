<?php

namespace App\Services;

use App\Models\MiniCourse;
use App\Models\MiniCourseStep;
use App\Models\Resource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CourseOrchestrator
{
    public function __construct(
        protected ClaudeService $claudeService,
        protected VectorSearchService $vectorSearchService
    ) {}

    /**
     * Generate a personalized mini-course from existing resources.
     *
     * @param  array  $params  {
     *     topic: string,
     *     orgId: int,
     *     targetGrades?: array,
     *     targetRiskLevels?: array,
     *     targetDurationMinutes?: int,
     *     courseType?: string,
     *     createdBy?: int,
     * }
     */
    public function generateCourse(array $params): MiniCourse
    {
        $topic = $params['topic'];
        $orgId = $params['orgId'];
        $targetGrades = $params['targetGrades'] ?? [];
        $targetRiskLevels = $params['targetRiskLevels'] ?? [];
        $targetDuration = $params['targetDurationMinutes'] ?? 30;
        $courseType = $params['courseType'] ?? MiniCourse::TYPE_SKILL_BUILDING;
        $createdBy = $params['createdBy'] ?? auth()->id();

        // Step 1: Find relevant resources using vector search (RAG)
        $relevantResources = $this->vectorSearchService->findRelevantResources(
            topic: $topic,
            orgIds: [$orgId],
            limit: 15,
            minSimilarity: 0.35
        );

        // Step 2: Build context from resources for Claude
        $resourceContext = $this->buildResourceContext($relevantResources);

        // Step 3: Generate course structure using Claude
        $courseStructure = $this->generateCourseStructure(
            topic: $topic,
            resourceContext: $resourceContext,
            targetGrades: $targetGrades,
            targetDuration: $targetDuration,
            courseType: $courseType
        );

        // Step 4: Create the course in the database
        return $this->createCourseFromStructure(
            structure: $courseStructure,
            params: $params,
            relevantResources: $relevantResources,
            createdBy: $createdBy
        );
    }

    /**
     * Build context string from relevant resources for RAG.
     */
    protected function buildResourceContext(Collection $resources): string
    {
        if ($resources->isEmpty()) {
            return 'No existing resources found. Generate original content.';
        }

        $context = "Available resources that can be referenced in the course:\n\n";

        foreach ($resources->take(10) as $index => $resource) {
            $similarity = $resource->similarity ?? 0;
            $context .= sprintf(
                "[Resource %d] ID: %d (Relevance: %d%%)\n".
                "Title: %s\n".
                "Type: %s\n".
                "Description: %s\n".
                "Duration: %d minutes\n\n",
                $index + 1,
                $resource->id,
                round($similarity * 100),
                $resource->title,
                $resource->resource_type,
                Str::limit($resource->description, 200),
                $resource->estimated_duration_minutes ?? 5
            );
        }

        return $context;
    }

    /**
     * Generate course structure using Claude AI.
     */
    protected function generateCourseStructure(
        string $topic,
        string $resourceContext,
        array $targetGrades,
        int $targetDuration,
        string $courseType
    ): array {
        $gradeLabel = empty($targetGrades) ? 'all grades' : 'grades '.implode(', ', $targetGrades);
        $typeLabel = MiniCourse::getCourseTypes()[$courseType] ?? $courseType;

        $systemPrompt = <<<'PROMPT'
You are an expert instructional designer creating personalized mini-courses for students. Create engaging, evidence-based courses that are age-appropriate and culturally responsive.

You will be given:
1. A topic/learning goal
2. Available resources from the organization's library (with relevance scores)
3. Target parameters (grades, duration, course type)

Your task is to create a structured course that:
- Uses available resources where highly relevant (70%+ relevance)
- Supplements with original content where needed
- Follows sound pedagogical principles
- Is appropriate for the target audience
- Balances content, practice, and reflection

Return a JSON object with this structure (no markdown, just JSON):
{
    "title": "Course title",
    "description": "2-3 sentence course description",
    "objectives": ["Learning objective 1", "Learning objective 2", "Learning objective 3"],
    "rationale": "Why this course matters and how it will help",
    "expected_experience": "What the student can expect from this course",
    "steps": [
        {
            "step_type": "content|reflection|action|practice|human_connection|assessment|checkpoint",
            "content_type": "text|video|document|link|embedded|interactive",
            "title": "Step title",
            "description": "Step description/content",
            "instructions": "What the student should do",
            "resource_id": null or ID of referenced resource,
            "estimated_duration_minutes": 5,
            "is_required": true,
            "feedback_prompt": "Optional prompt for reflection steps"
        }
    ],
    "difficulty_level": "beginner|intermediate|advanced"
}

Important guidelines:
- Create 4-8 steps depending on duration
- Start with engaging content to hook the learner
- Include at least one reflection step
- End with an action step or checkpoint
- Reference resource_id only for resources with 70%+ relevance
- Each step should have clear, actionable instructions
- Vary step types to maintain engagement
PROMPT;

        $userMessage = <<<MSG
Create a {$typeLabel} mini-course on the following topic:

**Topic:** {$topic}

**Target Audience:** Students in {$gradeLabel}
**Target Duration:** {$targetDuration} minutes

**{$resourceContext}**

Generate a complete course structure as JSON.
MSG;

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            Log::error('Course generation failed', [
                'topic' => $topic,
                'error' => $response['error'] ?? 'Unknown error',
            ]);

            // Return a basic fallback structure
            return $this->getFallbackCourseStructure($topic, $targetDuration);
        }

        try {
            $structure = json_decode($response['content'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Try to extract JSON from response
                preg_match('/\{.*\}/s', $response['content'], $matches);
                if (! empty($matches[0])) {
                    $structure = json_decode($matches[0], true);
                }
            }

            if (! is_array($structure) || ! isset($structure['title'])) {
                throw new \Exception('Invalid course structure');
            }

            return $structure;
        } catch (\Exception $e) {
            Log::error('Course structure parsing failed', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'response' => $response['content'] ?? null,
            ]);

            return $this->getFallbackCourseStructure($topic, $targetDuration);
        }
    }

    /**
     * Create the course and steps from generated structure.
     */
    protected function createCourseFromStructure(
        array $structure,
        array $params,
        Collection $relevantResources,
        ?int $createdBy
    ): MiniCourse {
        return DB::transaction(function () use ($structure, $params, $relevantResources, $createdBy) {
            // Create the course
            $course = MiniCourse::create([
                'org_id' => $params['orgId'],
                'title' => $structure['title'],
                'slug' => Str::slug($structure['title']).'-'.Str::random(6),
                'description' => $structure['description'],
                'objectives' => $structure['objectives'] ?? [],
                'rationale' => $structure['rationale'] ?? null,
                'expected_experience' => $structure['expected_experience'] ?? null,
                'course_type' => $params['courseType'] ?? MiniCourse::TYPE_SKILL_BUILDING,
                'creation_source' => MiniCourse::SOURCE_AI_GENERATED,
                'ai_generation_context' => [
                    'topic' => $params['topic'],
                    'target_grades' => $params['targetGrades'] ?? [],
                    'target_duration' => $params['targetDurationMinutes'] ?? 30,
                    'resources_used' => $relevantResources->pluck('id')->toArray(),
                    'generated_at' => now()->toIso8601String(),
                ],
                'target_grades' => $params['targetGrades'] ?? [],
                'target_risk_levels' => $params['targetRiskLevels'] ?? [],
                'estimated_duration_minutes' => $params['targetDurationMinutes'] ?? 30,
                'difficulty_level' => $structure['difficulty_level'] ?? 'beginner',
                'status' => MiniCourse::STATUS_DRAFT,
                'approval_status' => MiniCourse::APPROVAL_PENDING,
                'generation_trigger' => MiniCourse::TRIGGER_MANUAL,
                'auto_generated_at' => now(),
                'created_by' => $createdBy,
            ]);

            // Create steps
            $resourceMap = $relevantResources->keyBy('id');

            foreach ($structure['steps'] ?? [] as $index => $stepData) {
                $resourceId = $stepData['resource_id'] ?? null;

                // Validate resource exists and belongs to org
                if ($resourceId && ! $resourceMap->has($resourceId)) {
                    $resourceId = null;
                }

                MiniCourseStep::create([
                    'mini_course_id' => $course->id,
                    'sort_order' => $index,
                    'step_type' => $this->validateStepType($stepData['step_type'] ?? 'content'),
                    'title' => $stepData['title'],
                    'description' => $stepData['description'] ?? '',
                    'instructions' => $stepData['instructions'] ?? '',
                    'content_type' => $this->validateContentType($stepData['content_type'] ?? 'text'),
                    'content_data' => $stepData['content_data'] ?? [],
                    'resource_id' => $resourceId,
                    'estimated_duration_minutes' => $stepData['estimated_duration_minutes'] ?? 5,
                    'is_required' => $stepData['is_required'] ?? true,
                    'feedback_prompt' => $stepData['feedback_prompt'] ?? null,
                ]);
            }

            // Generate embedding for the course
            if (config('services.embeddings.auto_generate', true)) {
                $course->queueEmbeddingGeneration();
            }

            Log::info('AI course generated', [
                'course_id' => $course->id,
                'topic' => $params['topic'],
                'steps_count' => count($structure['steps'] ?? []),
                'resources_referenced' => collect($structure['steps'] ?? [])->filter(fn ($s) => $s['resource_id'] ?? null)->count(),
            ]);

            return $course;
        });
    }

    /**
     * Validate step type against allowed values.
     */
    protected function validateStepType(string $type): string
    {
        $validTypes = array_keys(MiniCourseStep::getStepTypes());

        return in_array($type, $validTypes) ? $type : MiniCourseStep::TYPE_CONTENT;
    }

    /**
     * Validate content type against allowed values.
     */
    protected function validateContentType(string $type): string
    {
        $validTypes = array_keys(MiniCourseStep::getContentTypes());

        return in_array($type, $validTypes) ? $type : MiniCourseStep::CONTENT_TEXT;
    }

    /**
     * Get a fallback course structure when AI generation fails.
     */
    protected function getFallbackCourseStructure(string $topic, int $duration): array
    {
        $stepsCount = max(3, min(6, (int) ($duration / 5)));

        $steps = [
            [
                'step_type' => 'content',
                'content_type' => 'text',
                'title' => 'Introduction to '.$topic,
                'description' => 'An overview of '.$topic.' and why it matters.',
                'instructions' => 'Read through this introduction to understand the key concepts.',
                'resource_id' => null,
                'estimated_duration_minutes' => 5,
                'is_required' => true,
            ],
            [
                'step_type' => 'reflection',
                'content_type' => 'text',
                'title' => 'Personal Reflection',
                'description' => 'Take a moment to reflect on your current understanding.',
                'instructions' => 'Think about what you already know about '.$topic.' and what you hope to learn.',
                'resource_id' => null,
                'estimated_duration_minutes' => 5,
                'is_required' => true,
                'feedback_prompt' => 'What do you already know about this topic? What questions do you have?',
            ],
        ];

        if ($stepsCount >= 4) {
            $steps[] = [
                'step_type' => 'practice',
                'content_type' => 'text',
                'title' => 'Practice Activity',
                'description' => 'Apply what you\'ve learned through a hands-on activity.',
                'instructions' => 'Complete the following activity to reinforce your understanding.',
                'resource_id' => null,
                'estimated_duration_minutes' => 10,
                'is_required' => true,
            ];
        }

        $steps[] = [
            'step_type' => 'action',
            'content_type' => 'text',
            'title' => 'Next Steps',
            'description' => 'Plan how you will apply what you\'ve learned.',
            'instructions' => 'Identify one specific action you will take based on this learning.',
            'resource_id' => null,
            'estimated_duration_minutes' => 5,
            'is_required' => true,
        ];

        return [
            'title' => 'Learning About '.Str::title($topic),
            'description' => 'A self-paced learning module to help you understand and apply concepts related to '.$topic.'.',
            'objectives' => [
                'Understand the key concepts of '.$topic,
                'Reflect on personal relevance',
                'Identify next steps for application',
            ],
            'rationale' => 'This course provides a structured approach to learning about '.$topic.' at your own pace.',
            'expected_experience' => 'You will learn through content, reflection, and action planning.',
            'steps' => $steps,
            'difficulty_level' => 'beginner',
        ];
    }

    /**
     * Suggest improvements to an existing course.
     */
    public function suggestImprovements(MiniCourse $course): array
    {
        $steps = $course->steps()->with(['resource', 'provider', 'program'])->get();

        $systemPrompt = <<<'PROMPT'
You are an expert instructional designer reviewing a mini-course. Analyze the course structure and provide specific, actionable suggestions for improvement.

Return a JSON object:
{
    "overall_score": 1-10,
    "strengths": ["Strength 1", "Strength 2"],
    "improvements": [
        {
            "area": "Area of improvement",
            "suggestion": "Specific suggestion",
            "priority": "high|medium|low"
        }
    ],
    "missing_elements": ["Element that should be added"]
}
PROMPT;

        $courseData = [
            'title' => $course->title,
            'description' => $course->description,
            'objectives' => $course->objectives,
            'type' => $course->course_type,
            'target_grades' => $course->target_grades,
            'duration' => $course->estimated_duration_minutes,
            'steps' => $steps->map(fn ($s) => [
                'type' => $s->step_type,
                'title' => $s->title,
                'description' => Str::limit($s->description, 100),
                'duration' => $s->estimated_duration_minutes,
                'has_resource' => $s->resource_id !== null,
            ])->toArray(),
        ];

        $userMessage = 'Review this course and suggest improvements: '.json_encode($courseData);

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'overall_score' => null,
                'strengths' => [],
                'improvements' => [],
                'missing_elements' => [],
                'error' => 'Unable to analyze course',
            ];
        }

        try {
            $suggestions = json_decode($response['content'], true);

            return $suggestions ?? [
                'overall_score' => null,
                'strengths' => [],
                'improvements' => [],
                'missing_elements' => [],
            ];
        } catch (\Exception $e) {
            return [
                'overall_score' => null,
                'strengths' => [],
                'improvements' => [],
                'missing_elements' => [],
                'error' => 'Unable to parse analysis',
            ];
        }
    }
}
