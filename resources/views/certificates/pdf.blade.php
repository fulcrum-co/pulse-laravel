<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate - {{ $certificate->title }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .certificate {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            box-sizing: border-box;
        }

        .certificate-inner {
            background: white;
            border-radius: 20px;
            padding: 60px 80px;
            text-align: center;
            max-width: 900px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            overflow: hidden;
        }

        .certificate-inner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .badge {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        .certificate-title {
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .certificate-heading {
            font-size: 36px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 30px;
        }

        .presented-to {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .recipient-name {
            font-size: 42px;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 30px;
            font-style: italic;
        }

        .completion-text {
            font-size: 16px;
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .course-title {
            font-weight: 600;
            color: #1f2937;
        }

        .details {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin-bottom: 40px;
            padding: 20px 0;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-item {
            text-align: center;
        }

        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .signature {
            text-align: left;
        }

        .signature-line {
            width: 200px;
            border-bottom: 2px solid #d1d5db;
            margin-bottom: 8px;
        }

        .signature-name {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }

        .signature-title {
            font-size: 12px;
            color: #6b7280;
        }

        .verify {
            text-align: right;
        }

        .verify-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 5px;
        }

        .verify-id {
            font-size: 12px;
            font-family: monospace;
            color: #6b7280;
        }

        .verify-url {
            font-size: 11px;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="certificate-inner">
            <div class="badge">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                </svg>
            </div>

            <div class="certificate-title">Certificate of Completion</div>
            <h1 class="certificate-heading">Achievement Unlocked</h1>

            <div class="presented-to">This certificate is proudly presented to</div>
            <div class="recipient-name">{{ $certificate->recipient_name }}</div>

            <div class="completion-text">
                For successfully completing the course
                <span class="course-title">"{{ $certificate->title }}"</span>
                @if($certificate->metadata['cohort_name'] ?? null)
                    as part of the {{ $certificate->metadata['cohort_name'] }} cohort
                @endif
            </div>

            <div class="details">
                <div class="detail-item">
                    <div class="detail-label">Date Completed</div>
                    <div class="detail-value">{{ $certificate->issued_at->format('F j, Y') }}</div>
                </div>
                @if($certificate->course_hours)
                <div class="detail-item">
                    <div class="detail-label">Hours Completed</div>
                    <div class="detail-value">{{ number_format($certificate->course_hours, 1) }} hours</div>
                </div>
                @endif
                <div class="detail-item">
                    <div class="detail-label">Credential ID</div>
                    <div class="detail-value">{{ strtoupper(substr($certificate->uuid, 0, 8)) }}</div>
                </div>
            </div>

            <div class="footer">
                <div class="signature">
                    <div class="signature-line"></div>
                    <div class="signature-name">Program Director</div>
                    <div class="signature-title">{{ $certificate->organization?->name ?? 'Learning Platform' }}</div>
                </div>

                <div class="verify">
                    <div class="verify-label">Verify this certificate</div>
                    <div class="verify-url">{{ route('certificates.verify', $certificate->uuid) }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
