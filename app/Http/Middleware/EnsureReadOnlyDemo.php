<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureReadOnlyDemo
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isProspect()) {
            return $next($request);
        }

        if ($request->isMethod('get') || $request->isMethod('head') || $request->isMethod('options')) {
            return $next($request);
        }

        // Allow logout and demo access endpoints
        $path = $request->path();
        $allowedPrefixes = [
            'logout',
            'demo/access',
            'demo/zoho-webhook',
            'demo/feedback',  // Allow feedback survey submissions
        ];

        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return $next($request);
            }
        }

        return response()->view('demo.read-only', [], 403);
    }
}
