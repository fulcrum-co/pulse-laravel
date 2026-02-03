<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\Learner;
use App\Models\Survey;

class PromptBuilderService
{
    /**
     * Build system prompt for conversational survey.
     */
    public function buildConversationalSurveyPrompt(Survey $survey, array $learners): string
    {
        $learnerNames = collect($learners)->pluck('full_name')->join(', ');

        $basePrompt = $survey->llm_system_prompt ?: config('pulse.prompts.conversational_survey');

        return $basePrompt . "\n\nLearners to discuss: {$learnerNames}";
    }

    /**
     * Build system prompt for data extraction.
     */
    public function buildDataExtractionPrompt(): string
    {
        return config('pulse.prompts.data_extraction');
    }

    /**
     * Build user message for data extraction from learner context.
     */
    public function buildExtractionMessage(string $transcript, Learner $learner): string
    {
        return "Learner: {$learner->full_name}\n" .
            "Grade: {$learner->grade_level}\n\n" .
            "Conversation transcript:\n{$transcript}\n\n" .
            'Extract the structured data as JSON.';
    }

    /**
     * Build system prompt for report narrative generation.
     */
    public function buildReportNarrativePrompt(): string
    {
        return config('pulse.prompts.report_narrative');
    }

    /**
     * Build user message for report narrative generation.
     */
    public function buildReportNarrativeMessage(array $data, array $context = []): string
    {
        return "Generate a narrative report for the following data:\n\n" .
            'Organization: ' . ($context['org_name'] ?? 'Unknown') . "\n" .
            'Time Period: ' . ($context['time_period'] ?? 'Unknown') . "\n\n" .
            "Data:\n" . json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Build system prompt for resource ranking.
     */
    public function buildResourceRankingPrompt(): string
    {
        return 'You are an educational resource specialist. ' .
            "Rank the following resources by relevance to the learner's needs. " .
            'Return a JSON array of indices in order of relevance (most relevant first).';
    }

    /**
     * Build user message for resource ranking.
     */
    public function buildResourceRankingMessage(array $resources, string $needDescription): string
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

        return "Learner need: {$needDescription}\n\n" .
            "Resources:\n" . json_encode($resourceList, JSON_PRETTY_PRINT) . "\n\n" .
            'Return only a JSON array of indices, e.g., [2, 0, 3, 1]';
    }

    /**
     * Build system prompt for emotional language filtering.
     */
    public function buildEmotionalLanguageFilterPrompt(): string
    {
        return 'You are a professional editor. ' .
            'Rewrite the following text to remove emotional language and keep only factual observations. ' .
            'Maintain the core information but use neutral, professional language.';
    }
}
