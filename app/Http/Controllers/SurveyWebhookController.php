<?php

namespace App\Http\Controllers;

use App\Services\SurveyDeliveryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SurveyWebhookController extends Controller
{
    public function __construct(
        protected SurveyDeliveryService $deliveryService
    ) {}

    /**
     * Handle Sinch voice call webhook.
     */
    public function handleVoice(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Sinch voice webhook received', ['payload' => $payload]);

        // Handle different Sinch voice events
        $event = $payload['event'] ?? null;

        switch ($event) {
            case 'ice':
                // Incoming Call Event - not used for outbound surveys
                return $this->handleIncomingCall($payload);

            case 'ace':
                // Answered Call Event
                return $this->handleAnsweredCall($payload);

            case 'dice':
                // Disconnected Call Event
                return $this->handleDisconnectedCall($payload);

            case 'pie':
                // Prompt Input Event (DTMF response)
                return $this->handlePromptInput($payload);

            default:
                Log::warning('Unknown Sinch voice event', ['event' => $event]);
                return response()->json(['status' => 'ignored']);
        }
    }

    /**
     * Handle Sinch SMS webhook.
     */
    public function handleSms(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Sinch SMS webhook received', ['payload' => $payload]);

        // Sinch SMS webhook format
        $from = $payload['from'] ?? null;
        $body = $payload['body'] ?? $payload['message'] ?? null;

        if (!$from || !$body) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $result = $this->deliveryService->handleSmsResponse($from, $body);

        if ($result['success']) {
            // If there's a response message to send back
            if (isset($result['message'])) {
                // The delivery service handles sending the next question
            }

            return response()->json(['status' => 'processed']);
        }

        return response()->json(['status' => 'no_active_survey']);
    }

    /**
     * Handle incoming call event (not typically used for outbound surveys).
     */
    protected function handleIncomingCall(array $payload): JsonResponse
    {
        // For incoming calls, we might want to redirect to a survey
        return response()->json([
            'action' => [
                'name' => 'hangup',
            ],
        ]);
    }

    /**
     * Handle answered call event.
     */
    protected function handleAnsweredCall(array $payload): JsonResponse
    {
        $callId = $payload['callId'] ?? null;

        // The greeting was already sent when the call was initiated
        // Now we wait for DTMF input
        return response()->json([
            'action' => [
                'name' => 'continue',
            ],
            'instructions' => [
                [
                    'name' => 'playFiles',
                    'ids' => ['#tts[You can respond by pressing numbers on your keypad]'],
                    'locale' => 'en-US',
                ],
                [
                    'name' => 'runMenu',
                    'barge' => true,
                    'menus' => [
                        [
                            'id' => 'main',
                            'mainPrompt' => '#tts[Please enter your response now]',
                            'maxDigits' => 1,
                            'timeoutMills' => 10000,
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Handle disconnected call event.
     */
    protected function handleDisconnectedCall(array $payload): JsonResponse
    {
        $callId = $payload['callId'] ?? null;

        Log::info('Survey call disconnected', ['call_id' => $callId]);

        return response()->json(['status' => 'acknowledged']);
    }

    /**
     * Handle DTMF prompt input.
     */
    protected function handlePromptInput(array $payload): JsonResponse
    {
        $callId = $payload['callId'] ?? null;
        $dtmf = $payload['menuResult']['value'] ?? null;

        if (!$callId || !$dtmf) {
            return response()->json([
                'action' => ['name' => 'continue'],
            ]);
        }

        $result = $this->deliveryService->handleVoiceResponse($callId, $dtmf);

        if ($result['complete'] ?? false) {
            // Survey complete, say goodbye and hang up
            return response()->json([
                'instructions' => [
                    [
                        'name' => 'playFiles',
                        'ids' => ['#tts[' . $result['tts'] . ']'],
                        'locale' => 'en-US',
                    ],
                    [
                        'name' => 'hangup',
                    ],
                ],
            ]);
        }

        // More questions to ask
        return response()->json([
            'instructions' => [
                [
                    'name' => 'playFiles',
                    'ids' => ['#tts[' . $result['tts'] . ']'],
                    'locale' => 'en-US',
                ],
                [
                    'name' => 'runMenu',
                    'barge' => true,
                    'menus' => [
                        [
                            'id' => 'question',
                            'mainPrompt' => '#tts[Enter your response]',
                            'maxDigits' => 1,
                            'timeoutMills' => 10000,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
