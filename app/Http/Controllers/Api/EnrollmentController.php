<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EnrollmentController extends Controller
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

        $enrollments = QueryBuilder::for(Enrollment::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('payment_status'),
            ])
            ->allowedSorts(['enrollment_date', 'progress_percentage', 'last_accessed'])
            ->where('student_id', $student->id)
            ->with(['course.category'])
            ->latest('enrollment_date')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $enrollments->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'completion_date' => $enrollment->completion_date,
                    'progress_percentage' => $enrollment->progress_percentage,
                    'status' => $enrollment->status,
                    'payment_status' => $enrollment->payment_status,
                    'certificate_issued' => $enrollment->certificate_issued,
                    'last_accessed' => $enrollment->last_accessed,
                    'course' => [
                        'id' => $enrollment->course->id,
                        'course_name' => $enrollment->course->course_name,
                        'course_code' => $enrollment->course->course_code,
                        'level' => $enrollment->course->level,
                        'duration_hours' => $enrollment->course->duration_hours,
                        'thumbnail' => $enrollment->course->thumbnail_url,
                        'category' => $enrollment->course->category ? [
                            'id' => $enrollment->course->category->id,
                            'category_name' => $enrollment->course->category->category_name,
                        ] : null,
                    ],
                ];
            }),
            'pagination' => [
                'current_page' => $enrollments->currentPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
                'total_pages' => $enrollments->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $course = Course::find($validated['course_id']);

        if ($course->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Course is not available for enrollment',
            ], 422);
        }

        $existingEnrollment = Enrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if ($existingEnrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course',
            ], 422);
        }

        if ($course->enrollment_limit) {
            $currentEnrollments = $course->enrollments()
                ->whereIn('status', ['active', 'completed'])
                ->count();

            if ($currentEnrollments >= $course->enrollment_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course enrollment limit has been reached',
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'enrollment_date' => now(),
                'status' => 'active',
                'progress_percentage' => 0,
                'payment_status' => $course->price > 0 ? 'pending' : 'paid',
                'certificate_issued' => false,
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($enrollment)
                ->withProperties([
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                ])
                ->log('Enrolled in course');

            // Send notification to all admins
            $admins = User::where('user_type', 'admin')
                ->where('status', 'active')
                ->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => __('New Course Enrollment'),
                    'message' => __(':student has enrolled in :course', [
                        'student' => $user->full_name,
                        'course' => $course->course_name,
                    ]),
                    'type' => 'enrollment',
                    'related_id' => $enrollment->id,
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully enrolled in course',
                'data' => [
                    'id' => $enrollment->id,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'status' => $enrollment->status,
                    'payment_status' => $enrollment->payment_status,
                    'course' => [
                        'id' => $course->id,
                        'course_name' => $course->course_name,
                        'price' => $course->price,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Enrollment failed',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function show(Request $request, Enrollment $enrollment): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student || $enrollment->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment not found',
            ], 404);
        }

        $enrollment->load([
            'course.category',
            'course.modules.lessons.lessonProgress' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            },
        ]);

        $enrollment->update(['last_accessed' => now()]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $enrollment->id,
                'enrollment_date' => $enrollment->enrollment_date,
                'completion_date' => $enrollment->completion_date,
                'progress_percentage' => $enrollment->progress_percentage,
                'status' => $enrollment->status,
                'payment_status' => $enrollment->payment_status,
                'certificate_issued' => $enrollment->certificate_issued,
                'last_accessed' => $enrollment->last_accessed,
                'course' => [
                    'id' => $enrollment->course->id,
                    'course_name' => $enrollment->course->course_name,
                    'course_code' => $enrollment->course->course_code,
                    'description' => $enrollment->course->description,
                    'level' => $enrollment->course->level,
                    'duration_hours' => $enrollment->course->duration_hours,
                    'instructor_name' => $enrollment->course->instructor_name,
                    'thumbnail' => $enrollment->course->thumbnail_url,
                    'category' => $enrollment->course->category ? [
                        'id' => $enrollment->course->category->id,
                        'category_name' => $enrollment->course->category->category_name,
                    ] : null,
                    'modules' => $enrollment->course->modules->map(function ($module) {
                        return [
                            'id' => $module->id,
                            'module_title' => $module->module_title,
                            'module_order' => $module->module_order,
                            'lessons' => $module->lessons->map(function ($lesson) {
                                $progress = $lesson->lessonProgress->first();
                                return [
                                    'id' => $lesson->id,
                                    'lesson_title' => $lesson->lesson_title,
                                    'lesson_type' => $lesson->lesson_type,
                                    'lesson_order' => $lesson->lesson_order,
                                    'duration_minutes' => $lesson->duration_minutes,
                                    'is_mandatory' => $lesson->is_mandatory,
                                    'progress' => $progress ? [
                                        'status' => $progress->status,
                                        'progress_percentage' => $progress->progress_percentage,
                                    ] : null,
                                ];
                            }),
                        ];
                    }),
                ],
            ],
        ]);
    }

    public function destroy(Request $request, Enrollment $enrollment): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student || $enrollment->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment not found',
            ], 404);
        }

        if ($enrollment->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot drop a completed enrollment',
            ], 422);
        }

        $enrollment->update(['status' => 'dropped']);

        activity()
            ->causedBy($user)
            ->performedOn($enrollment)
            ->withProperties([
                'course_id' => $enrollment->course_id,
                'student_id' => $student->id,
            ])
            ->log('Enrollment dropped');

        return response()->json([
            'success' => true,
            'message' => 'Enrollment dropped successfully',
        ]);
    }

    /**
     * Enroll by scanning QR code (enrollment_code)
     */
    public function enrollByCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        // Find course by enrollment code
        $course = Course::where('enrollment_code', strtoupper($validated['code']))->first();

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid enrollment code',
            ], 404);
        }

        if ($course->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Course is not available for enrollment',
            ], 422);
        }

        // Check if already enrolled
        $existingEnrollment = Enrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if ($existingEnrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course',
                'data' => [
                    'enrollment_id' => $existingEnrollment->id,
                    'course' => [
                        'id' => $course->id,
                        'course_name' => $course->course_name,
                    ],
                ],
            ], 422);
        }

        // Check enrollment limit
        if ($course->enrollment_limit) {
            $currentEnrollments = $course->enrollments()
                ->whereIn('status', ['active', 'completed'])
                ->count();

            if ($currentEnrollments >= $course->enrollment_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course enrollment limit has been reached',
                ], 422);
            }
        }

        DB::beginTransaction();

        try {
            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'enrollment_date' => now(),
                'status' => 'active',
                'progress_percentage' => 0,
                'payment_status' => $course->price > 0 ? 'pending' : 'paid',
                'certificate_issued' => false,
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($enrollment)
                ->withProperties([
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                    'enrollment_method' => 'qr_code',
                ])
                ->log('Enrolled in course via QR code');

            // Send notification to all admins
            $admins = User::where('user_type', 'admin')
                ->where('status', 'active')
                ->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => __('New Course Enrollment (QR)'),
                    'message' => __(':student has enrolled in :course via QR code', [
                        'student' => $user->full_name,
                        'course' => $course->course_name,
                    ]),
                    'type' => 'enrollment',
                    'related_id' => $enrollment->id,
                    'is_read' => false,
                ]);
            }

            DB::commit();

            $paymentRequired = $course->price > 0 && $enrollment->payment_status !== 'paid';

            return response()->json([
                'success' => true,
                'message' => $paymentRequired
                    ? 'Enrolled successfully. Please complete payment to access the course.'
                    : 'Successfully enrolled in course',
                'payment_required' => $paymentRequired,
                'data' => [
                    'id' => $enrollment->id,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'status' => $enrollment->status,
                    'payment_status' => $enrollment->payment_status,
                    'can_access_course' => !$paymentRequired,
                    'course' => [
                        'id' => $course->id,
                        'course_name' => $course->course_name,
                        'course_code' => $course->course_code,
                        'description' => $course->description,
                        'level' => $course->level,
                        'duration_hours' => $course->duration_hours,
                        'price' => $course->price,
                        'is_free' => $course->price == 0,
                        'thumbnail' => $course->thumbnail_url,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Enrollment failed',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }
}
