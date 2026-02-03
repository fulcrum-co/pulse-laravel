<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Learner;
use App\Models\Survey;
use App\Services\Domain\AIResponseParserDomainService;
use App\Services\Domain\PromptBuilderService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    protected ?string $apiKey;

    protected string $model;

    protected int $maxTokens;

    protected float $temperature;

    protected string $baseUrl;

    public function __construct(
        protected PromptBuilderService $promptBuilder,
        protected AIResponseParserDomainService $responseParser
    ) {
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
    public function startConversationalSurvey(Survey $survey, array $learners): array
    {
        $systemPrompt = $this->promptBuilder->buildConversationalSurveyPrompt($survey, $learners);

        $initialMessage = "Hello! I'm ready to help you complete your check-in for your learners. " .
            "We'll go through each learner one at a time. Let's start with the first learner. " .
            'How has their week been academically?';

        return [
            'system_prompt' => $systemPrompt,
            'initial_message' => $initialMessage,
            'learners' => $learners,
            'current_learner_index' => 0,
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
    public function extractStructuredData(string $transcript, Learner $learner): array
    {
        $systemPrompt = $this->promptBuilder->buildDataExtractionPrompt();
        $userMessage = $this->promptBuilder->buildExtractionMessage($transcript, $learner);

        $response = $this->sendMessage($userMessage, $systemPrompt);

        if (!$response['success']) {
            return [
                'success' => false,
                'error' => $response['error'],
            ];
        }

        $data = $this->responseParser->extractJson($response['content']);

        if ($data) {
            return [
                'success' => true,
                'data' => $data,
                'raw_response' => $response['content'],
            ];
        }

        Log::error('Failed to parse Claude JSON response', [
            'content' => $response['content'],
        ]);

        return [
            'success' => false,
            'error' => 'Failed to parse structured data from response',
            'raw_response' => $response['content'],
        ];
    }

    /**
     * Generate a narrative report.
     */
    public function generateReportNarrative(array $data, array $context = []): array
    {
        $systemPrompt = $this->promptBuilder->buildReportNarrativePrompt();
        $userMessage = $this->promptBuilder->buildReportNarrativeMessage($data, $context);

        $response = $this->sendMessage($userMessage, $systemPrompt);

        if (!$response['success']) {
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
     * Rank resources for a learner's needs.
     */
    public function rankResources(array $resources, string $needDescription): array
    {
        $systemPrompt = $this->promptBuilder->buildResourceRankingPrompt();
        $userMessage = $this->promptBuilder->buildResourceRankingMessage($resources, $needDescription);

        $response = $this->sendMessage($userMessage, $systemPrompt);

        if (!$response['success']) {
            return array_keys($resources);
        }

        $ranking = $this->responseParser->extractArray($response['content']);

        if ($ranking) {
            return $ranking;
        }

        Log::warning('Failed to parse resource ranking', [
            'response' => $response['content'],
        ]);

        return array_keys($resources);
    }

    /**
     * Filter emotional language from text.
     */
    public function filterEmotionalLanguage(string $text): string
    {
        $systemPrompt = $this->promptBuilder->buildEmotionalLanguageFilterPrompt();

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
