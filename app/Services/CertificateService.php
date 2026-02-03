<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Certificate;
use App\Models\CohortMember;
use App\Models\LeadScore;
use App\Models\LeadScoreEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Generate a certificate for a completed cohort member.
     */
    public function generate(CohortMember $member): Certificate
    {
        $user = $member->user;
        $cohort = $member->cohort;
        $course = $cohort->course;

        // Check if certificate already exists
        $existing = Certificate::where('cohort_member_id', $member->id)->first();
        if ($existing) {
            return $existing;
        }

        // Calculate total time spent
        $totalSeconds = $member->cohortProgress()->sum('time_spent_seconds');
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $timeDisplay = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes} minutes";

        // Create certificate
        $certificate = Certificate::create([
            'uuid' => Str::uuid(),
            'cohort_member_id' => $member->id,
            'user_id' => $user->id,
            'org_id' => $user->org_id,
            'course_id' => $course?->id,
            'title' => $course?->title ?? 'Course Completion',
            'recipient_name' => $user->name,
            'recipient_email' => $user->email,
            'issued_at' => now(),
            'course_hours' => round($totalSeconds / 3600, 1),
            'metadata' => [
                'cohort_name' => $cohort->name,
                'cohort_id' => $cohort->id,
                'time_display' => $timeDisplay,
                'steps_completed' => $member->steps_completed,
                'completion_date' => $member->completed_at?->format('F j, Y'),
            ],
        ]);

        // Generate PDF
        $this->generatePdf($certificate);

        // Award lead score points for certification
        $this->awardCertificationPoints($member, $certificate);

        return $certificate;
    }

    /**
     * Generate PDF for a certificate.
     */
    public function generatePdf(Certificate $certificate): string
    {
        $pdf = Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
        ]);

        $pdf->setPaper('letter', 'landscape');

        $filename = "certificates/{$certificate->uuid}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        $certificate->update([
            'pdf_path' => $filename,
        ]);

        return $filename;
    }

    /**
     * Regenerate PDF (e.g., after template update).
     */
    public function regeneratePdf(Certificate $certificate): string
    {
        // Delete old PDF if exists
        if ($certificate->pdf_path) {
            Storage::disk('public')->delete($certificate->pdf_path);
        }

        return $this->generatePdf($certificate);
    }

    /**
     * Get verification data for public display.
     */
    public function getVerificationData(string $uuid): ?array
    {
        $certificate = Certificate::where('uuid', $uuid)
            ->with(['user', 'cohortMember.cohort'])
            ->first();

        if (!$certificate) {
            return null;
        }

        if ($certificate->revoked_at) {
            return [
                'valid' => false,
                'revoked' => true,
                'revoked_at' => $certificate->revoked_at->format('F j, Y'),
                'revocation_reason' => $certificate->revocation_reason,
            ];
        }

        return [
            'valid' => true,
            'revoked' => false,
            'certificate_id' => $certificate->uuid,
            'title' => $certificate->title,
            'recipient_name' => $certificate->recipient_name,
            'issued_at' => $certificate->issued_at->format('F j, Y'),
            'course_hours' => $certificate->course_hours,
            'organization' => $certificate->organization?->name,
            'cohort_name' => $certificate->metadata['cohort_name'] ?? null,
            'completion_date' => $certificate->metadata['completion_date'] ?? null,
        ];
    }

    /**
     * Revoke a certificate.
     */
    public function revoke(Certificate $certificate, string $reason): void
    {
        $certificate->update([
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);
    }

    /**
     * Reinstate a revoked certificate.
     */
    public function reinstate(Certificate $certificate): void
    {
        $certificate->update([
            'revoked_at' => null,
            'revocation_reason' => null,
        ]);
    }

    /**
     * Get LinkedIn share URL for a certificate.
     */
    public function getLinkedInShareUrl(Certificate $certificate): string
    {
        $verifyUrl = route('certificates.verify', $certificate->uuid);

        $params = [
            'name' => $certificate->title,
            'organizationName' => $certificate->organization?->name ?? 'Learning Platform',
            'issueYear' => $certificate->issued_at->year,
            'issueMonth' => $certificate->issued_at->month,
            'certUrl' => $verifyUrl,
            'certId' => $certificate->uuid,
        ];

        return 'https://www.linkedin.com/profile/add?' . http_build_query($params);
    }

    /**
     * Award lead score points for earning a certificate.
     */
    protected function awardCertificationPoints(CohortMember $member, Certificate $certificate): void
    {
        $user = $member->user;
        if (!$user?->org_id) {
            return;
        }

        $leadScore = LeadScore::firstOrCreate(
            [
                'org_id' => $user->org_id,
                'user_id' => $user->id,
            ],
            ['total_score' => 0]
        );

        $points = LeadScore::POINTS_CERTIFICATION_EARNED;

        $leadScore->addPoints(
            $points,
            LeadScoreEvent::TYPE_CERTIFICATION_EARNED,
            "Earned certificate: {$certificate->title}",
            $certificate
        );

        // Update certifications count
        $leadScore->increment('certifications_earned');
    }

    /**
     * Get download URL for certificate PDF.
     */
    public function getDownloadUrl(Certificate $certificate): ?string
    {
        if (!$certificate->pdf_path) {
            return null;
        }

        return Storage::disk('public')->url($certificate->pdf_path);
    }
}
