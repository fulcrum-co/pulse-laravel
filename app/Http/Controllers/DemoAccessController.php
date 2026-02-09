<?php

namespace App\Http\Controllers;

use App\Models\DemoAccessToken;
use App\Models\DemoLead;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DemoAccessController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'org_name' => 'nullable|string|max:255',
            'org_url' => 'nullable|string|max:255',
            'org_size' => 'nullable|string|max:255',
        ]);

        $lead = DemoLead::create([
            ...$data,
            'org_size_note' => 'Includes everyone in the organization’s scope: administrators, staff, participants, and families/guardians. If applicable, include after‑school volunteers/support.',
            'source' => 'pilot',
            'source_url' => $request->headers->get('referer'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->sendToZohoFlow($data);
        $this->sendToGoogleSheets($data);

        $prospectUser = $this->getProspectUser();
        Auth::login($prospectUser);
        session()->put('demo_role_override', 'school_admin');
        session()->forget('prospect_tours_cleared');

        $token = $this->issueAccessToken($data['email']);

        return redirect()->route('dashboard')->with('demo_access_token', $token);
    }

    public function bypass()
    {
        // Log out any existing non-prospect session first
        if (Auth::check() && ! Auth::user()->isProspect()) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
        }

        $prospectUser = $this->getProspectUser();
        Auth::login($prospectUser);
        session()->put('demo_role_override', 'school_admin');
        session()->forget('prospect_tours_cleared');

        return redirect()->route('dashboard');
    }

    public function magic(string $token)
    {
        $record = DemoAccessToken::where('token', $token)->first();

        if (! $record || $record->expires_at->isPast()) {
            abort(403);
        }

        $record->update(['used_at' => now()]);

        $prospectUser = $this->getProspectUser();
        Auth::login($prospectUser);
        session()->put('demo_role_override', 'school_admin');
        session()->forget('prospect_tours_cleared');

        return redirect()->route('dashboard');
    }

    public function zohoWebhook(Request $request)
    {
        $payload = $request->all();

        $email = data_get($payload, 'email')
            ?? data_get($payload, 'Email')
            ?? data_get($payload, 'Email Address');

        if (! $email) {
            return response()->json(['error' => 'Missing email'], 422);
        }

        DemoLead::create([
            'first_name' => data_get($payload, 'first_name') ?? data_get($payload, 'First Name') ?? '',
            'last_name' => data_get($payload, 'last_name') ?? data_get($payload, 'Last Name') ?? '',
            'email' => $email,
            'phone' => data_get($payload, 'phone') ?? data_get($payload, 'Phone') ?? null,
            'org_name' => data_get($payload, 'org_name') ?? data_get($payload, 'Organization') ?? null,
            'org_url' => data_get($payload, 'org_url') ?? data_get($payload, 'Organization URL') ?? null,
            'org_size' => data_get($payload, 'org_size') ?? data_get($payload, 'Organization Size') ?? null,
            'org_size_note' => 'Includes everyone in the organization’s scope: administrators, staff, participants, and families/guardians. If applicable, include after‑school volunteers/support.',
            'source' => 'zoho',
            'source_url' => $request->headers->get('referer'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $token = $this->issueAccessToken($email);

        return response()->json([
            'success' => true,
            'magic_link' => route('demo.magic', ['token' => $token]),
            'expires_in_days' => 5,
        ]);
    }

    protected function sendToZohoFlow(array $data): void
    {
        $webhookUrl = config('services.zoho.flow_webhook_url');

        if (! $webhookUrl) {
            \Log::warning('Zoho Flow: ZOHO_FLOW_WEBHOOK_URL not configured');

            return;
        }

        try {
            Http::asJson()->timeout(5)->post($webhookUrl, [
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'org_name' => $data['org_name'] ?? null,
                'org_url' => $data['org_url'] ?? null,
                'org_size' => $data['org_size'] ?? null,
                'source' => 'pilot',
                // Zoho-friendly aliases for easier mapping in Flow.
                'First Name' => $data['first_name'] ?? null,
                'Last Name' => $data['last_name'] ?? null,
                'Email' => $data['email'] ?? null,
                'Phone' => $data['phone'] ?? null,
                'Company' => $data['org_name'] ?? null,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Zoho Flow webhook failed', ['error' => $e->getMessage()]);
        }
    }

    protected function sendToGoogleSheets(array $data): void
    {
        try {
            app(\App\Services\GoogleSheetsService::class)->appendRow([
                'Timestamp' => now()->toISOString(),
                'First Name' => $data['first_name'] ?? '',
                'Last Name' => $data['last_name'] ?? '',
                'Email' => $data['email'] ?? '',
                'Phone' => $data['phone'] ?? '',
                'Organization' => $data['org_name'] ?? '',
                'Organization URL' => $data['org_url'] ?? '',
                'Organization Size' => $data['org_size'] ?? '',
                'Source' => 'pilot',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Google Sheets submission failed', ['error' => $e->getMessage()]);
        }
    }

    protected function issueAccessToken(string $email): string
    {
        $token = Str::random(64);

        DemoAccessToken::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addDays(5),
        ]);

        return $token;
    }

    protected function getProspectUser(): User
    {
        $email = config('app.demo_prospect_email', 'prospect@pulse.local');

        // Use the first school org — that's where all seeded demo data lives
        $org = Organization::where('org_type', 'school')->first()
            ?? Organization::first();

        if (! $org) {
            $org = Organization::create([
                'org_type' => 'organization',
                'org_name' => 'Demo Organization',
                'active' => true,
            ]);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            // Keep prospect synced to the school org with seeded data
            if ($user->org_id !== $org->id) {
                $user->update(['org_id' => $org->id, 'current_org_id' => $org->id]);
            }

            return $user;
        }

        return User::create([
            'org_id' => $org->id,
            'current_org_id' => $org->id,
            'first_name' => 'Prospect',
            'last_name' => 'Demo',
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'primary_role' => 'prospect',
            'active' => true,
        ]);
    }
}
