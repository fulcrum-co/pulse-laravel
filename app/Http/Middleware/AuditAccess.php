<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\Learner;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action = 'view'): Response
    {
        $response = $next($request);

        // Only log successful responses
        if ($response->isSuccessful()) {
            $this->logAccess($request, $action);
        }

        return $response;
    }

    /**
     * Log the access to the audit log.
     */
    private function logAccess(Request $request, string $action): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        // Extract model from route parameters
        $route = $request->route();
        if (! $route) {
            return;
        }

        $parameters = $route->parameters();

        foreach ($parameters as $key => $model) {
            // Check if it's an Eloquent model
            if (is_object($model) && method_exists($model, 'getKey')) {
                // Determine if this involves a learner (for FERPA tracking)
                $contact = null;
                if ($model instanceof Learner) {
                    $contact = $model;
                } elseif (property_exists($model, 'learner_id') || method_exists($model, 'learner')) {
                    $contact = $model->learner ?? null;
                }

                AuditLog::log($action, $model, null, null, $contact);
            }
        }
    }
}
