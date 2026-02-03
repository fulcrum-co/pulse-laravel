<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService
    ) {}

    /**
     * Display public verification page.
     */
    public function verify(string $uuid)
    {
        $data = $this->certificateService->getVerificationData($uuid);

        if (!$data) {
            abort(404, 'Certificate not found');
        }

        return view('certificates.verify', [
            'verification' => $data,
            'uuid' => $uuid,
        ]);
    }

    /**
     * Download certificate PDF.
     */
    public function download(string $uuid)
    {
        $certificate = Certificate::where('uuid', $uuid)->firstOrFail();

        // Check if user has access (owner or admin)
        $user = auth()->user();
        if ($user && ($user->id === $certificate->user_id || $user->isAdmin())) {
            $url = $this->certificateService->getDownloadUrl($certificate);

            if (!$url) {
                // Generate PDF if it doesn't exist
                $this->certificateService->generatePdf($certificate);
                $url = $this->certificateService->getDownloadUrl($certificate);
            }

            return redirect($url);
        }

        abort(403, app(\App\Services\TerminologyService::class)->get('unauthorized_label'));
    }

    /**
     * Get LinkedIn share URL.
     */
    public function linkedinShare(string $uuid)
    {
        $certificate = Certificate::where('uuid', $uuid)->firstOrFail();

        $user = auth()->user();
        if (!$user || $user->id !== $certificate->user_id) {
            abort(403, app(\App\Services\TerminologyService::class)->get('unauthorized_label'));
        }

        $url = $this->certificateService->getLinkedInShareUrl($certificate);

        return redirect($url);
    }

    /**
     * Show user's certificates.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $certificates = Certificate::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->orderBy('issued_at', 'desc')
            ->paginate(12);

        return view('certificates.index', [
            'certificates' => $certificates,
        ]);
    }

    /**
     * Show single certificate detail.
     */
    public function show(string $uuid)
    {
        $certificate = Certificate::where('uuid', $uuid)
            ->with(['cohortMember.cohort', 'user'])
            ->firstOrFail();

        $user = auth()->user();
        if (!$user || ($user->id !== $certificate->user_id && !$user->isAdmin())) {
            abort(403, app(\App\Services\TerminologyService::class)->get('unauthorized_label'));
        }

        return view('certificates.show', [
            'certificate' => $certificate,
            'linkedinUrl' => $this->certificateService->getLinkedInShareUrl($certificate),
            'downloadUrl' => $this->certificateService->getDownloadUrl($certificate),
            'verifyUrl' => route('certificates.verify', $certificate->uuid),
        ]);
    }
}
