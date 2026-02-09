<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;

class DemoFeedbackController extends Controller
{
    public function store(Request $request, GoogleSheetsService $sheets)
    {
        $data = $request->all();

        $score = data_get($data, 'probability_score');
        if ($score === null) {
            $score = data_get($data, 'Probability Score');
        }

        $payload = [
            'Probability Score' => (int) ($score ?? 0),
            'Timestamp' => data_get($data, 'Timestamp') ?? now()->toISOString(),
            'Email' => data_get($data, 'Email') ?? data_get($data, 'email') ?? '',
            'First Name' => data_get($data, 'First Name') ?? data_get($data, 'first_name') ?? '',
            'Last Name' => data_get($data, 'Last Name') ?? data_get($data, 'last_name') ?? '',
            'Primary Value Trigger' => data_get($data, 'aha_moment_feature') ?? '',
            'Pain Discovery' => data_get($data, 'weekend_killer_task') ?? '',
            'Internal Champion Note' => data_get($data, 'viral_referral_note') ?? '',
            'Buying Confidence Signal' => data_get($data, 'district_buying_hurdle') ?? '',
            'Beta Builder Intent' => data_get($data, 'beta_builder_intent') ?? '',
        ];

        try {
            $sheets->appendRow($payload);
            \Log::info('Feedback submitted to Google Sheets', ['email' => $payload['Email']]);
        } catch (\Throwable $e) {
            \Log::error('Google Sheets feedback failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'error' => 'Failed to save feedback'], 500);
        }

        return response()->json(['success' => true]);
    }
}
