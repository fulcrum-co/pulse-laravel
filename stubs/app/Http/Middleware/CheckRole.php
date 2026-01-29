<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user has any of the specified roles
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // Check if user has permission via role hierarchy
        foreach ($roles as $role) {
            $roleConfig = config("pulse.roles.{$role}");
            $userRoleConfig = config("pulse.roles.{$request->user()->primary_role}");

            // Higher level roles can access lower level routes
            if ($userRoleConfig && $roleConfig) {
                if (($userRoleConfig['level'] ?? 0) >= ($roleConfig['level'] ?? 0)) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Unauthorized. Required role: ' . implode(' or ', $roles));
    }
}
