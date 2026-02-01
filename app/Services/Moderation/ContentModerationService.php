<?php

namespace App\Services\Moderation;

use App\Models\ContentModerationResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContentModerationService
{
    protected string $apiKey;

    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key');
        $this->model = config('services.moderation.model', config('services.anthropic.model'));
    }

    /**
     * Moderate content and return a result.
     */
    public function moderate(Model $model): ContentModerationResult
    {
        $startTime = microtime(true);

        try {
            // Get content and context from the model
            $content = $model->getModerationContent();
            $context = $model->getModerationContext();

            // Call the AI moderation endpoint
            $response = $this->callModerationAI($content, $context);

            $processingTime = (int) ((microtime(true) - $startTime) * 1000);

            // Create moderation result
            $result = $this->createResult($model, $response, $processingTime);

            // Update the model's moderation status
            $model->update([
                'moderation_status' => $result->status,
                'latest_moderation_id' => $result->id,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Content moderation failed', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);

            // Create a pending result on failure
            return $this->createFailedResult($model, $e->getMessage());
        }
    }

    /**
     * Call the AI moderation API.
     */
    protected function callModerationAI(string $content, array $context): array
    {
        $prompt = $this->buildModerationPrompt($content, $context);

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(60)->post(config('services.anthropic.base_url').'/messages', [
            'model' => $this->model,
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'system' => $this->getSystemPrompt(),
        ]);

        if (! $response->successful()) {
            throw new \Exception('Moderation API request failed: '.$response->body());
        }

        $responseText = $response->json('content.0.text');
        $tokenCount = $response->json('usage.input_tokens', 0) + $response->json('usage.output_tokens', 0);

        return $this->parseResponse($responseText, $tokenCount);
    }

    /**
     * Get the system prompt for moderation.
     */
    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are a content moderation specialist for K-12 educational content. Your role is to evaluate content for safety and appropriateness in educational settings.

You must evaluate content across four dimensions:

1. AGE APPROPRIATENESS (Weight: 30%)
   - Is the language appropriate for the target grade level?
   - Are topics suitable for the intended audience?
   - Is complexity appropriate for the developmental stage?

2. CLINICAL SAFETY (Weight: 35%)
   - Does it avoid harmful medical/mental health advice?
   - Does it appropriately handle sensitive topics?
   - Does it avoid promoting dangerous behaviors?
   - Does it include appropriate disclaimers when discussing health topics?

3. CULTURAL SENSITIVITY (Weight: 20%)
   - Is the content inclusive and respectful?
   - Does it avoid stereotypes or bias?
   - Does it represent diverse perspectives appropriately?

4. ACCURACY (Weight: 15%)
   - Is the information factually correct?
   - Are claims properly supported?
   - Is it up-to-date for the subject matter?

Respond ONLY with valid JSON in this exact format:
{
  "age_appropriateness_score": 0.0-1.0,
  "clinical_safety_score": 0.0-1.0,
  "cultural_sensitivity_score": 0.0-1.0,
  "accuracy_score": 0.0-1.0,
  "flags": ["string array of specific concerns"],
  "recommendations": ["string array of improvement suggestions"],
  "dimension_details": {
    "age_appropriateness": "brief explanation",
    "clinical_safety": "brief explanation",
    "cultural_sensitivity": "brief explanation",
    "accuracy": "brief explanation"
  }
}

Scores closer to 1.0 are better/safer. Be thorough but fair in your assessment.
PROMPT;
    }

    /**
     * Build the moderation prompt.
     */
    protected function buildModerationPrompt(string $content, array $context): string
    {
        $contextStr = '';

        if (! empty($context['target_grades'])) {
            $grades = is_array($context['target_grades']) ? implode(', ', $context['target_grades']) : $context['target_grades'];
            $contextStr .= "Target grades: {$grades}\n";
        }

        if (! empty($context['type'])) {
            $contextStr .= "Content type: {$context['type']}\n";
        }

        if ($context['is_ai_generated'] ?? false) {
            $contextStr .= "Note: This content was AI-generated\n";
        }

        return <<<PROMPT
Please evaluate the following educational content for a K-12 platform.

CONTEXT:
{$contextStr}

CONTENT TO MODERATE:
---
{$content}
---

Provide your evaluation as JSON.
PROMPT;
    }

    /**
     * Parse the AI response.
     */
    protected function parseResponse(string $responseText, int $tokenCount): array
    {
        // Extract JSON from response
        $jsonMatch = [];
        if (preg_match('/\{[\s\S]*\}/', $responseText, $jsonMatch)) {
            $parsed = json_decode($jsonMatch[0], true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $parsed['token_count'] = $tokenCount;

                return $parsed;
            }
        }

        throw new \Exception('Failed to parse moderation response as JSON');
    }

    /**
     * Create a moderation result from the AI response.
     */
    protected function createResult(Model $model, array $response, int $processingTime): ContentModerationResult
    {
        // Calculate weighted overall score
        $overallScore = $this->calculateOverallScore($response);

        // Determine status based on score
        $status = ContentModerationResult::determineStatus($overallScore);

        return ContentModerationResult::create([
            'org_id' => $model->org_id ?? null,
            'moderatable_type' => get_class($model),
            'moderatable_id' => $model->getKey(),
            'status' => $status,
            'overall_score' => $overallScore,
            'age_appropriateness_score' => $response['age_appropriateness_score'] ?? null,
            'clinical_safety_score' => $response['clinical_safety_score'] ?? null,
            'cultural_sensitivity_score' => $response['cultural_sensitivity_score'] ?? null,
            'accuracy_score' => $response['accuracy_score'] ?? null,
            'flags' => $response['flags'] ?? [],
            'recommendations' => $response['recommendations'] ?? [],
            'dimension_details' => $response['dimension_details'] ?? [],
            'model_version' => $this->model,
            'processing_time_ms' => $processingTime,
            'token_count' => $response['token_count'] ?? null,
        ]);
    }

    /**
     * Calculate the weighted overall score.
     */
    protected function calculateOverallScore(array $response): float
    {
        $weights = ContentModerationResult::DIMENSION_WEIGHTS;
        $totalWeight = 0;
        $weightedSum = 0;

        $scoreMapping = [
            'age_appropriateness' => 'age_appropriateness_score',
            'clinical_safety' => 'clinical_safety_score',
            'cultural_sensitivity' => 'cultural_sensitivity_score',
            'accuracy' => 'accuracy_score',
        ];

        foreach ($weights as $dimension => $weight) {
            $scoreKey = $scoreMapping[$dimension];
            if (isset($response[$scoreKey])) {
                $weightedSum += (float) $response[$scoreKey] * $weight;
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? round($weightedSum / $totalWeight, 4) : 0;
    }

    /**
     * Create a failed result when moderation fails.
     */
    protected function createFailedResult(Model $model, string $error): ContentModerationResult
    {
        return ContentModerationResult::create([
            'org_id' => $model->org_id ?? null,
            'moderatable_type' => get_class($model),
            'moderatable_id' => $model->getKey(),
            'status' => ContentModerationResult::STATUS_PENDING,
            'overall_score' => 0,
            'flags' => ['Moderation processing failed: '.$error],
            'recommendations' => ['Please retry moderation or review manually'],
        ]);
    }

    /**
     * Get moderation statistics for an organization.
     */
    public function getStats(?int $orgId = null): array
    {
        $query = ContentModerationResult::query();

        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        return [
            'total' => $query->count(),
            'passed' => (clone $query)->passed()->count(),
            'flagged' => (clone $query)->flagged()->count(),
            'rejected' => (clone $query)->rejected()->count(),
            'pending_review' => (clone $query)->needsReview()->count(),
            'average_score' => (clone $query)->avg('overall_score'),
            'average_processing_time_ms' => (clone $query)->avg('processing_time_ms'),
        ];
    }

    /**
     * Get items needing review.
     */
    public function getReviewQueue(?int $orgId = null, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        $query = ContentModerationResult::needsReview()
            ->with('moderatable')
            ->orderBy('created_at', 'desc');

        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        return $query->limit($limit)->get();
    }
}
