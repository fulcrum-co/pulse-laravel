<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * This migration documents the addition of 'pulse_admin' as a valid org_type.
 *
 * Organization hierarchy:
 * - pulse_admin: Top-level Pulse company account
 * - consultant: Can manage multiple sections
 * - section: Can manage multiple organizations
 * - organization: End user organizations
 * - department: Sub-unit within a organization
 */
return new class extends Migration
{
    public function up(): void
    {
        // org_type is a string field, so no schema change needed
        // This migration serves as documentation of the hierarchy change
        // and can be used to update any existing data if needed
    }

    public function down(): void
    {
        // Nothing to reverse
    }
};
