<?php

declare(strict_types=1);

namespace App\Services\Domain;

/**
 * SurveyQuestionFormatterService
 *
 * Handles formatting of survey questions for different delivery channels:
 * SMS, voice calls (TTS), and web. Ensures questions are properly
 * formatted for each medium's constraints and interaction patterns.
 */
class SurveyQuestionFormatterService
{
    /**
     * Format a question for TTS (Text-To-Speech) voice delivery.
     *
     * Converts question structure to readable spoken format, including
     * options for scale and multiple choice questions.
     *
     * @param  array  $question  Question structure with type, text, and options
     * @param  int  $questionNumber  Sequential question number in survey
     * @return string Formatted TTS text
     */
    public function formatQuestionForTts(array $question, int $questionNumber): string
    {
        $text = "Question {$questionNumber}: {$question['question']} ";

        if ($question['type'] === 'scale') {
            $text .= $this->formatScaleForTts($question);
        } elseif ($question['type'] === 'multiple_choice') {
            $text .= $this->formatMultipleChoiceForTts($question);
        }

        return $text;
    }

    /**
     * Format a question for SMS delivery.
     *
     * Creates a condensed text-based format suitable for mobile messaging.
     *
     * @param  array  $question  Question structure
     * @return string Formatted SMS text
     */
    public function formatQuestionForSms(array $question): string
    {
        $text = $question['question'] . "\n\n";

        if ($question['type'] === 'scale') {
            $text .= $this->formatScaleForSms($question);
        } elseif ($question['type'] === 'multiple_choice') {
            $text .= $this->formatMultipleChoiceForSms($question);
        } else {
            $text .= 'Reply with your answer.';
        }

        return $text;
    }

    /**
     * Format scale question for TTS.
     *
     * @param  array  $question  Question data
     * @return string TTS formatted scale instructions
     */
    protected function formatScaleForTts(array $question): string
    {
        $min = $question['min'] ?? 1;
        $max = $question['max'] ?? 5;
        $labels = $question['labels'] ?? [];

        $text = "Press a number from {$min} to {$max}. ";

        if (!empty($labels)) {
            $text .= "{$min} means {$labels[0]}, and {$max} means " . end($labels) . '. ';
        }

        return $text;
    }

    /**
     * Format scale question for SMS.
     *
     * @param  array  $question  Question data
     * @return string SMS formatted scale instructions
     */
    protected function formatScaleForSms(array $question): string
    {
        $min = $question['min'] ?? 1;
        $max = $question['max'] ?? 5;
        $labels = $question['labels'] ?? [];

        $text = "Reply with a number ({$min}-{$max}):\n";

        if (!empty($labels)) {
            for ($i = $min; $i <= $max; $i++) {
                $labelIndex = $i - $min;
                if (isset($labels[$labelIndex])) {
                    $text .= "{$i} = {$labels[$labelIndex]}\n";
                }
            }
        }

        return $text;
    }

    /**
     * Format multiple choice question for TTS.
     *
     * @param  array  $question  Question data
     * @return string TTS formatted options
     */
    protected function formatMultipleChoiceForTts(array $question): string
    {
        $options = $question['options'] ?? [];
        $text = 'Your options are: ';

        foreach ($options as $i => $option) {
            $num = $i + 1;
            $text .= "Press {$num} for {$option}. ";
        }

        return $text;
    }

    /**
     * Format multiple choice question for SMS.
     *
     * @param  array  $question  Question data
     * @return string SMS formatted options
     */
    protected function formatMultipleChoiceForSms(array $question): string
    {
        $options = $question['options'] ?? [];
        $text = "Reply with a number:\n";

        foreach ($options as $i => $option) {
            $text .= ($i + 1) . " = {$option}\n";
        }

        return $text;
    }

    /**
     * Format initial voice greeting for survey call.
     *
     * @param  string  $surveyTitle  The survey name
     * @param  array  $firstQuestion  The first question to ask
     * @return string TTS formatted greeting message
     */
    public function formatVoiceGreeting(string $surveyTitle, array $firstQuestion): string
    {
        $questionText = $this->formatQuestionForTts($firstQuestion, 1);

        return "Hello! This is Pulse calling with a quick survey called {$surveyTitle}. " .
               'Please use your phone keypad to respond. ' .
               $questionText;
    }

    /**
     * Format initial chat message for conversational delivery.
     *
     * @param  string  $surveyTitle  The survey name
     * @param  int  $estimatedMinutes  Estimated time to complete
     * @return string Chat greeting message
     */
    public function formatChatGreeting(string $surveyTitle, int $estimatedMinutes): string
    {
        return "Hi! I'm here to ask you a few questions about \"{$surveyTitle}\". " .
               "This should take about {$estimatedMinutes} minutes. Ready to begin?";
    }
}
