<?php

namespace App\Http\Controllers;

use App\Models\DemoAccessToken;
use App\Models\DemoLead;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $prospectUser = $this->getProspectUser();
        Auth::login($prospectUser);

        $token = $this->issueAccessToken($data['email']);

        return redirect()->route('dashboard')->with('demo_access_token', $token);
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
        $org = Organization::first();

        if (! $org) {
            $org = Organization::create([
                'org_type' => 'organization',
                'org_name' => 'Demo Organization',
                'active' => true,
            ]);
        }

        $user = User::where('email', $email)->first();
        if ($user) {
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
