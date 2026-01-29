<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'org_id',
        'student_number',
        'grade_level',
        'date_of_birth',
        'gender',
        'ethnicity',
        'iep_status',
        'ell_status',
        'free_reduced_lunch',
        'enrollment_status',
        'enrollment_date',
        'risk_level',
        'risk_score',
        'tags',
        'custom_fields',
        'counselor_user_id',
        'homeroom_classroom_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'iep_status' => 'boolean',
        'ell_status' => 'boolean',
        'free_reduced_lunch' => 'boolean',
        'tags' => 'array',
        'custom_fields' => 'array',
        'risk_score' => 'decimal:2',
    ];

    /**
     * Get the associated user account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the student's organization (school).
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the student's counselor.
     */
    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_user_id');
    }

    /**
     * Get the student's homeroom classroom.
     */
    public function homeroomClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'homeroom_classroom_id');
    }

    /**
     * Get the classrooms the student is enrolled in.
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class)->withTimestamps();
    }

    /**
     * Get survey attempts for this student.
     */
    public function surveyAttempts(): HasMany
    {
        return $this->hasMany(SurveyAttempt::class);
    }

    /**
     * Get resource assignments for this student.
     */
    public function resourceAssignments(): HasMany
    {
        return $this->hasMany(ResourceAssignment::class);
    }

    /**
     * Get conversations for this student.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get the student's full name from user.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user?->full_name ?? 'Unknown Student';
    }

    /**
     * Get the latest completed survey attempt.
     */
    public function getLatestSurveyAttemptAttribute()
    {
        return $this->surveyAttempts()
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->first();
    }

    /**
     * Scope to filter by risk level.
     */
    public function scopeRiskLevel($query, string $level)
    {
        return $query->where('risk_level', $level);
    }

    /**
     * Scope to filter active students.
     */
    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    /**
     * Scope to filter by grade level.
     */
    public function scopeGradeLevel($query, string $grade)
    {
        return $query->where('grade_level', $grade);
    }

    /**
     * Scope to filter students with IEP.
     */
    public function scopeWithIep($query)
    {
        return $query->where('iep_status', true);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }
}
