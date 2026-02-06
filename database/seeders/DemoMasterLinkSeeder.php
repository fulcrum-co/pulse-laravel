<?php

namespace Database\Seeders;

use App\Models\DemoAccessToken;
use Illuminate\Database\Seeder;

class DemoMasterLinkSeeder extends Seeder
{
    /**
     * Seed the demo master link token.
     */
    public function run(): void
    {
        $token = 'demomasterlink' . str_repeat('0', 50);

        // Only create if it doesn't exist
        if (DemoAccessToken::where('token', $token)->exists()) {
            $this->command->info('Demo master link already exists.');
            return;
        }

        DemoAccessToken::create([
            'token' => $token,
            'email' => 'demo@pulseconnect.us',
            'expires_at' => now()->addYears(10),
        ]);

        $this->command->info('Demo master link created!');
        $this->command->info('URL: https://pilot.pulseconnect.us/demo/access/' . $token);
    }
}
