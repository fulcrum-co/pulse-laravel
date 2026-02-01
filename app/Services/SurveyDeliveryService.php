<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use App\Models\SurveyDelivery;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SurveyDeliveryService
{
    public function __construct(
        protected SinchService $sinchService,
        protected TranscriptionService $transcriptionService,
        protected ClaudeService $claudeService
    ) {}

    /**
     * Deliver a survey via the specified channel.
     */
    public function deliver(
        Survey $survey,
        string $channel,
        string $recipientType,
        int $recipientId,
        ?string $phoneNumber = null,
        ?string $scheduledFor = null
    ): SurveyDelivery {
        // Create delivery record
        $delivery = SurveyDelivery::create([
            'survey_id' => $survey->id,
            'channel' => $channel,
            'status' => SurveyDelivery::STATUS_PENDING,
            'recipient_type' => $recipientType === 'student' ? Student::class : User::class,
            'recipient_id' => $recipientId,
            'phone_number' => $phoneNumber ? $this->formatPhoneNumber($phoneNumber) : null,
            'scheduled_for' => $scheduledFor,
        ]);

        // If not scheduled for later, deliver immediately
        if (! $scheduledFor) {
            $this->processDelivery($delivery);
        }

        return $delivery;
    }

    /**
     * Process a pending delivery.
     */
    public function processDelivery(SurveyDelivery $delivery): bool
    {
        try {
            return match ($delivery->channel) {
                SurveyDelivery::CHANNEL_WEB => $this->deliverViaWeb($delivery),
                SurveyDelivery::CHANNEL_SMS => $this->deliverViaSms($delivery),
                SurveyDelivery::CHANNEL_VOICE => $this->deliverViaVoiceCall($delivery),
                SurveyDelivery::CHANNEL_WHATSAPP => $this->deliverViaWhatsApp($delivery),
                SurveyDelivery::CHANNEL_CHAT => $this->deliverViaChat($delivery),
                default => false,
            };
        } catch (\Exception $e) {
            Log::error('Survey delivery failed', [
                'delivery_id' => $delivery->id,
                'error' => $e->getMessage(),
            ]);

            $delivery->markFailed($e->getMessage());

            return false;
        }
    }

    /**
     * Deliver survey via web (create link).
     */
    protected function deliverViaWeb(SurveyDelivery $delivery): bool
    {
        // Create a survey attempt for the recipient
        $attempt = SurveyAttempt::create([
            'survey_id' => $delivery->survey_id,
            'student_id' => $delivery->recipient_type === Student::class ? $delivery->recipient_id : null,
            'user_id' => $delivery->recipient_type === User::class ? $delivery->recipient_id : null,
            'status' => SurveyAttempt::STATUS_IN_PROGRESS,
            'response_channel' => SurveyAttempt::CHANNEL_WEB,
            'delivery_id' => $delivery->id,
            'started_at' => now(),
        ]);

        // Generate survey URL
        $surveyUrl = route('surveys.respond', ['survey' => $delivery->survey_id, 'attempt' => $attempt->id]);

        $delivery->update([
            'status' => SurveyDelivery::STATUS_SENT,
            'survey_attempt_id' => $attempt->id,
            'delivered_at' => now(),
            'delivery_metadata' => [
                'survey_url' => $surveyUrl,
            ],
        ]);

        return true;
    }

    /**
     * Deliver survey via SMS.
     */
    protected function deliverViaSms(SurveyDelivery $delivery): bool
    {
        if (! $delivery->phone_number) {
            $delivery->markFailed('No phone number provided');

            return false;
        }

        $survey = $delivery->survey;

        // Create a survey attempt
        $attempt = SurveyAttempt::create([
            'survey_id' => $delivery->survey_id,
            'student_id' => $delivery->recipient_type === Student::class ? $delivery->recipient_id : null,
            'user_id' => $delivery->recipient_type === User::class ? $delivery->recipient_id : null,
            'status' => SurveyAttempt::STATUS_IN_PROGRESS,
            'response_channel' => SurveyAttempt::CHANNEL_SMS,
            'delivery_id' => $delivery->id,
            'started_at' => now(),
        ]);

        // Generate survey URL for SMS
        $surveyUrl = route('surveys.respond', ['survey' => $delivery->survey_id, 'attempt' => $attempt->id]);

        // Send SMS with survey link
        $message = "Hi! You've been invited to complete the \"{$survey->title}\" survey. Click here to respond: {$surveyUrl}";

        $result = $this->sinchService->sendSms($delivery->phone_number, $message);

        if ($result['success'] ?? false) {
            $delivery->markSent($result['message_id'] ?? null);
            $delivery->update(['survey_attempt_id' => $attempt->id]);

            return true;
        }

        $delivery->markFailed($result['error'] ?? 'SMS send failed');

        return false;
    }

    /**
     * Deliver survey via voice call with TTS.
     */
    protected function deliverViaVoiceCall(SurveyDelivery $delivery): bool
    {
        if (! $delivery->phone_number) {
            $delivery->markFailed('No phone number provided');

            return false;
        }

        $survey = $delivery->survey;

        // Create a survey attempt
        $attempt = SurveyAttempt::create([
            'survey_id' => $delivery->survey_id,
            'student_id' => $delivery->recipient_type === Student::class ? $delivery->recipient_id : null,
            'user_id' => $delivery->recipient_type === User::class ? $delivery->recipient_id : null,
            'status' => SurveyAttempt::STATUS_IN_PROGRESS,
            'response_channel' => SurveyAttempt::CHANNEL_VOICE,
            'delivery_id' => $delivery->id,
            'started_at' => now(),
        ]);

        // Generate initial greeting for the call
        $greeting = $this->generateVoiceGreeting($survey);

        // Initiate the call via Sinch
        $result = $this->sinchService->initiateCall($delivery->phone_number, $greeting);

        if ($result['success'] ?? false) {
            $delivery->markSent($result['call_id'] ?? null);
            $delivery->update([
                'survey_attempt_id' => $attempt->id,
                'delivery_metadata' => [
                    'call_id' => $result['call_id'] ?? null,
                    'greeting' => $greeting,
                ],
            ]);

            return true;
        }

        $delivery->markFailed($result['error'] ?? 'Voice call initiation failed');

        return false;
    }

    /**
     * Deliver survey via WhatsApp.
     */
    protected function deliverViaWhatsApp(SurveyDelivery $delivery): bool
    {
        if (! $delivery->phone_number) {
            $delivery->markFailed('No phone number provided');

            return false;
        }

        $survey = $delivery->survey;

        // Create a survey attempt
        $attempt = SurveyAttempt::create([
            'survey_id' => $delivery->survey_id,
            'student_id' => $delivery->recipient_type === Student::class ? $delivery->recipient_id : null,
            'user_id' => $delivery->recipient_type === User::class ? $delivery->recipient_id : null,
            'status' => SurveyAttempt::STATUS_IN_PROGRESS,
            'response_channel' => SurveyAttempt::CHANNEL_CHAT,
            'delivery_id' => $delivery->id,
            'started_at' => now(),
        ]);

        // Generate survey URL
        $surveyUrl = route('surveys.respond', ['survey' => $delivery->survey_id, 'attempt' => $attempt->id]);

        // Send WhatsApp message
        $message = "Hi! You've been invited to complete the \"{$survey->title}\" survey from Pulse. Click here to respond: {$surveyUrl}";

        $result = $this->sinchService->sendWhatsApp($delivery->phone_number, $message);

        if ($result['success'] ?? false) {
            $delivery->markSent($result['message_id'] ?? null);
            $delivery->update(['survey_attempt_id' => $attempt->id]);

            return true;
        }

        $delivery->markFailed($result['error'] ?? 'WhatsApp send failed');

        return false;
    }

    /**
     * Deliver survey via conversational chat (AI-powered).
     */
    protected function deliverViaChat(SurveyDelivery $delivery): bool
    {
        // Create a survey attempt
        $attempt = SurveyAttempt::create([
            'survey_id' => $delivery->survey_id,
            'student_id' => $delivery->recipient_type === Student::class ? $delivery->recipient_id : null,
            'user_id' => $delivery->recipient_type === User::class ? $delivery->recipient_id : null,
            'status' => SurveyAttempt::STATUS_IN_PROGRESS,
            'response_channel' => SurveyAttempt::CHANNEL_CHAT,
            'delivery_id' => $delivery->id,
            'started_at' => now(),
        ]);

        // Generate initial chat message
        $survey = $delivery->survey;
        $initialMessage = "Hi! I'm here to ask you a few questions about \"{$survey->title}\". This should take about {$survey->estimated_duration_minutes} minutes. Ready to begin?";

        $delivery->update([
            'status' => SurveyDelivery::STATUS_SENT,
            'survey_attempt_id' => $attempt->id,
            'delivered_at' => now(),
            'delivery_metadata' => [
                'initial_message' => $initialMessage,
            ],
        ]);

        // Add initial message to conversation log
        $attempt->addToConversationLog('assistant', $initialMessage);

        return true;
    }

    /**
     * Handle an incoming SMS response.
     */
    public function handleSmsResponse(string $from, string $body): array
    {
        // Find active delivery for this phone number
        $delivery = SurveyDelivery::byPhone($from)
            ->channel(SurveyDelivery::CHANNEL_SMS)
            ->inProgress()
            ->latest()
            ->first();

        if (! $delivery) {
            return [
                'success' => false,
                'error' => 'No active survey found for this number.',
            ];
        }

        $survey = $delivery->survey;
        $attempt = $delivery->surveyAttempt;
        $currentQuestion = $delivery->getCurrentQuestion();

        if (! $currentQuestion) {
            return [
                'success' => false,
                'error' => 'No current question.',
            ];
        }

        // Process the response
        $normalizedResponse = $this->normalizeResponse($body, $currentQuestion);

        // Record the response
        $attempt->recordResponse($currentQuestion['id'], $normalizedResponse);
        $delivery->recordResponse($normalizedResponse);
        $delivery->advanceQuestion();

        // Check if survey is complete
        if ($delivery->isComplete()) {
            $attempt->markCompleted();
            $delivery->markCompleted();

            return [
                'success' => true,
                'complete' => true,
                'message' => 'Thank you for completing the survey!',
            ];
        }

        // Send next question
        $nextQuestion = $delivery->getCurrentQuestion();
        $nextMessage = $this->formatQuestionForSms($nextQuestion);

        $this->sinchService->sendSms($from, $nextMessage);

        return [
            'success' => true,
            'complete' => false,
            'next_question' => $nextQuestion,
        ];
    }

    /**
     * Handle a voice call webhook (DTMF response).
     */
    public function handleVoiceResponse(string $callId, string $dtmfDigits): array
    {
        $delivery = SurveyDelivery::byExternalId($callId)
            ->channel(SurveyDelivery::CHANNEL_VOICE)
            ->first();

        if (! $delivery) {
            return [
                'success' => false,
                'error' => 'No delivery found for this call.',
            ];
        }

        $survey = $delivery->survey;
        $attempt = $delivery->surveyAttempt;
        $currentQuestion = $delivery->getCurrentQuestion();

        if (! $currentQuestion) {
            return [
                'success' => false,
                'tts' => 'Thank you, the survey is complete. Goodbye!',
                'hangup' => true,
            ];
        }

        // Process DTMF response
        $normalizedResponse = $this->normalizeDtmfResponse($dtmfDigits, $currentQuestion);

        // Record the response
        $attempt->recordResponse($currentQuestion['id'], $normalizedResponse);
        $delivery->recordResponse($normalizedResponse);
        $delivery->advanceQuestion();

        // Check if survey is complete
        if ($delivery->isComplete()) {
            $attempt->markCompleted();
            $delivery->markCompleted();

            return [
                'success' => true,
                'complete' => true,
                'tts' => 'Thank you for completing the survey. Your responses have been recorded. Goodbye!',
                'hangup' => true,
            ];
        }

        // Get next question TTS
        $nextQuestion = $delivery->getCurrentQuestion();
        $nextTts = $this->formatQuestionForTts($nextQuestion, $delivery->current_question_index + 1);

        return [
            'success' => true,
            'complete' => false,
            'tts' => $nextTts,
            'collect_dtmf' => true,
        ];
    }

    /**
     * Generate voice greeting for survey call.
     */
    protected function generateVoiceGreeting(Survey $survey): string
    {
        $firstQuestion = $survey->questions[0] ?? null;
        $questionText = $firstQuestion ? $this->formatQuestionForTts($firstQuestion, 1) : '';

        return "Hello! This is Pulse calling with a quick survey called {$survey->title}. ".
               'Please use your phone keypad to respond. '.
               $questionText;
    }

    /**
     * Format a question for TTS delivery.
     */
    protected function formatQuestionForTts(array $question, int $questionNumber): string
    {
        $text = "Question {$questionNumber}: {$question['question']} ";

        if ($question['type'] === 'scale') {
            $min = $question['min'] ?? 1;
            $max = $question['max'] ?? 5;
            $labels = $question['labels'] ?? [];

            $text .= "Press a number from {$min} to {$max}. ";

            if (! empty($labels)) {
                $text .= "{$min} means {$labels[0]}, and {$max} means ".end($labels).'. ';
            }
        } elseif ($question['type'] === 'multiple_choice') {
            $options = $question['options'] ?? [];
            $text .= 'Your options are: ';
            foreach ($options as $i => $option) {
                $num = $i + 1;
                $text .= "Press {$num} for {$option}. ";
            }
        }

        return $text;
    }

    /**
     * Format a question for SMS delivery.
     */
    protected function formatQuestionForSms(array $question): string
    {
        $text = $question['question']."\n\n";

        if ($question['type'] === 'scale') {
            $min = $question['min'] ?? 1;
            $max = $question['max'] ?? 5;
            $labels = $question['labels'] ?? [];

            $text .= "Reply with a number ({$min}-{$max}):\n";

            if (! empty($labels)) {
                for ($i = $min; $i <= $max; $i++) {
                    $labelIndex = $i - $min;
                    if (isset($labels[$labelIndex])) {
                        $text .= "{$i} = {$labels[$labelIndex]}\n";
                    }
                }
            }
        } elseif ($question['type'] === 'multiple_choice') {
            $options = $question['options'] ?? [];
            $text .= "Reply with a number:\n";
            foreach ($options as $i => $option) {
                $text .= ($i + 1)." = {$option}\n";
            }
        } else {
            $text .= 'Reply with your answer.';
        }

        return $text;
    }

    /**
     * Normalize a text response based on question type.
     */
    protected function normalizeResponse(string $response, array $question): mixed
    {
        $response = trim($response);

        if ($question['type'] === 'scale') {
            if (is_numeric($response)) {
                $value = (int) $response;
                $min = $question['min'] ?? 1;
                $max = $question['max'] ?? 5;

                return max($min, min($max, $value));
            }
        }

        if ($question['type'] === 'multiple_choice') {
            if (is_numeric($response)) {
                $options = $question['options'] ?? [];
                $index = (int) $response - 1;

                return $options[$index] ?? $response;
            }
        }

        return $response;
    }

    /**
     * Normalize DTMF digits to a response.
     */
    protected function normalizeDtmfResponse(string $dtmf, array $question): mixed
    {
        // DTMF digits are typically single characters
        $digit = substr($dtmf, 0, 1);

        if ($question['type'] === 'scale' && is_numeric($digit)) {
            $value = (int) $digit;
            $min = $question['min'] ?? 1;
            $max = $question['max'] ?? 5;

            return max($min, min($max, $value));
        }

        if ($question['type'] === 'multiple_choice' && is_numeric($digit)) {
            $options = $question['options'] ?? [];
            $index = (int) $digit - 1;

            return $options[$index] ?? $digit;
        }

        return $digit;
    }

    /**
     * Format phone number to E.164 format.
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Add +1 if US number without country code
        if (strlen($cleaned) === 10) {
            return '+1'.$cleaned;
        }

        // Add + if not present
        if (strlen($cleaned) === 11 && str_starts_with($cleaned, '1')) {
            return '+'.$cleaned;
        }

        // Return with + prefix
        return '+'.$cleaned;
    }
}
