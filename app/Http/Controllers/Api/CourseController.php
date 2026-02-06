<?php

namespace App\Http\Controllers\Api;

use App\Filters\PriceRangeFilter;
use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courses = QueryBuilder::for(Course::class)
            ->allowedFilters([
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('level'),
                AllowedFilter::exact('is_featured'),
                AllowedFilter::exact('pricing_type'),
                AllowedFilter::custom('price_range', new PriceRangeFilter()),
                AllowedFilter::custom('search', new UniversalSearchFilter([
                    'course_name',
                    'course_code',
                    'description',
                    'instructor_name',
                ])),
            ])
            ->allowedSorts(['course_name', 'price', 'created_at'])
            ->where('status', 'published')
            ->with('category')
            ->withCount('enrollments')
            ->withAvg('reviews', 'rating')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'course_name' => $course->course_name,
                    'course_code' => $course->course_code,
                    'description' => $course->description,
                    'image_url' => $course->thumbnail_url,
                    'level' => $course->level,
                    'duration_hours' => $course->duration_hours,
                    'pricing_type' => $course->pricing_type,
                    'is_free' => $course->pricing_type === 'free',
                    'price' => $course->price,
                    'instructor_name' => $course->instructor_name,
                    'is_featured' => $course->is_featured,
                    'status' => $course->status,
                    'category' => $course->category ? [
                        'id' => $course->category->id,
                        'category_name' => $course->category->category_name,
                        'image_url' => $course->category->full_image_url,
                    ] : null,
                    'enrollments_count' => $course->enrollments_count,
                    'average_rating' => round($course->reviews_avg_rating ?? 0, 1),
                    'created_at' => $course->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'total_pages' => $courses->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, Course $course): JsonResponse
    {
        if ($course->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Course not found',
            ], 404);
        }

        $user = $request->user();
        $student = $user->student;

        $course->load(['category', 'modules.lessons']);
        $course->loadCount(['enrollments', 'reviews']);
        $course->loadAvg('reviews', 'rating');

        $isEnrolled = false;
        $enrollment = null;

        if ($student) {
            $enrollment = $course->enrollments()
                ->where('student_id', $student->id)
                ->first();
            $isEnrolled = $enrollment !== null;
        }

        activity()
            ->causedBy($user)
            ->performedOn($course)
            ->withProperties(['course_id' => $course->id])
            ->log('Viewed course');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $course->id,
                'course_name' => $course->course_name,
                'course_code' => $course->course_code,
                'description' => $course->description,
                'image_url' => $course->thumbnail_url,
                'level' => $course->level,
                'duration_hours' => $course->duration_hours,
                'pricing_type' => $course->pricing_type,
                'is_free' => $course->pricing_type === 'free',
                'price' => $course->price,
                'instructor_name' => $course->instructor_name,
                'enrollment_limit' => $course->enrollment_limit,
                'is_featured' => $course->is_featured,
                'status' => $course->status,
                'category' => $course->category ? [
                    'id' => $course->category->id,
                    'category_name' => $course->category->category_name,
                    'description' => $course->category->description,
                    'image_url' => $course->category->full_image_url,
                ] : null,
                'modules' => $course->modules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'module_title' => $module->module_title,
                        'module_order' => $module->module_order,
                        'description' => $module->description,
                        'lessons_count' => $module->lessons->count(),
                    ];
                }),
                'total_lessons' => $course->modules->sum(fn($m) => $m->lessons->count()),
                'enrollments_count' => $course->enrollments_count,
                'reviews_count' => $course->reviews_count,
                'average_rating' => round($course->reviews_avg_rating ?? 0, 1),
                'is_enrolled' => $isEnrolled,
                'enrollment' => $enrollment ? [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status,
                    'progress_percentage' => $enrollment->progress_percentage,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'completion_date' => $enrollment->completion_date,
                    'last_accessed' => $enrollment->last_accessed,
                    'payment_status' => $enrollment->payment_status,
                    'certificate_issued' => $enrollment->certificate_issued,
                ] : null,
                'created_at' => $course->created_at,
                'updated_at' => $course->updated_at,
            ],
        ]);
    }

    public function modules(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $enrollment = $course->enrollments()
            ->where('student_id', $student->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course',
            ], 403);
        }

        $course->load(['modules.lessons.lessonProgress' => function ($query) use ($student) {
            $query->where('student_id', $student->id);
        }]);

        return response()->json([
            'success' => true,
            'data' => [
                'course' => [
                    'id' => $course->id,
                    'course_name' => $course->course_name,
                    'image_url' => $course->thumbnail_url,
                ],
                'enrollment' => [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status,
                    'progress_percentage' => $enrollment->progress_percentage,
                ],
                'modules' => $course->modules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'module_title' => $module->module_title,
                        'module_order' => $module->module_order,
                        'description' => $module->description,
                        'lessons' => $module->lessons->map(function ($lesson) {
                            $progress = $lesson->lessonProgress->first();
                            return [
                                'id' => $lesson->id,
                                'lesson_title' => $lesson->lesson_title,
                                'lesson_type' => $lesson->lesson_type,
                                'lesson_order' => $lesson->lesson_order,
                                'description' => $lesson->description,
                                'duration_minutes' => $lesson->duration_minutes,
                                'video_duration' => $lesson->video_duration,
                                'formatted_duration' => $lesson->formatted_duration,
                                'is_mandatory' => $lesson->is_mandatory,
                                'image_url' => $lesson->image_url ? asset('storage/' . $lesson->image_url) : null,
                                'video_url' => $lesson->video_url ? asset('storage/' . $lesson->video_url) : null,
                                'video_thumbnail_url' => $lesson->video_thumbnail_url,
                                'progress' => $progress ? [
                                    'status' => $progress->status,
                                    'progress_percentage' => $progress->progress_percentage,
                                    'completed_at' => $progress->completed_at,
                                ] : null,
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }

    public function reviews(Request $request, Course $course): JsonResponse
    {
        $reviews = QueryBuilder::for(CourseReview::class)
            ->allowedFilters([
                AllowedFilter::exact('rating'),
            ])
            ->allowedSorts(['created_at', 'rating'])
            ->where('course_id', $course->id)
            ->with('student.user')
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'review_text' => $review->review_text,
                    'would_recommend' => $review->would_recommend,
                    'student' => [
                        'id' => $review->student->id,
                        'full_name' => $review->student->user->full_name,
                        'profile_picture' => $review->student->user->profile_picture_url,
                    ],
                    'created_at' => $review->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'total_pages' => $reviews->lastPage(),
            ],
        ]);
    }

    public function storeReview(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
            'would_recommend' => 'required|boolean',
        ]);

        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $enrollment = $course->enrollments()
            ->where('student_id', $student->id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You must be enrolled to review this course',
            ], 403);
        }

        $existingReview = CourseReview::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this course',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $review = CourseReview::create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'rating' => $validated['rating'],
                'review_text' => $validated['review_text'],
                'would_recommend' => $validated['would_recommend'],
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($review)
                ->withProperties([
                    'course_id' => $course->id,
                    'rating' => $validated['rating'],
                ])
                ->log('Course reviewed');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully',
                'data' => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'review_text' => $review->review_text,
                    'would_recommend' => $review->would_recommend,
                    'created_at' => $review->created_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }
}
