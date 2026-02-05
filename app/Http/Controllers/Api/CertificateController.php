<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $certificates = Certificate::where('student_id', $student->id)
            ->with(['course.category'])
            ->latest('issue_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $certificates->map(function ($certificate) {
                return [
                    'id' => $certificate->id,
                    'certificate_code' => $certificate->certificate_code,
                    'issue_date' => $certificate->issue_date,
                    'verification_url' => $certificate->verification_url,
                    'download_url' => url("/api/certificates/{$certificate->id}/download"),
                    'course' => [
                        'id' => $certificate->course->id,
                        'course_name' => $certificate->course->course_name,
                        'course_code' => $certificate->course->course_code,
                        'category' => $certificate->course->category?->category_name,
                        'thumbnail' => $certificate->course->thumbnail_url,
                    ],
                ];
            }),
        ]);
    }

    public function show(Request $request, Certificate $certificate): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student || $certificate->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found',
            ], 404);
        }

        $certificate->load(['course.category', 'student.user']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $certificate->id,
                'certificate_code' => $certificate->certificate_code,
                'issue_date' => $certificate->issue_date,
                'verification_url' => $certificate->verification_url,
                'download_url' => url("/api/certificates/{$certificate->id}/download"),
                'student' => [
                    'full_name' => $certificate->student->user->full_name,
                    'student_id_number' => $certificate->student->student_id_number,
                ],
                'course' => [
                    'id' => $certificate->course->id,
                    'course_name' => $certificate->course->course_name,
                    'course_code' => $certificate->course->course_code,
                    'category' => $certificate->course->category?->category_name,
                    'level' => $certificate->course->level,
                    'instructor_name' => $certificate->course->instructor_name,
                    'duration_hours' => $certificate->course->duration_hours,
                    'thumbnail' => $certificate->course->thumbnail_url,
                ],
            ],
        ]);
    }

    public function download(Request $request, Certificate $certificate)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student || $certificate->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found',
            ], 404);
        }

        // Load relationships
        $certificate->load(['course.category', 'student.user']);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
        ]);

        // Set paper size to A4 landscape
        $pdf->setPaper('a4', 'landscape');

        // Log activity
        activity()
            ->causedBy($user)
            ->performedOn($certificate)
            ->withProperties(['certificate_code' => $certificate->certificate_code])
            ->log('Certificate downloaded');

        // Generate filename
        $filename = 'certificate-' . $certificate->certificate_code . '.pdf';

        return $pdf->download($filename);
    }

    public function verify(string $code): JsonResponse
    {
        $certificate = Certificate::where('certificate_code', $code)
            ->with(['student.user', 'course.category'])
            ->first();

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found or invalid',
                'is_valid' => false,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Certificate verified successfully',
            'is_valid' => true,
            'data' => [
                'certificate_code' => $certificate->certificate_code,
                'issue_date' => $certificate->issue_date,
                'student_name' => $certificate->student->user->full_name,
                'course_name' => $certificate->course->course_name,
                'course_code' => $certificate->course->course_code,
                'category' => $certificate->course->category?->category_name,
                'level' => $certificate->course->level,
                'duration_hours' => $certificate->course->duration_hours,
                'instructor_name' => $certificate->course->instructor_name,
            ],
        ]);
    }
}
