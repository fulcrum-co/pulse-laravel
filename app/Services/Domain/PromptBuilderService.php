<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\Participant;
use App\Models\Survey;

class PromptBuilderService
{
    /**
     * Build system prompt for conversational survey.
     */
    public function buildConversationalSurveyPrompt(Survey $survey, array $participants): string
    {
        $learnerNames = collect($participants)->pluck('full_name')->join(', ');

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
     * Build user message for data extraction from participant context.
     */
    public function buildExtractionMessage(string $transcript, Participant $participant): string
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return $terminology->get('participant_label').": {$participant->full_name}\n" .
            $terminology->get('level_label').": {$participant->level}\n\n" .
            $terminology->get('conversation_transcript_label').":\n{$transcript}\n\n" .
            $terminology->get('extract_structured_data_label');
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
            app(\App\Services\TerminologyService::class)->get('organization_label').': ' . ($context['org_name'] ?? app(\App\Services\TerminologyService::class)->get('unknown_label')) . "\n" .
            app(\App\Services\TerminologyService::class)->get('time_period_label').': ' . ($context['time_period'] ?? app(\App\Services\TerminologyService::class)->get('unknown_label')) . "\n\n" .
            "Data:\n" . json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Build system prompt for resource ranking.
     */
    public function buildResourceRankingPrompt(): string
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return $terminology->get('resource_ranking_system_prompt_prefix') . ' ' .
            str_replace(':participant', $terminology->get('learner_singular'), $terminology->get('resource_ranking_system_prompt_suffix'));
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

        $terminology = app(\App\Services\TerminologyService::class);

        return $terminology->get('participant_need_label').": {$needDescription}\n\n" .
            $terminology->get('resources_label').":\n" . json_encode($resourceList, JSON_PRETTY_PRINT) . "\n\n" .
            $terminology->get('return_indices_only_label');
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
