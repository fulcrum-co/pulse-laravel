<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $org = $request->user()->organization;

        if (!$org) {
            abort(403, 'No organization associated with this account.');
        }

        // Check subscription status
        $status = $org->subscription_status ?? 'trial';

        if ($status === 'suspended') {
            return redirect()->route('subscription.suspended')
                ->with('error', 'Your organization\'s subscription has been suspended. Please contact support.');
        }

        // You could add more checks here:
        // - Trial expiration
        // - Feature-specific access
        // - Usage limits

        return $next($request);
    }
}
