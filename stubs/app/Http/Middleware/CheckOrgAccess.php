<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrgAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $paramName = 'org_id'): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        // Get org ID from route parameter or request
        $orgId = $request->route($paramName) ?? $request->input($paramName);

        if (! $orgId) {
            return $next($request);
        }

        // Check if user can access this organization
        if (! $request->user()->canAccessOrg($orgId)) {
            abort(403, 'You do not have access to this organization.');
        }

        return $next($request);
    }
}
