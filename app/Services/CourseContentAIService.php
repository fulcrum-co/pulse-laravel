<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

class CourseContentAIService
{
    public function __construct(
        protected ClaudeService $claudeService
    ) {}

    /**
     * Generate a complete course draft from topic and parameters.
     */
    public function generateCompleteCourse(array $params): array
    {
        $topic = $params['topic'] ?? '';
        $audience = $params['audience'] ?? 'participants';
        $gradeLevel = $params['level'] ?? null;
        $courseType = $params['course_type'] ?? 'skill_building';
        $duration = $params['duration_minutes'] ?? 30;
        $objectives = $params['objectives'] ?? [];

        $systemPrompt = <<<'PROMPT'
You are an expert educational course designer. Create a comprehensive mini-course structure.

Return a JSON object with:
- title: Engaging course title
- description: 2-3 sentence description
- objectives: Array of 3-5 clear learning objectives
- rationale: Why this course is valuable (for staff/direct_supervisors)
- expected_experience: What the participant will do (written for the participant)
- course_type: One of: intervention, enrichment, skill_building, wellness, academic, behavioral
- target_needs: Array of needs/skills this addresses
- estimated_duration_minutes: Total time (should match requested duration)
- steps: Array of steps (4-7 steps), each with:
  - title: Step title
  - step_type: One of: content, reflection, action, practice, human_connection, assessment, checkpoint
  - content_type: One of: text, video, document, link, embedded, interactive
  - description: What this step covers
  - instructions: Clear instructions for the participant
  - content_data: Object with:
    - body: Main content text (use markdown)
    - key_points: Array of key takeaways
    - prompts: Array of reflection/discussion prompts (if applicable)
    - quiz_questions: Array of questions (for assessment steps)
  - duration: Minutes for this step
  - is_required: Boolean
  - feedback_prompt: Optional reflection prompt

Design principles:
- Progressive skill building
- Mix of content, reflection, and action
- Age-appropriate language
- Practical and actionable
- Engaging and achievable
PROMPT;

        $userMessage = "Create a mini-course about: {$topic}\n\n";
        $userMessage .= "Target audience: {$audience}\n";
        if ($gradeLevel) {
            $userMessage .= "Level level: {$gradeLevel}\n";
        }
        $userMessage .= "Course type: {$courseType}\n";
        $userMessage .= "Target duration: {$duration} minutes\n";

        if (! empty($objectives)) {
            $userMessage .= "Desired objectives:\n".implode("\n", array_map(fn ($o) => "- {$o}", $objectives));
        }

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            Log::error('Failed to generate complete course', ['error' => $response['error'] ?? 'Unknown']);

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to generate course',
            ];
        }

        $courseData = $this->parseJsonResponse($response['content']);

        if (! $courseData) {
            return [
                'success' => false,
                'error' => 'Failed to parse AI response',
            ];
        }

        return [
            'success' => true,
            'course' => $courseData,
        ];
    }

    /**
     * Generate introduction section for a course.
     */
    public function generateIntroduction(string $topic, array $context = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are an educational content writer. Create an engaging introduction for a mini-course.

Return a JSON object with:
- title: Attention-grabbing introduction title
- hook: Opening statement to capture interest (1-2 sentences)
- overview: What the participant will learn (2-3 sentences)
- why_it_matters: Why this topic is important (2-3 sentences)
- what_to_expect: Brief preview of course structure
- success_criteria: How participants will know they've succeeded
PROMPT;

        $courseType = $context['course_type'] ?? 'skill_building';
        $audience = $context['audience'] ?? 'participants';
        $gradeLevel = $context['level'] ?? null;

        $userMessage = "Create an introduction for a {$courseType} course about: {$topic}\n";
        $userMessage .= "Audience: {$audience}\n";
        if ($gradeLevel) {
            $userMessage .= "Level level: {$gradeLevel}\n";
        }

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to generate introduction',
            ];
        }

        return [
            'success' => true,
            'introduction' => $this->parseJsonResponse($response['content'], [
                'title' => "Introduction to {$topic}",
                'hook' => '',
                'overview' => '',
                'why_it_matters' => '',
                'what_to_expect' => '',
                'success_criteria' => '',
            ]),
        ];
    }

    /**
     * Generate a content section/step.
     */
    public function generateContentSection(string $topic, array $objectives = [], array $context = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are an educational content writer. Create educational content for a mini-course step.

Return a JSON object with:
- title: Section title
- body: Main content (markdown formatted, 300-600 words)
- key_points: Array of 3-5 key takeaways
- examples: Array of practical examples
- tips: Array of helpful tips
- common_mistakes: Array of mistakes to avoid (if applicable)
- vocabulary: Object of key terms and definitions (if applicable)
PROMPT;

        $gradeLevel = $context['level'] ?? null;
        $courseType = $context['course_type'] ?? 'skill_building';

        $userMessage = "Create educational content about: {$topic}\n\n";
        if (! empty($objectives)) {
            $userMessage .= "Learning objectives to address:\n".implode("\n", array_map(fn ($o) => "- {$o}", $objectives))."\n\n";
        }
        $userMessage .= "Course type: {$courseType}\n";
        if ($gradeLevel) {
            $userMessage .= "Level level: {$gradeLevel}\n";
        }

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to generate content section',
            ];
        }

        return [
            'success' => true,
            'content' => $this->parseJsonResponse($response['content'], [
                'title' => $topic,
                'body' => '',
                'key_points' => [],
                'examples' => [],
                'tips' => [],
            ]),
        ];
    }

    /**
     * Generate reflection prompts.
     */
    public function generateReflectionPrompts(string $topic, array $context = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are an educational coach. Create thoughtful reflection prompts for participants.

Return a JSON object with:
- title: Reflection section title
- introduction: Brief intro to reflection (1-2 sentences)
- prompts: Array of 3-5 reflection prompts, each with:
  - question: The reflection question
  - guidance: Brief guidance on how to approach it
  - depth: "surface" | "moderate" | "deep"
- journaling_prompt: Optional longer journaling prompt
- connection_prompt: How to connect learning to real life
PROMPT;

        $contentCovered = $context['content_covered'] ?? $topic;
        $objectives = $context['objectives'] ?? [];

        $userMessage = "Create reflection prompts for participants after studying: {$contentCovered}\n\n";
        if (! empty($objectives)) {
            $userMessage .= "Learning objectives covered:\n".implode("\n", array_map(fn ($o) => "- {$o}", $objectives));
        }

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to generate reflection prompts',
            ];
        }

        return [
            'success' => true,
            'reflection' => $this->parseJsonResponse($response['content'], [
                'title' => 'Reflection',
                'introduction' => '',
                'prompts' => [],
                'journaling_prompt' => '',
                'connection_prompt' => '',
            ]),
        ];
    }

    /**
     * Generate assessment questions.
     */
    public function generateAssessment(array $objectives, string $format = 'quiz', array $context = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are an assessment designer. Create appropriate assessment items.

Return a JSON object with:
- title: Assessment title
- instructions: Instructions for the participant
- questions: Array of questions, each with:
  - type: "multiple_choice" | "true_false" | "short_answer" | "reflection"
  - question: The question text
  - options: Array of options (for multiple choice)
  - correct_answer: The correct answer (for auto-graded types)
  - explanation: Why this is the correct answer
  - points: Point value
  - objective_addressed: Which objective this tests
- passing_score: Minimum score to pass (percentage)
- feedback_on_completion: Message shown after completion
PROMPT;

        $numQuestions = $context['num_questions'] ?? 5;
        $difficulty = $context['difficulty'] ?? 'moderate';

        $userMessage = "Create a {$format} assessment for the following learning objectives:\n";
        $userMessage .= implode("\n", array_map(fn ($o) => "- {$o}", $objectives))."\n\n";
        $userMessage .= "Number of questions: {$numQuestions}\n";
        $userMessage .= "Difficulty: {$difficulty}\n";

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to generate assessment',
            ];
        }

        return [
            'success' => true,
            'assessment' => $this->parseJsonResponse($response['content'], [
                'title' => 'Assessment',
                'instructions' => '',
                'questions' => [],
                'passing_score' => 70,
                'feedback_on_completion' => '',
            ]),
        ];
    }

    /**
     * Generate an action plan step.
     */
    public function generateActionPlan(string $topic, array $context = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are a learning coach. Create an action plan template for participants to apply what they've learned.

Return a JSON object with:
- title: Action plan title
- introduction: Why action planning matters (1-2 sentences)
- goal_setting_prompt: Prompt for setting a personal goal
- action_items: Array of suggested action items, each with:
  - action: What to do
  - timeframe: Suggested timeframe
  - resources_needed: What's needed to complete it
- accountability_prompt: How to stay accountable
- progress_checkpoints: Array of milestone check-ins
- celebration_prompt: How to recognize success
PROMPT;

        $objectives = $context['objectives'] ?? [];

        $userMessage = "Create an action plan for applying learning about: {$topic}\n\n";
        if (! empty($objectives)) {
            $userMessage .= "Based on objectives:\n".implode("\n", array_map(fn ($o) => "- {$o}", $objectives));
        }

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to generate action plan',
            ];
        }

        return [
            'success' => true,
            'action_plan' => $this->parseJsonResponse($response['content'], [
                'title' => 'Your Action Plan',
                'introduction' => '',
                'goal_setting_prompt' => '',
                'action_items' => [],
                'accountability_prompt' => '',
                'progress_checkpoints' => [],
                'celebration_prompt' => '',
            ]),
        ];
    }

    /**
     * Get inline suggestions as user types.
     */
    public function getInlineSuggestions(string $content, string $fieldType, array $context = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are an educational content assistant. Provide helpful suggestions to improve the content.

Return a JSON object with:
- suggestions: Array of 2-4 specific suggestions, each with:
  - type: "expand" | "clarify" | "example" | "simplify" | "format"
  - text: The suggestion
  - preview: How the content might look with this suggestion applied (if applicable)
- improvements: Array of specific text improvements
- questions: Any clarifying questions about intent
PROMPT;

        $courseContext = isset($context['course_title']) ? "Course: {$context['course_title']}\n" : '';
        $stepContext = isset($context['step_title']) ? "Step: {$context['step_title']}\n" : '';

        $userMessage = "Provide suggestions for improving this {$fieldType} content:\n\n";
        $userMessage .= $courseContext.$stepContext;
        $userMessage .= "Current content:\n{$content}";

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to get suggestions',
            ];
        }

        return [
            'success' => true,
            'data' => $this->parseJsonResponse($response['content'], [
                'suggestions' => [],
                'improvements' => [],
                'questions' => [],
            ]),
        ];
    }

    /**
     * Complete/continue text that user is typing.
     */
    public function completeText(string $partial, string $fieldType, array $context = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are an educational content writer. Complete the partial text in a way that is:
- Natural and flows from what's written
- Appropriate for the context and audience
- Educational and helpful

Return a JSON object with:
- completion: The suggested completion (just the new text, not the original)
- alternatives: Array of 2 alternative completions
PROMPT;

        $courseType = $context['course_type'] ?? 'skill_building';
        $audience = $context['audience'] ?? 'participants';

        $userMessage = "Complete this {$fieldType} text for a {$courseType} course aimed at {$audience}:\n\n";
        $userMessage .= "Partial text: \"{$partial}\"";

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to complete text',
            ];
        }

        return [
            'success' => true,
            'data' => $this->parseJsonResponse($response['content'], [
                'completion' => '',
                'alternatives' => [],
            ]),
        ];
    }

    /**
     * Extract course structure from uploaded document text.
     */
    public function extractCourseFromDocument(string $documentText, array $params = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are an expert at converting documents into structured educational courses.
Analyze the document and create a mini-course structure.

Return a JSON object with:
- title: Course title (derived from document topic)
- description: Course description
- objectives: Array of 3-5 learning objectives (extracted from document)
- rationale: Why this content is valuable
- expected_experience: What participants will do
- course_type: Best fit from: intervention, enrichment, skill_building, wellness, academic, behavioral
- estimated_duration_minutes: Based on content length
- steps: Array of 4-7 steps extracted from the document, each with:
  - title: Step title (section heading or derived)
  - step_type: Best fit from: content, reflection, action, practice, assessment, checkpoint
  - content_type: text (default for document extraction)
  - description: What this section covers
  - instructions: Instructions for the participant
  - content_data:
    - body: Extracted/adapted content (markdown)
    - key_points: Key takeaways from this section
  - duration: Estimated minutes
  - is_required: true
- extraction_notes: Any notes about the extraction (gaps, suggestions)
PROMPT;

        $courseType = $params['course_type'] ?? null;
        $audience = $params['audience'] ?? 'participants';
        $gradeLevel = $params['level'] ?? null;

        $userMessage = "Convert this document into a mini-course structure:\n\n";
        $userMessage .= "---BEGIN DOCUMENT---\n{$documentText}\n---END DOCUMENT---\n\n";

        if ($courseType) {
            $userMessage .= "Preferred course type: {$courseType}\n";
        }
        $userMessage .= "Target audience: {$audience}\n";
        if ($gradeLevel) {
            $userMessage .= "Level level: {$gradeLevel}\n";
        }

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            Log::error('Failed to extract course from document', ['error' => $response['error'] ?? 'Unknown']);

            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to extract course from document',
            ];
        }

        $courseData = $this->parseJsonResponse($response['content']);

        if (! $courseData) {
            return [
                'success' => false,
                'error' => 'Failed to parse extracted course structure',
            ];
        }

        return [
            'success' => true,
            'course' => $courseData,
        ];
    }

    /**
     * Convert document text to content blocks.
     */
    public function convertDocumentToBlocks(string $documentText, array $params = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are an expert at converting documents into structured content blocks for an educational platform.

Return a JSON object with:
- blocks: Array of content blocks, each with:
  - type: "heading" | "text" | "list" | "quote" | "callout" | "image_placeholder" | "video_placeholder"
  - content: The content (markdown for text, array for lists)
  - level: For headings, 1-3
  - style: For callouts, "info" | "warning" | "tip" | "example"
- summary: Brief summary of the document
- suggested_title: Suggested title if not obvious
- word_count: Approximate word count
PROMPT;

        $response = $this->claudeService->sendMessage(
            "Convert this document to content blocks:\n\n{$documentText}",
            $systemPrompt
        );

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to convert document',
            ];
        }

        return [
            'success' => true,
            'data' => $this->parseJsonResponse($response['content'], [
                'blocks' => [],
                'summary' => '',
                'suggested_title' => '',
                'word_count' => 0,
            ]),
        ];
    }

    /**
     * Improve existing content.
     */
    public function improveContent(string $content, string $improvementType, array $context = []): array
    {
        $improvements = [
            'clarity' => 'Make this content clearer and easier to understand',
            'engagement' => 'Make this content more engaging and interesting',
            'simplify' => 'Simplify this content for younger or struggling participants',
            'expand' => 'Expand this content with more detail and examples',
            'academic' => 'Make this content more academically rigorous',
            'practical' => 'Add more practical, real-world applications',
        ];

        $instruction = $improvements[$improvementType] ?? $improvements['clarity'];

        $systemPrompt = <<<PROMPT
You are an educational content editor. {$instruction}.

Return a JSON object with:
- improved_content: The improved version
- changes_made: Array of changes made
- before_after: Array of specific before/after examples
PROMPT;

        $response = $this->claudeService->sendMessage(
            "Improve this content:\n\n{$content}",
            $systemPrompt
        );

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to improve content',
            ];
        }

        return [
            'success' => true,
            'data' => $this->parseJsonResponse($response['content'], [
                'improved_content' => $content,
                'changes_made' => [],
                'before_after' => [],
            ]),
        ];
    }

    /**
     * Generate quiz questions from content.
     */
    public function generateQuizFromContent(string $content, int $numQuestions = 5, array $context = []): array
    {
        $systemPrompt = <<<'PROMPT'
You are an assessment expert. Create quiz questions based on the content provided.

Return a JSON object with:
- questions: Array of questions, each with:
  - type: "multiple_choice" | "true_false" | "fill_blank"
  - question: The question text
  - options: Array of options (for multiple choice, 4 options)
  - correct_answer: The correct answer
  - explanation: Why this is correct
  - difficulty: "easy" | "medium" | "hard"
  - content_reference: Which part of the content this tests
PROMPT;

        $questionTypes = $context['question_types'] ?? ['multiple_choice', 'true_false'];
        $difficulty = $context['difficulty'] ?? 'mixed';

        $userMessage = "Create {$numQuestions} quiz questions based on this content:\n\n{$content}\n\n";
        $userMessage .= 'Question types to include: '.implode(', ', $questionTypes)."\n";
        $userMessage .= "Difficulty: {$difficulty}";

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Failed to generate quiz',
            ];
        }

        return [
            'success' => true,
            'data' => $this->parseJsonResponse($response['content'], [
                'questions' => [],
            ]),
        ];
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
                    'content' => substr($content, 0, 500),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $default;
    }
}
