<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\Provider;
use App\Models\Participant;
use App\Models\User;

/**
 * StreamIdGeneratorService
 *
 * Handles ID generation and channel ID construction for Stream Chat service.
 * Ensures consistent formatting of user IDs and channel identifiers across
 * the application.
 */
class StreamIdGeneratorService
{
    /**
     * Provider ID prefix for Stream.
     */
    private const PROVIDER_PREFIX = 'provider_';

    /**
     * User ID prefix for Stream.
     */
    private const USER_PREFIX = 'user_';

    /**
     * Participant ID prefix for Stream.
     */
    private const STUDENT_PREFIX = 'learner_';

    /**
     * System user ID for system messages.
     */
    private const SYSTEM_USER_ID = 'system';

    /**
     * Generate Stream user ID for a provider.
     *
     * @param  Provider  $provider  The provider model
     * @return string Stream user ID
     */
    public function getProviderStreamId(Provider $provider): string
    {
        return self::PROVIDER_PREFIX . $provider->id;
    }

    /**
     * Generate Stream user ID for a user.
     *
     * @param  User  $user  The user model
     * @return string Stream user ID
     */
    public function getUserStreamId(User $user): string
    {
        return self::USER_PREFIX . $user->id;
    }

    /**
     * Generate Stream user ID for a participant.
     *
     * @param  Participant  $participant  The participant model
     * @return string Stream user ID
     */
    public function getLearnerStreamId(Participant $participant): string
    {
        return self::STUDENT_PREFIX . $participant->id;
    }

    /**
     * Generate Stream user ID for an initiator (User or Participant).
     *
     * Determines the correct ID prefix based on model type.
     *
     * @param  User|Participant  $initiator  The initiator model
     * @return string Stream user ID
     */
    public function getInitiatorStreamId(User|Participant $initiator): string
    {
        if ($initiator instanceof User) {
            return $this->getUserStreamId($initiator);
        }

        return $this->getLearnerStreamId($initiator);
    }

    /**
     * Generate channel ID for provider conversation.
     *
     * Creates a unique channel ID combining provider and initiator information.
     *
     * @param  Provider  $provider  The provider
     * @param  User|Participant  $initiator  The conversation initiator
     * @return string Unique channel ID
     *
     * @example
     *   'provider_123_user_456'
     *   'provider_123_learner_789'
     */
    public function generateProviderChannelId(Provider $provider, User|Participant $initiator): string
    {
        $initiatorPrefix = $initiator instanceof User ? 'user' : 'participant';

        return "provider_{$provider->id}_{$initiatorPrefix}_{$initiator->id}";
    }

    /**
     * Extract provider ID from Stream user ID.
     *
     * @param  string  $streamId  Stream user ID
     * @return string|null Provider ID or null if not a provider ID
     */
    public function extractProviderIdFromStreamId(string $streamId): ?string
    {
        if (str_starts_with($streamId, self::PROVIDER_PREFIX)) {
            return substr($streamId, strlen(self::PROVIDER_PREFIX));
        }

        return null;
    }

    /**
     * Extract user ID from Stream user ID.
     *
     * @param  string  $streamId  Stream user ID
     * @return string|null User ID or null if not a user ID
     */
    public function extractUserIdFromStreamId(string $streamId): ?string
    {
        if (str_starts_with($streamId, self::USER_PREFIX)) {
            return substr($streamId, strlen(self::USER_PREFIX));
        }

        return null;
    }

    /**
     * Extract participant ID from Stream user ID.
     *
     * @param  string  $streamId  Stream user ID
     * @return string|null Participant ID or null if not a participant ID
     */
    public function extractLearnerIdFromStreamId(string $streamId): ?string
    {
        if (str_starts_with($streamId, self::STUDENT_PREFIX)) {
            return substr($streamId, strlen(self::STUDENT_PREFIX));
        }

        return null;
    }

    /**
     * Determine entity type from Stream user ID.
     *
     * @param  string  $streamId  Stream user ID
     * @return string|null Entity type: 'provider', 'user', 'participant', or null
     */
    public function getEntityTypeFromStreamId(string $streamId): ?string
    {
        if (str_starts_with($streamId, self::PROVIDER_PREFIX)) {
            return 'provider';
        }
        if (str_starts_with($streamId, self::USER_PREFIX)) {
            return 'user';
        }
        if (str_starts_with($streamId, self::STUDENT_PREFIX)) {
            return 'participant';
        }

        return null;
    }

    /**
     * Get the system user ID for system messages.
     *
     * @return string System user ID
     */
    public function getSystemUserId(): string
    {
        return self::SYSTEM_USER_ID;
    }

    /**
     * Check if a Stream ID is a system ID.
     *
     * @param  string  $streamId  Stream user ID
     * @return bool True if this is the system user ID
     */
    public function isSystemId(string $streamId): bool
    {
        return $streamId === self::SYSTEM_USER_ID;
    }
}
