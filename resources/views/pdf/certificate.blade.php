<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion - {{ $certificate->certificate_code }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            background: #fff;
            width: 297mm;
            height: 210mm;
        }

        .certificate {
            width: 100%;
            height: 100%;
            position: relative;
            padding: 15mm;
        }

        /* Top and bottom borders */
        .border-top {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(to right, #3b82f6, #60a5fa, #22c55e);
        }

        .border-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(to right, #3b82f6, #60a5fa, #22c55e);
        }

        /* Corner decorations */
        .corner {
            position: absolute;
            width: 40px;
            height: 40px;
            border-width: 3px;
            border-style: solid;
        }

        .corner-tl {
            top: 12mm;
            left: 12mm;
            border-color: #3b82f6 transparent transparent #3b82f6;
            border-radius: 8px 0 0 0;
        }

        .corner-tr {
            top: 12mm;
            right: 12mm;
            border-color: #22c55e #22c55e transparent transparent;
            border-radius: 0 8px 0 0;
        }

        .corner-bl {
            bottom: 12mm;
            left: 12mm;
            border-color: transparent transparent #22c55e #22c55e;
            border-radius: 0 0 0 8px;
        }

        .corner-br {
            bottom: 12mm;
            right: 12mm;
            border-color: transparent #3b82f6 #3b82f6 transparent;
            border-radius: 0 0 8px 0;
        }

        .content {
            text-align: center;
            padding: 10mm 20mm;
        }

        /* Award icon circle */
        .award-icon {
            width: 70px;
            height: 70px;
            background: #22c55e;
            border-radius: 50%;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .award-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        .platform-name {
            color: #3b82f6;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .title {
            font-size: 36px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 15px;
            font-family: Georgia, 'Times New Roman', serif;
        }

        /* Divider with star */
        .divider {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 15px 0;
        }

        .divider-line {
            width: 80px;
            height: 1px;
            background: linear-gradient(to right, transparent, #3b82f6);
        }

        .divider-line-right {
            background: linear-gradient(to left, transparent, #22c55e);
        }

        .divider-star {
            width: 16px;
            height: 16px;
            margin: 0 10px;
            color: #3b82f6;
        }

        .certify-text {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .student-name {
            font-size: 32px;
            font-weight: bold;
            color: #3b82f6;
            font-family: Georgia, 'Times New Roman', serif;
            margin: 10px 0;
        }

        .course-box {
            background: linear-gradient(to right, #eff6ff, #f0fdf4);
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 12px 30px;
            display: inline-block;
            margin: 15px 0;
        }

        .course-name {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
        }

        .course-category {
            color: #22c55e;
            font-size: 12px;
            margin-top: 3px;
        }

        .course-details {
            display: flex;
            justify-content: center;
            gap: 30px;
            color: #6b7280;
            font-size: 11px;
            margin: 10px 0;
        }

        .course-detail-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Certificate info grid */
        .cert-info {
            display: flex;
            justify-content: center;
            gap: 60px;
            margin: 15px 0;
        }

        .cert-info-item {
            text-align: center;
        }

        .cert-info-label {
            font-size: 9px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }

        .cert-info-value {
            font-size: 12px;
            font-weight: 600;
            color: #1f2937;
        }

        .cert-info-value.mono {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 11px;
        }

        .status-badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        /* Signatures */
        .signatures {
            display: flex;
            justify-content: center;
            gap: 80px;
            margin: 20px 0 15px;
        }

        .signature {
            text-align: center;
        }

        .signature-line {
            width: 120px;
            border-bottom: 2px solid #d1d5db;
            margin-bottom: 5px;
        }

        .signature-line.blue {
            border-color: #93c5fd;
        }

        .signature-line.green {
            border-color: #86efac;
        }

        .signature-label {
            font-size: 10px;
            color: #6b7280;
        }

        /* Verification footer */
        .verification {
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 10px;
            color: #6b7280;
        }

        .verification-icon {
            width: 14px;
            height: 14px;
            color: #22c55e;
        }

        .verification-url {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9px;
            border: 1px solid #bfdbfe;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="border-top"></div>
        <div class="border-bottom"></div>

        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

        <div class="content">
            <!-- Award Icon -->
            <div class="award-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                </svg>
            </div>

            <!-- Header -->
            <div class="platform-name">EduPlex Learning Platform</div>
            <h1 class="title">Certificate of Completion</h1>

            <!-- Divider -->
            <div class="divider">
                <div class="divider-line"></div>
                <svg class="divider-star" viewBox="0 0 24 24" fill="#3b82f6">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                </svg>
                <div class="divider-line divider-line-right"></div>
            </div>

            <!-- Certify Text -->
            <p class="certify-text">This is to certify that</p>
            <p class="student-name">{{ $certificate->student->user->full_name }}</p>
            <p class="certify-text">has successfully completed the course</p>

            <!-- Course Box -->
            <div class="course-box">
                <div class="course-name">{{ $certificate->course->course_name }}</div>
                @if($certificate->course->category)
                    <div class="course-category">{{ $certificate->course->category->category_name }}</div>
                @endif
            </div>

            <!-- Course Details -->
            <div class="course-details">
                <span class="course-detail-item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                    </svg>
                    {{ $certificate->course->duration_hours ?? 0 }} hours
                </span>
                @if($certificate->course->level)
                    <span class="course-detail-item">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                        {{ ucfirst($certificate->course->level) }}
                    </span>
                @endif
            </div>

            <!-- Checkmark Divider -->
            <div class="divider" style="margin: 10px 0;">
                <div class="divider-line" style="width: 50px; background: #d1d5db;"></div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" style="margin: 0 10px;">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/>
                </svg>
                <div class="divider-line divider-line-right" style="width: 50px; background: #d1d5db;"></div>
            </div>

            <!-- Certificate Info -->
            <div class="cert-info">
                <div class="cert-info-item">
                    <div class="cert-info-label">Issue Date</div>
                    <div class="cert-info-value">{{ $certificate->issue_date->format('F d, Y') }}</div>
                </div>
                <div class="cert-info-item">
                    <div class="cert-info-label">Certificate ID</div>
                    <div class="cert-info-value mono">{{ $certificate->certificate_code }}</div>
                </div>
                <div class="cert-info-item">
                    <div class="cert-info-label">Status</div>
                    <div class="status-badge">Valid</div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="signatures">
                <div class="signature">
                    <div class="signature-line blue"></div>
                    <div class="signature-label">Platform Director</div>
                </div>
                <div class="signature">
                    <div class="signature-line green"></div>
                    <div class="signature-label">Course Instructor</div>
                </div>
            </div>

            <!-- Verification -->
            <div class="verification">
                <svg class="verification-icon" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span>Verify at:</span>
                <span class="verification-url">{{ $certificate->verification_url ?: url("/api/certificates/verify/{$certificate->certificate_code}") }}</span>
            </div>
        </div>
    </div>
</body>
</html>
