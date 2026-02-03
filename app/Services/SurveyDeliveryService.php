<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Learner;
use App\Models\Survey;
use App\Models\SurveyAttempt;
use App\Models\SurveyDelivery;
use App\Models\User;
use App\Services\Domain\PhoneNumberFormatterService;
use App\Services\Domain\SurveyQuestionFormatterService;
use App\Services\Domain\SurveyResponseNormalizerService;
use Illuminate\Support\Facades\Log;

class SurveyDeliveryService
{
    public function __construct(
        protected SinchService $sinchService,
        protected TranscriptionService $transcriptionService,
        protected ClaudeService $claudeService,
        protected PhoneNumberFormatterService $phoneFormatter,
        protected SurveyQuestionFormatterService $questionFormatter,
        protected SurveyResponseNormalizerService $responseNormalizer
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
            'recipient_type' => $recipientType === 'learner' ? Learner::class : User::class,
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
            'learner_id' => $delivery->recipient_type === Learner::class ? $delivery->recipient_id : null,
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
            'learner_id' => $delivery->recipient_type === Learner::class ? $delivery->recipient_id : null,
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
            'learner_id' => $delivery->recipient_type === Learner::class ? $delivery->recipient_id : null,
            'user_id' => $delivery->recipient_type === User::class ? $delivery->recipient_id : null,
            'status' => SurveyAttempt::STATUS_IN_PROGRESS,
            'response_channel' => SurveyAttempt::CHANNEL_VOICE,
            'delivery_id' => $delivery->id,
            'started_at' => now(),
        ]);

        // Generate initial greeting for the call
        $firstQuestion = $survey->questions[0] ?? null;
        $greeting = $this->questionFormatter->formatVoiceGreeting($survey->title, $firstQuestion ?? []);

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
            'learner_id' => $delivery->recipient_type === Learner::class ? $delivery->recipient_id : null,
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
            'learner_id' => $delivery->recipient_type === Learner::class ? $delivery->recipient_id : null,
            'user_id' => $delivery->recipient_type === User::class ? $delivery->recipient_id : null,
            'status' => SurveyAttempt::STATUS_IN_PROGRESS,
            'response_channel' => SurveyAttempt::CHANNEL_CHAT,
            'delivery_id' => $delivery->id,
            'started_at' => now(),
        ]);

        // Generate initial chat message
        $survey = $delivery->survey;
        $initialMessage = $this->questionFormatter->formatChatGreeting($survey->title, $survey->estimated_duration_minutes ?? 5);

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
        $normalizedResponse = $this->responseNormalizer->normalizeTextResponse($body, $currentQuestion);

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
        $nextMessage = $this->questionFormatter->formatQuestionForSms($nextQuestion);

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
        $normalizedResponse = $this->responseNormalizer->normalizeDtmfResponse($dtmfDigits, $currentQuestion);

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
        $nextTts = $this->questionFormatter->formatQuestionForTts($nextQuestion, $delivery->current_question_index + 1);

        return [
            'success' => true,
            'complete' => false,
            'tts' => $nextTts,
            'collect_dtmf' => true,
        ];
    }

    /**
     * Format phone number to E.164 format.
     */
    protected function formatPhoneNumber(string $phone): string
    {
        return $this->phoneFormatter->formatPhoneNumber($phone);
    }
}
