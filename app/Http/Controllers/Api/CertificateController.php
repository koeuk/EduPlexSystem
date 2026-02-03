<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
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
            ->with('course')
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
                    'certificate_url' => $certificate->certificate_url,
                    'course' => [
                        'id' => $certificate->course->id,
                        'course_name' => $certificate->course->course_name,
                        'course_code' => $certificate->course->course_code,
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

        $certificate->load(['course', 'student.user']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $certificate->id,
                'certificate_code' => $certificate->certificate_code,
                'issue_date' => $certificate->issue_date,
                'verification_url' => $certificate->verification_url,
                'certificate_url' => $certificate->certificate_url,
                'student' => [
                    'full_name' => $certificate->student->user->full_name,
                    'student_id_number' => $certificate->student->student_id_number,
                ],
                'course' => [
                    'id' => $certificate->course->id,
                    'course_name' => $certificate->course->course_name,
                    'course_code' => $certificate->course->course_code,
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

        $media = $certificate->getFirstMedia('certificate');

        if (!$media) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate file not found',
            ], 404);
        }

        activity()
            ->causedBy($user)
            ->performedOn($certificate)
            ->withProperties(['certificate_code' => $certificate->certificate_code])
            ->log('Certificate downloaded');

        return $media->toResponse($request);
    }

    public function verify(string $code): JsonResponse
    {
        $certificate = Certificate::where('certificate_code', $code)
            ->with(['student.user', 'course'])
            ->first();

        if (!$certificate) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Certificate verified',
            'data' => [
                'certificate_code' => $certificate->certificate_code,
                'issue_date' => $certificate->issue_date,
                'student_name' => $certificate->student->user->full_name,
                'course_name' => $certificate->course->course_name,
                'course_code' => $certificate->course->course_code,
                'instructor_name' => $certificate->course->instructor_name,
                'certificate_url' => $certificate->certificate_url,
            ],
        ]);
    }
}
