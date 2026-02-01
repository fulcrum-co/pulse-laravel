<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Survey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    protected ?string $apiKey;

    protected string $model;

    protected int $maxTokens;

    protected float $temperature;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
        $this->model = config('services.anthropic.model', 'claude-sonnet-4-20250514');
        $this->maxTokens = config('services.anthropic.max_tokens', 4096);
        $this->temperature = config('services.anthropic.temperature', 0.7);
        $this->baseUrl = config('services.anthropic.base_url', 'https://api.anthropic.com/v1');
    }

    /**
     * Send a message to Claude API.
     */
    public function sendMessage(string $userMessage, ?string $systemPrompt = null, array $conversationHistory = []): array
    {
        $messages = $conversationHistory;
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        $payload = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $messages,
        ];

        if ($systemPrompt) {
            $payload['system'] = $systemPrompt;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(120)->post("{$this->baseUrl}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'content' => $data['content'][0]['text'] ?? '',
                    'usage' => $data['usage'] ?? [],
                    'stop_reason' => $data['stop_reason'] ?? null,
                ];
            }

            Log::error('Claude API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            Log::error('Claude API exception', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Start a conversational survey session.
     */
    public function startConversationalSurvey(Survey $survey, array $students): array
    {
        $studentNames = collect($students)->pluck('full_name')->join(', ');

        $systemPrompt = $survey->llm_system_prompt ?: config('pulse.prompts.conversational_survey');
        $systemPrompt .= "\n\nStudents to discuss: {$studentNames}";

        $initialMessage = "Hello! I'm ready to help you complete your check-in for your students. ".
            "We'll go through each student one at a time. Let's start with the first student. ".
            'How has their week been academically?';

        return [
            'system_prompt' => $systemPrompt,
            'initial_message' => $initialMessage,
            'students' => $students,
            'current_student_index' => 0,
            'conversation_history' => [],
        ];
    }

    /**
     * Process a response in an ongoing conversation.
     */
    public function processConversationTurn(
        string $userResponse,
        array $session,
        string $systemPrompt
    ): array {
        $session['conversation_history'][] = [
            'role' => 'user',
            'content' => $userResponse,
        ];

        $response = $this->sendMessage(
            $userResponse,
            $systemPrompt,
            $session['conversation_history']
        );

        if ($response['success']) {
            $session['conversation_history'][] = [
                'role' => 'assistant',
                'content' => $response['content'],
            ];
        }

        return [
            'session' => $session,
            'response' => $response,
        ];
    }

    /**
     * Extract structured data from conversation transcript.
     */
    public function extractStructuredData(string $transcript, Student $student): array
    {
        $systemPrompt = config('pulse.prompts.data_extraction');

        $userMessage = "Student: {$student->full_name}\n".
            "Grade: {$student->grade_level}\n\n".
            "Conversation transcript:\n{$transcript}\n\n".
            'Extract the structured data as JSON.';

        $response = $this->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'],
            ];
        }

        // Parse JSON from response
        $content = $response['content'];

        // Try to extract JSON from the response
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            try {
                $data = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [
                        'success' => true,
                        'data' => $data,
                        'raw_response' => $content,
                    ];
                }
            } catch (\Exception $e) {
                Log::error('Failed to parse Claude JSON response', [
                    'content' => $content,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success' => false,
            'error' => 'Failed to parse structured data from response',
            'raw_response' => $content,
        ];
    }

    /**
     * Generate a narrative report.
     */
    public function generateReportNarrative(array $data, array $context = []): array
    {
        $systemPrompt = config('pulse.prompts.report_narrative');

        $userMessage = "Generate a narrative report for the following data:\n\n".
            'Organization: '.($context['org_name'] ?? 'Unknown')."\n".
            'Time Period: '.($context['time_period'] ?? 'Unknown')."\n\n".
            "Data:\n".json_encode($data, JSON_PRETTY_PRINT);

        $response = $this->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            return [
                'success' => false,
                'error' => $response['error'],
            ];
        }

        return [
            'success' => true,
            'narrative' => $response['content'],
        ];
    }

    /**
     * Rank resources for a student's needs.
     */
    public function rankResources(array $resources, string $needDescription): array
    {
        $resourceList = collect($resources)->map(function ($resource, $index) {
            return [
                'index' => $index,
                'title' => $resource['title'],
                'description' => $resource['description'],
                'type' => $resource['resource_type'],
                'tags' => $resource['tags'],
            ];
        })->toArray();

        $systemPrompt = 'You are an educational resource specialist. '.
            "Rank the following resources by relevance to the student's needs. ".
            'Return a JSON array of indices in order of relevance (most relevant first).';

        $userMessage = "Student need: {$needDescription}\n\n".
            "Resources:\n".json_encode($resourceList, JSON_PRETTY_PRINT)."\n\n".
            'Return only a JSON array of indices, e.g., [2, 0, 3, 1]';

        $response = $this->sendMessage($userMessage, $systemPrompt);

        if (! $response['success']) {
            // Return original order if ranking fails
            return array_keys($resources);
        }

        // Parse ranking from response
        if (preg_match('/\[[\d,\s]+\]/', $response['content'], $matches)) {
            try {
                $ranking = json_decode($matches[0], true);
                if (is_array($ranking)) {
                    return $ranking;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to parse resource ranking', [
                    'response' => $response['content'],
                ]);
            }
        }

        return array_keys($resources);
    }

    /**
     * Filter emotional language from text.
     */
    public function filterEmotionalLanguage(string $text): string
    {
        $systemPrompt = 'You are a professional editor. '.
            'Rewrite the following text to remove emotional language and keep only factual observations. '.
            'Maintain the core information but use neutral, professional language.';

        $response = $this->sendMessage($text, $systemPrompt);

        return $response['success'] ? $response['content'] : $text;
    }

    /**
     * Check if API is configured and working.
     */
    public function healthCheck(): array
    {
        if (empty($this->apiKey)) {
            return [
                'healthy' => false,
                'error' => 'API key not configured',
            ];
        }

        $response = $this->sendMessage('Hello, this is a health check. Respond with "OK".');

        return [
            'healthy' => $response['success'],
            'error' => $response['error'] ?? null,
        ];
    }
}
