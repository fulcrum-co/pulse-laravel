<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Provider;
use App\Models\ProviderConversation;
use App\Models\Learner;
use App\Models\User;
use App\Services\Domain\StreamIdGeneratorService;
use Illuminate\Support\Facades\Log;

class StreamChatService
{
    protected mixed $client = null;

    protected ?string $apiKey = null;

    protected ?string $apiSecret = null;

    protected bool $sdkAvailable = false;

    protected StreamIdGeneratorService $idGenerator;

    public function __construct(StreamIdGeneratorService $idGenerator)
    {
        $this->idGenerator = $idGenerator;
        $this->apiKey = config('services.stream.api_key');
        $this->apiSecret = config('services.stream.api_secret');

        // Check if SDK is available
        $this->sdkAvailable = class_exists(\GetStream\StreamChat\Client::class);

        if ($this->sdkAvailable && $this->apiKey && $this->apiSecret) {
            try {
                $this->client = new \GetStream\StreamChat\Client($this->apiKey, $this->apiSecret);
            } catch (\Throwable $e) {
                Log::warning('StreamChatService: Failed to initialize client', ['error' => $e->getMessage()]);
                $this->client = null;
            }
        }
    }

    /**
     * Check if the service is configured and SDK is available.
     */
    public function isConfigured(): bool
    {
        return $this->sdkAvailable && ! empty($this->apiKey) && ! empty($this->apiSecret) && $this->client !== null;
    }

    /**
     * Check if SDK is available (for graceful degradation).
     */
    public function isSdkAvailable(): bool
    {
        return $this->sdkAvailable;
    }

    /**
     * Create or update a user in Stream.
     */
    public function createOrUpdateUser(string $userId, array $data): void
    {
        if (! $this->isConfigured()) {
            Log::warning('StreamChatService: Not configured, skipping user creation');

            return;
        }

        try {
            $this->client->upsertUser([
                'id' => $userId,
                'name' => $data['name'] ?? $userId,
                'image' => $data['image'] ?? null,
                'role' => $data['role'] ?? 'user',
                ...array_filter($data, fn ($key) => ! in_array($key, ['name', 'image', 'role']), ARRAY_FILTER_USE_KEY),
            ]);
        } catch (\Exception $e) {
            Log::error('StreamChatService: Failed to create/update user', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate a user token for client-side authentication.
     */
    public function generateUserToken(string $userId): string
    {
        if (! $this->isConfigured()) {
            throw new \Exception('StreamChatService is not configured');
        }

        return $this->client->createToken($userId);
    }

    /**
     * Delete a user from Stream.
     */
    public function deleteUser(string $userId): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        try {
            $this->client->deleteUser($userId, ['mark_messages_deleted' => true]);
        } catch (\Exception $e) {
            Log::error('StreamChatService: Failed to delete user', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a channel for provider conversation.
     */
    public function createProviderChannel(Provider $provider, User|Learner $initiator): array
    {
        if (! $this->isConfigured()) {
            throw new \Exception('StreamChatService is not configured');
        }

        // Generate IDs
        $providerId = $this->getProviderStreamId($provider);
        $initiatorId = $this->getInitiatorStreamId($initiator);
        $channelId = $this->generateChannelId($provider, $initiator);

        // Ensure both users exist in Stream
        $this->ensureUserExists($provider);
        $this->ensureUserExists($initiator);

        try {
            $channel = $this->client->Channel('messaging', $channelId);
            $response = $channel->create($initiatorId, [
                'members' => [$providerId, $initiatorId],
                'created_by_id' => $initiatorId,
                'provider_id' => $provider->id,
                'provider_name' => $provider->display_name,
            ]);

            return [
                'channel_id' => $channelId,
                'channel_type' => 'messaging',
                'response' => $response,
            ];
        } catch (\Exception $e) {
            Log::error('StreamChatService: Failed to create channel', [
                'channel_id' => $channelId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get or create a channel.
     */
    public function getOrCreateChannel(string $channelType, string $channelId): array
    {
        if (! $this->isConfigured()) {
            throw new \Exception('StreamChatService is not configured');
        }

        try {
            $channel = $this->client->Channel($channelType, $channelId);
            $response = $channel->query(['watch' => false]);

            return [
                'channel_id' => $channelId,
                'channel_type' => $channelType,
                'exists' => true,
                'response' => $response,
            ];
        } catch (\Exception $e) {
            return [
                'channel_id' => $channelId,
                'channel_type' => $channelType,
                'exists' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Archive a channel (hide from lists but keep history).
     */
    public function archiveChannel(string $channelType, string $channelId): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        try {
            $channel = $this->client->Channel($channelType, $channelId);
            $channel->update(['archived' => true]);
        } catch (\Exception $e) {
            Log::error('StreamChatService: Failed to archive channel', [
                'channel_id' => $channelId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send a system message to a channel.
     */
    public function sendSystemMessage(string $channelType, string $channelId, string $text): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        try {
            $channel = $this->client->Channel($channelType, $channelId);
            $channel->sendMessage([
                'text' => $text,
                'user_id' => 'system',
            ], 'system');
        } catch (\Exception $e) {
            Log::error('StreamChatService: Failed to send system message', [
                'channel_id' => $channelId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get messages for a channel.
     */
    public function getChannelMessages(string $channelType, string $channelId, int $limit = 50): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $channel = $this->client->Channel($channelType, $channelId);
            $response = $channel->query([
                'messages' => ['limit' => $limit],
            ]);

            return $response['messages'] ?? [];
        } catch (\Exception $e) {
            Log::error('StreamChatService: Failed to get messages', [
                'channel_id' => $channelId,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Add a member to a channel.
     */
    public function addMember(string $channelType, string $channelId, string $userId): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        try {
            $channel = $this->client->Channel($channelType, $channelId);
            $channel->addMembers([$userId]);
        } catch (\Exception $e) {
            Log::error('StreamChatService: Failed to add member', [
                'channel_id' => $channelId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove a member from a channel.
     */
    public function removeMember(string $channelType, string $channelId, string $userId): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        try {
            $channel = $this->client->Channel($channelType, $channelId);
            $channel->removeMembers([$userId]);
        } catch (\Exception $e) {
            Log::error('StreamChatService: Failed to remove member', [
                'channel_id' => $channelId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle incoming webhook from Stream.
     */
    public function handleWebhook(array $payload): void
    {
        $type = $payload['type'] ?? null;

        switch ($type) {
            case 'message.new':
                $this->handleNewMessage($payload);
                break;
            case 'message.read':
                $this->handleMessageRead($payload);
                break;
            case 'channel.created':
                Log::info('StreamChatService: Channel created', ['payload' => $payload]);
                break;
            default:
                Log::debug('StreamChatService: Unhandled webhook type', ['type' => $type]);
        }
    }

    /**
     * Verify webhook signature.
     */
    public function verifyWebhookSignature(string $body, string $signature): bool
    {
        if (! $this->apiSecret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $body, $this->apiSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle new message webhook.
     */
    protected function handleNewMessage(array $payload): void
    {
        $channelId = $payload['channel_id'] ?? null;
        $message = $payload['message'] ?? [];
        $user = $payload['user'] ?? [];

        if (! $channelId) {
            return;
        }

        // Find the conversation
        $conversation = ProviderConversation::where('stream_channel_id', $channelId)->first();

        if (! $conversation) {
            Log::warning('StreamChatService: Conversation not found for channel', ['channel_id' => $channelId]);

            return;
        }

        // Update last message info
        $senderType = str_starts_with($user['id'] ?? '', 'provider_') ? 'provider' : 'initiator';
        $conversation->updateLastMessage(
            $message['text'] ?? '',
            $senderType === 'provider' ? Provider::class : ($conversation->initiator_type),
            $senderType === 'provider' ? $conversation->provider_id : $conversation->initiator_id
        );

        // Increment unread count for the other party
        if ($senderType === 'provider') {
            $conversation->incrementInitiatorUnread();
        } else {
            $conversation->incrementProviderUnread();

            // Trigger notification to provider if they're offline
            if ($conversation->canSendNotification()) {
                // This would dispatch a job to send notification
                // SendProviderNotificationJob::dispatch($conversation, $message);
            }
        }
    }

    /**
     * Handle message read webhook.
     */
    protected function handleMessageRead(array $payload): void
    {
        $channelId = $payload['channel_id'] ?? null;
        $user = $payload['user'] ?? [];

        if (! $channelId) {
            return;
        }

        $conversation = ProviderConversation::where('stream_channel_id', $channelId)->first();

        if (! $conversation) {
            return;
        }

        // Reset unread count for the reader
        $isProvider = str_starts_with($user['id'] ?? '', 'provider_');

        if ($isProvider) {
            $conversation->markReadByProvider();
        } else {
            $conversation->markReadByInitiator();
        }
    }

    /**
     * Ensure a user exists in Stream.
     */
    protected function ensureUserExists(Provider|User|Learner $entity): void
    {
        if ($entity instanceof Provider) {
            $this->createOrUpdateUser($this->idGenerator->getProviderStreamId($entity), [
                'name' => $entity->display_name,
                'image' => $entity->thumbnail_url,
                'role' => 'provider',
                'provider_type' => $entity->provider_type,
            ]);
        } elseif ($entity instanceof User) {
            $this->createOrUpdateUser($this->idGenerator->getUserStreamId($entity), [
                'name' => $entity->full_name,
                'image' => $entity->avatar_url,
                'role' => $entity->primary_role,
            ]);
        } elseif ($entity instanceof Learner) {
            $this->createOrUpdateUser($this->idGenerator->getLearnerStreamId($entity), [
                'name' => $entity->full_name,
                'role' => 'learner',
            ]);
        }
    }

    /**
     * Get Stream user ID for a provider.
     */
    public function getProviderStreamId(Provider $provider): string
    {
        return $this->idGenerator->getProviderStreamId($provider);
    }

    /**
     * Get Stream user ID for a user.
     */
    public function getUserStreamId(User $user): string
    {
        return $this->idGenerator->getUserStreamId($user);
    }

    /**
     * Get Stream user ID for a learner.
     */
    public function getLearnerStreamId(Learner $learner): string
    {
        return $this->idGenerator->getLearnerStreamId($learner);
    }

    /**
     * Get Stream user ID for an initiator (User or Learner).
     */
    public function getInitiatorStreamId(User|Learner $initiator): string
    {
        return $this->idGenerator->getInitiatorStreamId($initiator);
    }

    /**
     * Generate channel ID for provider conversation.
     */
    public function generateChannelId(Provider $provider, User|Learner $initiator): string
    {
        return $this->idGenerator->generateProviderChannelId($provider, $initiator);
    }

    /**
     * Get the underlying Stream client (for advanced use).
     */
    public function getClient(): mixed
    {
        return $this->client;
    }
}
