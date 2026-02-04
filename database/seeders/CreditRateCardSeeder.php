<?php

namespace Database\Seeders;

use App\Models\CreditRateCard;
use Illuminate\Database\Seeder;

class CreditRateCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = [
            // AI Services (Claude)
            [
                'action_type' => 'ai_analysis',
                'display_name' => 'AI Narrative Analysis',
                'category' => 'ai',
                'vendor_cost' => 0.003,      // $0.003 per 1K input tokens (Claude 3 Haiku)
                'vendor_unit' => 'per_1k_tokens',
                'credit_cost' => 9,          // 0.003 * 3 * 1000 = 9 credits
            ],
            [
                'action_type' => 'ai_summary',
                'display_name' => 'AI Summary Generation',
                'category' => 'ai',
                'vendor_cost' => 0.015,      // $0.015 per 1K output tokens (Claude 3 Sonnet)
                'vendor_unit' => 'per_1k_tokens',
                'credit_cost' => 45,         // 0.015 * 3 * 1000 = 45 credits
            ],
            [
                'action_type' => 'ai_course_generation',
                'display_name' => 'AI Course Generation',
                'category' => 'ai',
                'vendor_cost' => 0.075,      // Higher cost for complex generation
                'vendor_unit' => 'per_1k_tokens',
                'credit_cost' => 225,        // 0.075 * 3 * 1000 = 225 credits
            ],

            // Transcription Services
            [
                'action_type' => 'transcription_minute',
                'display_name' => 'Voice Transcription',
                'category' => 'voice',
                'vendor_cost' => 0.006,      // $0.006 per minute (Whisper)
                'vendor_unit' => 'per_minute',
                'credit_cost' => 18,         // 0.006 * 3 * 1000 = 18 credits
            ],
            [
                'action_type' => 'transcription_assemblyai',
                'display_name' => 'Premium Transcription (AssemblyAI)',
                'category' => 'voice',
                'vendor_cost' => 0.015,      // $0.015 per minute
                'vendor_unit' => 'per_minute',
                'credit_cost' => 45,         // 0.015 * 3 * 1000 = 45 credits
            ],

            // SMS/Telecom (Sinch)
            [
                'action_type' => 'sms_outbound',
                'display_name' => 'SMS (Outbound)',
                'category' => 'telecom',
                'vendor_cost' => 0.0079,     // ~$0.0079 per message
                'vendor_unit' => 'per_message',
                'credit_cost' => 24,         // 0.0079 * 3 * 1000 ≈ 24 credits
            ],
            [
                'action_type' => 'sms_inbound',
                'display_name' => 'SMS (Inbound)',
                'category' => 'telecom',
                'vendor_cost' => 0.0075,     // ~$0.0075 per message
                'vendor_unit' => 'per_message',
                'credit_cost' => 23,         // 0.0075 * 3 * 1000 ≈ 23 credits
            ],
            [
                'action_type' => 'whatsapp_outbound',
                'display_name' => 'WhatsApp (Outbound)',
                'category' => 'telecom',
                'vendor_cost' => 0.015,      // ~$0.015 per message
                'vendor_unit' => 'per_message',
                'credit_cost' => 45,         // 0.015 * 3 * 1000 = 45 credits
            ],
            [
                'action_type' => 'whatsapp_inbound',
                'display_name' => 'WhatsApp (Inbound)',
                'category' => 'telecom',
                'vendor_cost' => 0.005,      // ~$0.005 per message
                'vendor_unit' => 'per_message',
                'credit_cost' => 15,         // 0.005 * 3 * 1000 = 15 credits
            ],
        ];

        foreach ($rates as $rate) {
            CreditRateCard::updateOrCreate(
                ['action_type' => $rate['action_type']],
                array_merge($rate, ['active' => true])
            );
        }

        $this->command->info('Credit rate cards seeded successfully.');
    }
}
