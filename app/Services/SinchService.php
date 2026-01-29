<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Conversation;
use App\Models\User;

class SinchService
{
    protected string $projectId;
    protected string $keyId;
    protected string $keySecret;
    protected string $phoneNumber;
    protected string $whatsappNumber;
    protected string $voiceUrl;
    protected string $smsUrl;
    protected string $whatsappUrl;

    public function __construct()
    {
        $this->projectId = config('services.sinch.project_id');
        $this->keyId = config('services.sinch.key_id');
        $this->keySecret = config('services.sinch.key_secret');
        $this->phoneNumber = config('services.sinch.phone_number');
        $this->whatsappNumber = config('services.sinch.whatsapp_number');
        $this->voiceUrl = config('services.sinch.voice_url');
        $this->smsUrl = config('services.sinch.sms_url');
        $this->whatsappUrl = config('services.sinch.whatsapp_url');
    }

    /**
     * Make an HTTP request to Sinch API.
     */
    protected function request(string $method, string $url, array $data = []): array
    {
        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->timeout(30)
                ->{$method}($url, $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('Sinch API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url,
            ]);

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Sinch API exception', [
                'message' => $e->getMessage(),
                'url' => $url,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ==========================================
    // VOICE CALLS
    // ==========================================

    /**
     * Initiate an outbound voice call.
     */
    public function initiateCall(string $toNumber, string $initialMessage): array
    {
        $response = $this->request('post', "{$this->voiceUrl}/calling/calls", [
            'method' => 'ttsCallout',
            'ttsCallout' => [
                'cli' => $this->phoneNumber,
                'destination' => [
                    'type' => 'number',
                    'endpoint' => $this->formatPhoneNumber($toNumber),
                ],
                'locale' => 'en-US',
                'text' => $initialMessage,
            ],
        ]);

        if ($response['success']) {
            // Create conversation record
            $conversation = Conversation::create([
                'channel' => 'voice',
                'direction' => 'outbound',
                'sinch_conversation_id' => $response['data']['callId'] ?? null,
                'phone_number' => $toNumber,
                'status' => 'initiated',
                'started_at' => now(),
            ]);

            $response['conversation_id'] = $conversation->_id;
        }

        return $response;
    }

    /**
     * End an active call.
     */
    public function endCall(string $callId): array
    {
        return $this->request('delete', "{$this->voiceUrl}/calling/calls/{$callId}");
    }

    /**
     * Get call details.
     */
    public function getCallDetails(string $callId): array
    {
        return $this->request('get', "{$this->voiceUrl}/calling/calls/{$callId}");
    }

    /**
     * Handle incoming call webhook.
     */
    public function handleIncomingCall(array $payload): array
    {
        $callId = $payload['callId'] ?? null;
        $from = $payload['from']['endpoint'] ?? null;

        // Create conversation record
        $conversation = Conversation::create([
            'channel' => 'voice',
            'direction' => 'inbound',
            'sinch_conversation_id' => $callId,
            'phone_number' => $from,
            'status' => 'initiated',
            'started_at' => now(),
        ]);

        // Try to find user by phone
        $user = User::where('phone', $from)->first();
        if ($user) {
            $conversation->update([
                'user_id' => $user->_id,
                'org_id' => $user->org_id,
            ]);
        }

        return [
            'success' => true,
            'conversation_id' => $conversation->_id,
            'user' => $user,
        ];
    }

    // ==========================================
    // SMS
    // ==========================================

    /**
     * Send an SMS message.
     */
    public function sendSms(string $toNumber, string $message): array
    {
        $response = $this->request('post', "{$this->smsUrl}/{$this->projectId}/batches", [
            'from' => $this->phoneNumber,
            'to' => [$this->formatPhoneNumber($toNumber)],
            'body' => $message,
        ]);

        if ($response['success']) {
            // Create or update conversation
            $conversation = Conversation::where('phone_number', $toNumber)
                ->where('channel', 'sms')
                ->where('status', '!=', 'completed')
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'channel' => 'sms',
                    'direction' => 'outbound',
                    'phone_number' => $toNumber,
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'messages' => [],
                ]);
            }

            $conversation->addMessage('sent', $message);
            $response['conversation_id'] = $conversation->_id;
        }

        return $response;
    }

    /**
     * Handle incoming SMS webhook.
     */
    public function handleIncomingSms(array $payload): array
    {
        $from = $payload['from'] ?? null;
        $body = $payload['body'] ?? '';

        // Find or create conversation
        $conversation = Conversation::where('phone_number', $from)
            ->where('channel', 'sms')
            ->where('status', '!=', 'completed')
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'channel' => 'sms',
                'direction' => 'inbound',
                'phone_number' => $from,
                'status' => 'in_progress',
                'started_at' => now(),
                'messages' => [],
            ]);

            // Try to find user by phone
            $user = User::where('phone', $from)->first();
            if ($user) {
                $conversation->update([
                    'user_id' => $user->_id,
                    'org_id' => $user->org_id,
                ]);
            }
        }

        $conversation->addMessage('received', $body);

        return [
            'success' => true,
            'conversation_id' => $conversation->_id,
            'message' => $body,
        ];
    }

    // ==========================================
    // WHATSAPP
    // ==========================================

    /**
     * Send a WhatsApp message.
     */
    public function sendWhatsApp(string $toNumber, string $message): array
    {
        $response = $this->request('post', "{$this->whatsappUrl}/messages:send", [
            'app_id' => $this->projectId,
            'recipient' => [
                'contact_id' => $this->formatPhoneNumber($toNumber),
            ],
            'message' => [
                'text_message' => [
                    'text' => $message,
                ],
            ],
        ]);

        if ($response['success']) {
            // Create or update conversation
            $conversation = Conversation::where('phone_number', $toNumber)
                ->where('channel', 'whatsapp')
                ->where('status', '!=', 'completed')
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'channel' => 'whatsapp',
                    'direction' => 'outbound',
                    'phone_number' => $toNumber,
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'messages' => [],
                ]);
            }

            $conversation->addMessage('sent', $message);
            $response['conversation_id'] = $conversation->_id;
        }

        return $response;
    }

    /**
     * Handle incoming WhatsApp webhook.
     */
    public function handleIncomingWhatsApp(array $payload): array
    {
        $from = $payload['contact_id'] ?? null;
        $body = $payload['message']['text_message']['text'] ?? '';

        // Find or create conversation
        $conversation = Conversation::where('phone_number', $from)
            ->where('channel', 'whatsapp')
            ->where('status', '!=', 'completed')
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'channel' => 'whatsapp',
                'direction' => 'inbound',
                'phone_number' => $from,
                'status' => 'in_progress',
                'started_at' => now(),
                'messages' => [],
            ]);

            // Try to find user by phone
            $user = User::where('phone', $from)->first();
            if ($user) {
                $conversation->update([
                    'user_id' => $user->_id,
                    'org_id' => $user->org_id,
                ]);
            }
        }

        $conversation->addMessage('received', $body);

        return [
            'success' => true,
            'conversation_id' => $conversation->_id,
            'message' => $body,
        ];
    }

    // ==========================================
    // UTILITIES
    // ==========================================

    /**
     * Format phone number to E.164 format.
     */
    protected function formatPhoneNumber(string $number): string
    {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // Add country code if not present (assuming US)
        if (strlen($number) === 10) {
            $number = '1' . $number;
        }

        return '+' . $number;
    }

    /**
     * Check if services are configured.
     */
    public function healthCheck(): array
    {
        $issues = [];

        if (empty($this->keyId) || empty($this->keySecret)) {
            $issues[] = 'Sinch API credentials not configured';
        }

        if (empty($this->phoneNumber)) {
            $issues[] = 'Sinch phone number not configured';
        }

        return [
            'healthy' => empty($issues),
            'issues' => $issues,
        ];
    }
}
