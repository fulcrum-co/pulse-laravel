<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyCreationSession;
use App\Models\QuestionBank;
use Illuminate\Http\UploadedFile;

class SurveyCreationService
{
    public function __construct(
        protected ClaudeService $claudeService,
        protected TranscriptionService $transcriptionService
    ) {}

    /**
     * Generate initial greeting for chat-based creation.
     */
    public function generateInitialGreeting(SurveyCreationSession $session): string
    {
        $context = $session->context;
        $purpose = $context['purpose'] ?? null;

        if ($purpose) {
            return "Hi! I see you want to create a survey about {$purpose}. Let me help you with that. What specific aspects would you like to assess?";
        }

        return "Hi! I'm here to help you create a survey. What's the main purpose of this survey? For example, are you looking to check on student wellness, assess academic stress, or gather feedback on something specific?";
    }

    /**
     * Process a chat message and generate AI response with suggestions.
     */
    public function processChatMessage(SurveyCreationSession $session, string $message): array
    {
        $systemPrompt = $this->getSurveyCreationPrompt();
        $conversationHistory = $this->formatConversationHistory($session);

        $result = $this->claudeService->sendMessage($message, $systemPrompt, $conversationHistory);

        if (!$result['success']) {
            return [
                'response' => "I'm having trouble processing that. Could you try rephrasing your request?",
                'suggestions' => null,
            ];
        }

        $response = $result['content'];

        // Try to extract any suggested questions from the response
        $extractedQuestions = $this->extractQuestionsFromResponse($response);

        // Add extracted questions to draft
        if (!empty($extractedQuestions)) {
            foreach ($extractedQuestions as $question) {
                $session->addDraftQuestion($question);
            }
        }

        // Add AI response to conversation history
        $session->addMessage('assistant', $response);

        return [
            'response' => $response,
            'suggestions' => $extractedQuestions,
        ];
    }

    /**
     * Generate question suggestions based on context.
     */
    public function generateQuestionSuggestions(array $context): array
    {
        $purpose = $context['purpose'] ?? 'general wellness check';
        $surveyType = $context['survey_type'] ?? 'wellness';
        $existingQuestions = $context['existing_questions'] ?? [];
        $count = $context['count'] ?? 5;

        $prompt = <<<PROMPT
Generate {$count} survey questions for a {$surveyType} survey with the following purpose: {$purpose}

Existing questions (avoid duplicates):
{$this->formatExistingQuestions($existingQuestions)}

Return ONLY a valid JSON array of question objects. Each question should have:
- id: unique identifier (q1, q2, etc.)
- type: "scale" (1-5 rating), "multiple_choice", or "text"
- question: the question text
- For scale: include "min", "max", and "labels" array
- For multiple_choice: include "options" array

Focus on questions that are:
1. Clear and easy to understand
2. Appropriate for the target audience
3. Actionable (the answers will inform intervention decisions)
4. Non-leading and unbiased

JSON only, no explanation:
PROMPT;

        $result = $this->claudeService->sendMessage($prompt, 'You are a survey design expert. Return only valid JSON.');

        if ($result['success']) {
            $questions = $this->parseJsonFromResponse($result['content']);
            if ($questions) {
                return $questions;
            }
        }

        // Fallback to question bank
        return $this->getQuestionsFromBank($surveyType, $count);
    }

    /**
     * Refine a question based on feedback.
     */
    public function refineQuestion(string $question, string $feedback, ?string $questionType = null): array
    {
        $typeHint = $questionType ? " The question should be a {$questionType} type question." : "";

        $prompt = <<<PROMPT
Refine this survey question based on the feedback provided.

Original question: "{$question}"
Feedback: "{$feedback}"{$typeHint}

Return ONLY a valid JSON object with:
- question: the refined question text
- type: "scale", "multiple_choice", or "text"
- If scale: include "min", "max", "labels"
- If multiple_choice: include "options"

JSON only:
PROMPT;

        $result = $this->claudeService->sendMessage($prompt, 'You are a survey design expert. Return only valid JSON.');

        if ($result['success']) {
            $refined = $this->parseJsonFromResponse($result['content']);
            if ($refined) {
                return $refined;
            }
        }

        // Return original if refinement fails
        return [
            'question' => $question,
            'type' => $questionType ?? 'text',
        ];
    }

    /**
     * Generate interpretation rules for a set of questions.
     */
    public function generateInterpretationRules(array $context): array
    {
        $questions = $context['questions'];
        $surveyType = $context['survey_type'];

        $questionsJson = json_encode($questions, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
Generate interpretation rules for this {$surveyType} survey with these questions:

{$questionsJson}

Return ONLY a valid JSON object with:
- scoring_method: "average", "weighted", or "sum"
- risk_thresholds: { "high": number, "medium": number } (for average score thresholds)
- weights: { "question_id": weight } (if weighted, otherwise omit)
- keyword_flags: array of concerning keywords to flag in text responses
- auto_flag_on: array of conditions that should trigger immediate attention

Consider that this is an educational wellness context where:
- Lower scores on scale questions typically indicate concern
- We want to identify students who may need additional support

JSON only:
PROMPT;

        $result = $this->claudeService->sendMessage($prompt, 'You are an expert in educational assessment. Return only valid JSON.');

        if ($result['success']) {
            $rules = $this->parseJsonFromResponse($result['content']);
            if ($rules) {
                return $rules;
            }
        }

        // Return default interpretation config
        return [
            'scoring_method' => 'average',
            'risk_thresholds' => [
                'high' => 2.0,
                'medium' => 3.0,
            ],
            'keyword_flags' => ['hurt', 'scared', 'help', 'alone', 'bullied'],
        ];
    }

    /**
     * Process voice input for survey creation.
     */
    public function processVoiceInput(SurveyCreationSession $session, UploadedFile $audio): array
    {
        // Store the audio file
        $path = $audio->store('survey-creation-audio/' . $session->id, 'local');

        // Transcribe the audio
        $transcriptionResult = $this->transcriptionService->transcribe($path);

        if (!$transcriptionResult['success']) {
            return [
                'success' => false,
                'error' => 'Failed to transcribe audio.',
                'transcription' => null,
                'extracted_questions' => [],
            ];
        }

        $transcription = $transcriptionResult['transcription'];

        // Extract questions from the transcription
        $extractedQuestions = $this->extractQuestionsFromTranscription($transcription);

        // Add to draft questions
        foreach ($extractedQuestions as $question) {
            $session->addDraftQuestion($question);
        }

        // Update context with voice input
        $session->updateContext([
            'last_voice_input' => $transcription,
            'voice_input_at' => now()->toIso8601String(),
        ]);

        return [
            'success' => true,
            'transcription' => $transcription,
            'extracted_questions' => $extractedQuestions,
        ];
    }

    /**
     * Extract questions from a transcription.
     */
    protected function extractQuestionsFromTranscription(string $transcription): array
    {
        $prompt = <<<PROMPT
Extract survey questions from this voice recording transcription:

"{$transcription}"

The person is describing questions they want to ask in a survey. Convert their descriptions into well-formed survey questions.

Return ONLY a valid JSON array of question objects. Each should have:
- id: unique identifier (q1, q2, etc.)
- type: "scale" (1-5 rating), "multiple_choice", or "text"
- question: the question text (clear, professional wording)
- For scale: include "min", "max", and "labels" array
- For multiple_choice: include "options" array

If no clear questions can be extracted, return an empty array: []

JSON only:
PROMPT;

        $result = $this->claudeService->sendMessage($prompt, 'You are a survey design expert. Return only valid JSON.');

        if ($result['success']) {
            $questions = $this->parseJsonFromResponse($result['content']);
            if ($questions && is_array($questions)) {
                return $questions;
            }
        }

        return [];
    }

    /**
     * Extract questions from an AI response.
     */
    protected function extractQuestionsFromResponse(string $response): array
    {
        // Look for JSON in the response
        $questions = $this->parseJsonFromResponse($response);

        if ($questions && is_array($questions)) {
            // Ensure each question has required fields
            return array_filter($questions, fn($q) =>
                isset($q['question']) && isset($q['type'])
            );
        }

        return [];
    }

    /**
     * Get system prompt for survey creation chat.
     */
    protected function getSurveyCreationPrompt(): string
    {
        return <<<PROMPT
You are a helpful survey creation assistant for an educational wellness platform called Pulse.
Help educators create effective surveys to check on student wellbeing, academic stress, and engagement.

Guidelines:
1. Ask clarifying questions to understand the survey's purpose
2. Suggest appropriate question types (scale 1-5, multiple choice, open text)
3. Ensure questions are age-appropriate and non-leading
4. Focus on actionable insights - questions should help identify students who need support
5. Keep surveys concise (typically 5-10 questions)
6. When you have enough information, provide question suggestions in JSON format

When suggesting questions, include them in a JSON array like this:
```json
[
  {
    "id": "q1",
    "type": "scale",
    "question": "How are you feeling today?",
    "min": 1,
    "max": 5,
    "labels": ["Very Bad", "Bad", "Okay", "Good", "Great"]
  }
]
```

Be conversational and helpful. Guide the educator through the survey creation process step by step.
PROMPT;
    }

    /**
     * Format conversation history for Claude.
     */
    protected function formatConversationHistory(SurveyCreationSession $session): array
    {
        $history = $session->conversation_history ?? [];

        return array_map(fn($msg) => [
            'role' => $msg['role'],
            'content' => $msg['content'],
        ], $history);
    }

    /**
     * Format existing questions for the prompt.
     */
    protected function formatExistingQuestions(array $questions): string
    {
        if (empty($questions)) {
            return 'None yet.';
        }

        return collect($questions)
            ->map(fn($q) => "- " . ($q['question'] ?? $q))
            ->join("\n");
    }

    /**
     * Parse JSON from AI response.
     */
    protected function parseJsonFromResponse(string $response): ?array
    {
        // Try to extract JSON from the response
        if (preg_match('/\[[\s\S]*\]/', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
        }

        return null;
    }

    /**
     * Get questions from question bank as fallback.
     */
    protected function getQuestionsFromBank(string $category, int $count): array
    {
        return QuestionBank::where('is_public', true)
            ->category($category)
            ->orderBy('usage_count', 'desc')
            ->limit($count)
            ->get()
            ->map(fn($q) => $q->toSurveyQuestion())
            ->toArray();
    }
}
