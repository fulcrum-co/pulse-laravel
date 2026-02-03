<?php

declare(strict_types=1);

namespace App\Services\Domain;

use stdClass;

class DemoDataProviderService
{
    protected static array $demoProviders = [
        [
            'id' => 'demo_1',
            'name' => 'Dr. Sarah Chen',
            'display_name' => 'Dr. Sarah Chen, LCSW',
            'provider_type' => 'therapist',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=150&h=150&fit=crop&crop=face',
            'verified' => true,
            'online' => true,
        ],
        [
            'id' => 'demo_2',
            'name' => 'James Miller',
            'display_name' => 'James Miller - Math Tutor',
            'provider_type' => 'tutor',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face',
            'verified' => true,
            'online' => false,
        ],
        [
            'id' => 'demo_3',
            'name' => 'Dr. Emily Rodriguez',
            'display_name' => 'Dr. Emily Rodriguez, PhD',
            'provider_type' => 'psychologist',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1594824476967-48c8b964273f?w=150&h=150&fit=crop&crop=face',
            'verified' => true,
            'online' => true,
        ],
        [
            'id' => 'demo_4',
            'name' => 'Marcus Thompson',
            'display_name' => 'Marcus Thompson - College Advisor',
            'provider_type' => 'support_person',
            'thumbnail_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
            'verified' => false,
            'online' => true,
        ],
    ];

    protected static array $demoLearners = [
        ['id' => 'learner_101', 'name' => 'Emma Johnson', 'level' => '10th Level'],
        ['id' => 'learner_102', 'name' => 'Liam Williams', 'level' => '11th Level'],
        ['id' => 'learner_103', 'name' => 'Sophia Davis', 'level' => '9th Level'],
    ];

    /**
     * Get demo providers.
     */
    public function getProviders(): array
    {
        return self::$demoProviders;
    }

    /**
     * Get demo participants.
     */
    public function getLearners(): array
    {
        return self::$demoLearners;
    }

    /**
     * Create a demo provider object.
     */
    public function createProvider(array $data): stdClass
    {
        $obj = new stdClass;
        $obj->id = $data['id'] ?? '';
        $obj->name = $data['name'] ?? '';
        $obj->display_name = $data['display_name'] ?? $data['name'] ?? '';
        $obj->provider_type = $data['provider_type'] ?? '';
        $obj->thumbnail_url = $data['thumbnail_url'] ?? '';
        $obj->verified = $data['verified'] ?? false;
        $obj->online = $data['online'] ?? false;

        return $obj;
    }

    /**
     * Create a demo participant object.
     */
    public function createLearner(array $data): stdClass
    {
        $obj = new stdClass;
        $obj->id = $data['id'] ?? '';
        $obj->name = $data['name'] ?? '';
        $obj->full_name = $data['name'] ?? '';
        $obj->level = $data['level'] ?? '';

        return $obj;
    }

    /**
     * Create a demo conversation object.
     */
    public function createConversation(array $data): stdClass
    {
        $obj = new stdClass;
        $obj->id = $data['id'] ?? '';
        $obj->provider = $this->createProvider($data['provider'] ?? []);
        $obj->participant = isset($data['participant']) && $data['participant'] ? $this->createLearner($data['participant']) : null;
        $obj->last_message_preview = $data['last_message'] ?? '';
        $obj->last_message_at = $data['last_message_at'] ?? now();
        $obj->unread_count_initiator = $data['unread_count'] ?? 0;
        $obj->stream_channel_id = $data['stream_channel_id'] ?? '';
        $obj->stream_channel_type = 'messaging';
        $obj->provider_id = $data['provider']['id'] ?? '';

        return $obj;
    }

    /**
     * Check if provider is verified.
     */
    public function isProviderVerified(stdClass $provider): bool
    {
        return $provider->verified ?? false;
    }

    /**
     * Check if provider is online.
     */
    public function isProviderOnline(stdClass $provider): bool
    {
        return $provider->online ?? false;
    }
}
