<!DOCTYPE html>
<html lang="en">
@php
    $terminology = app(\App\Services\TerminologyService::class);
    $certificateFallback = $terminology->get('certificate_singular');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $terminology->get('certificate_verification_page_title') }} - {{ $verification['title'] ?? $certificateFallback }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-2xl mx-auto px-4 py-16">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-full mb-4">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $terminology->get('certificate_verification_title') }}</h1>
            <p class="text-gray-600 mt-1">{{ $terminology->get('certificate_verification_subtitle') }}</p>
        </div>

        <!-- Verification Result -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            @if($verification['valid'])
                <!-- Valid Certificate -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-xl font-semibold text-white">{{ $terminology->get('certificate_verified_title') }}</h2>
                            <p class="text-green-100">{{ $terminology->get('certificate_verified_subtitle') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Recipient -->
                    <div class="text-center pb-6 border-b border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">{{ $terminology->get('certificate_awarded_to_label') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $verification['recipient_name'] }}</p>
                    </div>

                    <!-- Certificate Details -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-500">{{ $terminology->get('certificate_label') }}</p>
                                <p class="font-semibold text-gray-900">{{ $verification['title'] }}</p>
                            </div>
                        </div>

                        @if($verification['organization'])
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm text-gray-500">{{ $terminology->get('certificate_issued_by_label') }}</p>
                                <p class="font-semibold text-gray-900">{{ $verification['organization'] }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">{{ $terminology->get('certificate_issue_date_label') }}</p>
                                <p class="font-semibold text-gray-900">{{ $verification['issued_at'] }}</p>
                            </div>
                            @if($verification['course_hours'])
                            <div>
                                <p class="text-sm text-gray-500">{{ $terminology->get('certificate_course_hours_label') }}</p>
                                <p class="font-semibold text-gray-900">{{ number_format($verification['course_hours'], 1) }} {{ $terminology->get('hours_label') }}</p>
                            </div>
                            @endif
                        </div>

                        @if($verification['cohort_name'])
                        <div>
                            <p class="text-sm text-gray-500">{{ $terminology->get('certificate_cohort_label') }}</p>
                            <p class="font-semibold text-gray-900">{{ $verification['cohort_name'] }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Certificate ID -->
                    <div class="pt-6 border-t border-gray-100">
                        <p class="text-sm text-gray-500 mb-1">{{ $terminology->get('certificate_id_label') }}</p>
                        <p class="font-mono text-sm text-gray-600 bg-gray-50 px-3 py-2 rounded">{{ $verification['certificate_id'] }}</p>
                    </div>
                </div>

            @elseif($verification['revoked'])
                <!-- Revoked Certificate -->
                <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-xl font-semibold text-white">{{ $terminology->get('certificate_revoked_title') }}</h2>
                            <p class="text-red-100">{{ $terminology->get('certificate_revoked_subtitle') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="bg-red-50 rounded-lg p-4">
                        <p class="text-sm text-red-800">
                            <strong>{{ $terminology->get('certificate_revoked_on_label') }}:</strong> {{ $verification['revoked_at'] }}
                        </p>
                        @if($verification['revocation_reason'])
                        <p class="text-sm text-red-700 mt-2">
                            <strong>{{ $terminology->get('certificate_revoked_reason_label') }}:</strong> {{ $verification['revocation_reason'] }}
                        </p>
                        @endif
                    </div>
                </div>

            @else
                <!-- Invalid/Not Found -->
                <div class="bg-gradient-to-r from-gray-500 to-gray-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-xl font-semibold text-white">{{ $terminology->get('certificate_not_found_title') }}</h2>
                            <p class="text-gray-200">{{ $terminology->get('certificate_not_found_subtitle') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <p class="text-gray-600">{{ $terminology->get('certificate_not_found_body') }}</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500">
            <p>{{ $terminology->get('verification_performed_on_label') }} {{ now()->format('F j, Y \\a\\t g:i A') }}</p>
        </div>
    </div>
</body>
</html>
