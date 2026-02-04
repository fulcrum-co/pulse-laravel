<?php

declare(strict_types=1);

use App\Services\TerminologyService;

if (! function_exists('term')) {
    /**
     * Get a terminology label for the current organization.
     *
     * Usage in Blade: {{ term('student') }}
     * Returns custom term if set, otherwise default.
     */
    function term(string $key): string
    {
        return app(TerminologyService::class)->get($key);
    }
}
