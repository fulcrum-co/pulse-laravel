<?php

namespace App\Services;

use App\Models\MiniCourse;
use App\Models\MiniCourseStep;
use App\Models\MiniCourseSuggestion;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MiniCourseGenerationService
{
    public function __construct(
        protected ClaudeService $claudeService
    ) {}

    /**
     * Generate a course from student context via AI.
     */
    public function generateFromContext(Student $student, array $signals = []): ?MiniCourse
    {
        $context = $this->buildStudentContext($student, $signals);

        // Generate course structure with AI
        $courseData = $this->generateCourseStructure($context);

        if (! $courseData) {
            Log::warning('Failed to generate course structure', ['student_id' => $student->id]);

            return null;
        }

        // Create the course
        $course = MiniCourse::create([
            'org_id' => $student->org_id,
            'title' => $courseData['title'],
            'description' => $courseData['description'],
            'objectives' => $courseData['objectives'] ?? [],
            'rationale' => $courseData['rationale'] ?? null,
            'expected_experience' => $courseData['expected_experience'] ?? null,
            'course_type' => $courseData['course_type'] ?? MiniCourse::TYPE_INTERVENTION,
            'creation_source' => MiniCourse::SOURCE_AI_GENERATED,
            'ai_generation_context' => [
                'student_id' => $student->id,
                'signals' => $signals,
                'generated_at' => now()->toISOString(),
            ],
            'target_grades' => [$student->grade_level],
            'target_risk_levels' => [$student->risk_level],
            'target_needs' => $courseData['target_needs'] ?? [],
            'estimated_duration_minutes' => $courseData['estimated_duration_minutes'] ?? 30,
            'status' => MiniCourse::STATUS_DRAFT,
            'created_by' => null, // AI generated
        ]);

        // Create steps
        if (! empty($courseData['steps'])) {
            foreach ($courseData['steps'] as $index => $stepData) {
                MiniCourseStep::create([
                    'mini_course_id' => $course->id,
                    'sort_order' => $index + 1,
                    'step_type' => $stepData['step_type'] ?? MiniCourseStep::TYPE_CONTENT,
                    'title' => $stepData['title'],
                    'description' => $stepData['description'] ?? null,
                    'instructions' => $stepData['instructions'] ?? null,
                    'content_type' => $stepData['content_type'] ?? MiniCourseStep::CONTENT_TEXT,
                    'content_data' => $stepData['content_data'] ?? null,
                    'estimated_duration_minutes' => $stepData['duration'] ?? 5,
                    'is_required' => $stepData['is_required'] ?? true,
                    'feedback_prompt' => $stepData['feedback_prompt'] ?? null,
                ]);
            }
        }

        return $course;
    }

    /**
     * Generate a course from an existing template with customizations.
     */
    public function generateFromTemplate(MiniCourse $template, array $customizations = []): MiniCourse
    {
        $newCourse = $template->duplicate();

        // Apply customizations
        if (! empty($customizations['title'])) {
            $newCourse->title = $customizations['title'];
        }

        if (! empty($customizations['target_student'])) {
            $student = Student::find($customizations['target_student']);
            if ($student) {
                $newCourse->target_grades = [$student->grade_level];
                $newCourse->target_risk_levels = [$student->risk_level];
            }
        }

        $newCourse->creation_source = MiniCourse::SOURCE_TEMPLATE;
        $newCourse->source_course_id = $template->id;
        $newCourse->is_template = false;
        $newCourse->save();

        return $newCourse;
    }

    /**
     * Get AI-assisted editing suggestions for a course.
     */
    public function suggestCourseEdits(MiniCourse $course, ?Student $student = null): array
    {
        $context = [
            'course' => [
                'title' => $course->title,
                'description' => $course->description,
                'objectives' => $course->objectives,
                'course_type' => $course->course_type,
                'steps' => $course->steps->map(fn ($s) => [
                    'title' => $s->title,
                    'type' => $s->step_type,
                    'description' => $s->description,
                ])->toArray(),
            ],
        ];

        if ($student) {
            $context['student'] = [
                'grade_level' => $student->grade_level,
                'risk_level' => $student->risk_level,
                'tags' => $student->tags,
            ];
        }

        $systemPrompt = <<<'PROMPT'
You are an educational course designer. Analyze the given course and suggest improvements.
Consider the student context if provided.

Return a JSON object with:
- suggestions: Array of improvement suggestions, each with:
  - type: "content" | "structure" | "engagement" | "accessibility"
  - description: What to improve
  - priority: "high" | "medium" | "low"
  - implementation: How to implement the change
- missing_elements: Array of important elements the course should have
- overall_quality_score: 1-10 rating of current course quality
PROMPT;

        $response = $this->claudeService->sendMessage(
            json_encode($context),
            $systemPrompt
        );

        if (! $response['success']) {
            return [
                'suggestions' => [],
                'missing_elements' => [],
                'overall_quality_score' => null,
                'error' => $response['error'] ?? 'Failed to generate suggestions',
            ];
        }

        return $this->parseJsonResponse($response['content'], [
            'suggestions' => [],
            'missing_elements' => [],
            'overall_quality_score' => null,
        ]);
    }

    /**
     * Generate a course suggestion for a student.
     */
    public function generateCourseSuggestion(Student $student, array $signals = []): ?MiniCourseSuggestion
    {
        $context = $this->buildStudentContext($student, $signals);

        // First check for existing courses that match
        $existingCourses = MiniCourse::where('org_id', $student->org_id)
            ->where('status', MiniCourse::STATUS_ACTIVE)
            ->get();

        if ($existingCourses->isNotEmpty()) {
            $match = $this->findBestMatchingCourse($existingCourses, $context);

            if ($match && $match['score'] >= 70) {
                return MiniCourseSuggestion::create([
                    'org_id' => $student->org_id,
                    'contact_type' => Student::class,
                    'contact_id' => $student->id,
                    'mini_course_id' => $match['course']->id,
                    'suggestion_source' => MiniCourseSuggestion::SOURCE_AI_RECOMMENDED,
                    'relevance_score' => $match['score'] / 100,
                    'trigger_signals' => $signals,
                    'ai_rationale' => $match['reason'],
                    'ai_explanation' => [
                        'matching_factors' => $match['factors'] ?? [],
                        'confidence' => $match['score'],
                    ],
                    'intended_outcomes' => $match['intended_outcomes'] ?? [],
                    'status' => MiniCourseSuggestion::STATUS_PENDING,
                ]);
            }
        }

        // If no good match, suggest generating a new course
        return null;
    }

    /**
     * Build context about a student for AI generation.
     */
    protected function buildStudentContext(Student $student, array $signals = []): array
    {
        $metrics = $student->metrics()
            ->where('period_start', '>=', now()->subMonths(3))
            ->get();

        $recentNotes = $student->notes()
            ->where('created_at', '>=', now()->subMonths(1))
            ->limit(5)
            ->get();

        $recentSurveys = $student->surveyAttempts()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(3))
            ->with('survey')
            ->limit(3)
            ->get();

        return [
            'student' => [
                'grade_level' => $student->grade_level,
                'risk_level' => $student->risk_level,
                'iep_status' => $student->iep_status,
                'ell_status' => $student->ell_status,
                'tags' => $student->tags ?? [],
            ],
            'signals' => $signals,
            'metrics' => $metrics->map(fn ($m) => [
                'category' => $m->metric_category,
                'key' => $m->metric_key,
                'value' => $m->numeric_value,
                'status' => $m->status,
            ])->toArray(),
            'recent_notes_summary' => $recentNotes->map(fn ($n) => [
                'type' => $n->note_type,
                'content_preview' => substr($n->content ?? '', 0, 200),
            ])->toArray(),
            'survey_insights' => $recentSurveys->map(fn ($a) => [
                'survey_name' => $a->survey?->title,
                'score' => $a->overall_score,
                'completed_at' => $a->completed_at?->toDateString(),
            ])->toArray(),
        ];
    }

    /**
     * Generate course structure using AI.
     */
    protected function generateCourseStructure(array $context): ?array
    {
        $systemPrompt = <<<'PROMPT'
You are an educational intervention designer creating personalized mini-courses for students.
Based on the student context provided, design a short, focused intervention course.

Return a JSON object with:
- title: Course title (engaging, student-friendly)
- description: Brief description (2-3 sentences)
- objectives: Array of 3-5 learning objectives
- rationale: Why this course was created for this student (for staff/parents)
- expected_experience: What the student will do (written for the student)
- course_type: One of: intervention, enrichment, skill_building, wellness, academic, behavioral
- target_needs: Array of needs this addresses
- estimated_duration_minutes: Total estimated time (15-60 minutes)
- steps: Array of 4-7 steps, each with:
  - title: Step title
  - step_type: One of: content, reflection, action, practice, human_connection, assessment, checkpoint
  - content_type: One of: text, video, document, link, embedded, interactive
  - description: What this step covers
  - instructions: Instructions for the student
  - content_data: Object with relevant content (body text, prompts, etc.)
  - duration: Estimated minutes
  - is_required: Boolean
  - feedback_prompt: Optional prompt for reflection/feedback

Focus on:
- Practical, actionable content
- Age-appropriate language
- Building skills progressively
- Including reflection and action steps
- Keeping it engaging and achievable
PROMPT;

        $response = $this->claudeService->sendMessage(
            "Generate a personalized mini-course for this student:\n\n".json_encode($context, JSON_PRETTY_PRINT),
            $systemPrompt
        );

        if (! $response['success']) {
            Log::error('Failed to generate course structure', [
                'error' => $response['error'] ?? 'Unknown error',
            ]);

            return null;
        }

        return $this->parseJsonResponse($response['content']);
    }

    /**
     * Find best matching existing course for a student.
     */
    protected function findBestMatchingCourse(Collection $courses, array $context): ?array
    {
        $courseList = $courses->map(fn ($c) => [
            'id' => $c->id,
            'title' => $c->title,
            'description' => $c->description,
            'course_type' => $c->course_type,
            'target_grades' => $c->target_grades,
            'target_risk_levels' => $c->target_risk_levels,
            'target_needs' => $c->target_needs,
            'objectives' => $c->objectives,
        ])->toArray();

        $systemPrompt = <<<'PROMPT'
You are matching a student to existing courses. Find the best match based on the student's needs and context.

Return a JSON object with:
- course_id: ID of the best matching course (or null if no good match)
- score: Match quality 0-100
- reason: Brief explanation of why this is a good match
- factors: Array of factors that led to this match
- intended_outcomes: What outcomes this course should produce for this student
PROMPT;

        $response = $this->claudeService->sendMessage(
            "Student context:\n".json_encode($context, JSON_PRETTY_PRINT).
            "\n\nAvailable courses:\n".json_encode($courseList, JSON_PRETTY_PRINT),
            $systemPrompt
        );

        if (! $response['success']) {
            return null;
        }

        $result = $this->parseJsonResponse($response['content']);

        if ($result && isset($result['course_id'])) {
            $course = $courses->firstWhere('id', $result['course_id']);
            if ($course) {
                return [
                    'course' => $course,
                    'score' => $result['score'] ?? 0,
                    'reason' => $result['reason'] ?? '',
                    'factors' => $result['factors'] ?? [],
                    'intended_outcomes' => $result['intended_outcomes'] ?? [],
                ];
            }
        }

        return null;
    }

    /**
     * Parse JSON from AI response.
     */
    protected function parseJsonResponse(string $content, ?array $default = null): ?array
    {
        // Try to extract JSON from response
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            try {
                $data = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $data;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to parse AI JSON response', [
                    'content' => $content,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $default;
    }

    /**
     * Generate step content for a specific step type.
     */
    public function generateStepContent(string $stepType, string $topic, array $context = []): array
    {
        $prompts = [
            MiniCourseStep::TYPE_CONTENT => "Create educational content about: {$topic}",
            MiniCourseStep::TYPE_REFLECTION => "Create reflection prompts for students to think about: {$topic}",
            MiniCourseStep::TYPE_ACTION => "Create an action plan template for: {$topic}",
            MiniCourseStep::TYPE_PRACTICE => "Create a practice exercise for: {$topic}",
            MiniCourseStep::TYPE_ASSESSMENT => "Create a brief self-assessment quiz for: {$topic}",
        ];

        $prompt = $prompts[$stepType] ?? $prompts[MiniCourseStep::TYPE_CONTENT];

        $systemPrompt = <<<'PROMPT'
You are creating content for a mini-course step. Create engaging, age-appropriate content.
Return a JSON object with:
- body: Main content text (markdown supported)
- prompts: Array of discussion/reflection prompts (if applicable)
- key_points: Array of key takeaways
PROMPT;

        $response = $this->claudeService->sendMessage(
            $prompt."\n\nContext: ".json_encode($context),
            $systemPrompt
        );

        if (! $response['success']) {
            return [
                'body' => "Content about {$topic}",
                'prompts' => [],
                'key_points' => [],
            ];
        }

        return $this->parseJsonResponse($response['content'], [
            'body' => $response['content'],
            'prompts' => [],
            'key_points' => [],
        ]);
    }
}
