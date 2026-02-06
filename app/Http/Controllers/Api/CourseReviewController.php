<?php

namespace App\Http\Controllers\Api;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CourseReviewController extends Controller
{
    /**
     * Get all reviews for authenticated student
     */
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

        $reviews = QueryBuilder::for(CourseReview::class)
            ->allowedFilters([
                AllowedFilter::exact('course_id'),
                AllowedFilter::exact('rating'),
            ])
            ->allowedSorts(['created_at', 'rating'])
            ->where('student_id', $student->id)
            ->with('course')
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
                    'course' => $review->course ? [
                        'id' => $review->course->id,
                        'course_name' => $review->course->course_name,
                        'image_url' => $review->course->thumbnail_url,
                    ] : null,
                    'created_at' => $review->created_at,
                    'updated_at' => $review->updated_at,
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

    /**
     * Get reviews for a specific course
     */
    public function byCourse(Request $request, Course $course): JsonResponse
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

    /**
     * Get a specific review
     */
    public function show(Request $request, CourseReview $review): JsonResponse
    {
        $review->load(['course', 'student.user']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'review_text' => $review->review_text,
                'would_recommend' => $review->would_recommend,
                'course' => $review->course ? [
                    'id' => $review->course->id,
                    'course_name' => $review->course->course_name,
                    'image_url' => $review->course->thumbnail_url,
                ] : null,
                'student' => [
                    'id' => $review->student->id,
                    'full_name' => $review->student->user->full_name,
                    'image_url' => $review->student->user->profile_picture_url,
                ],
                'created_at' => $review->created_at,
                'updated_at' => $review->updated_at,
            ],
        ]);
    }

    /**
     * Create a new review for a course
     */
    public function store(Request $request, Course $course): JsonResponse
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

    /**
     * Update own review
     */
    public function update(Request $request, CourseReview $review): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student || $review->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
            'would_recommend' => 'sometimes|boolean',
        ]);

        $review->update($validated);

        activity()
            ->causedBy($user)
            ->performedOn($review)
            ->withProperties(['updated_fields' => array_keys($validated)])
            ->log('Review updated');

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'review_text' => $review->review_text,
                'would_recommend' => $review->would_recommend,
                'updated_at' => $review->updated_at,
            ],
        ]);
    }

    /**
     * Delete own review
     */
    public function destroy(Request $request, CourseReview $review): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student || $review->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        activity()
            ->causedBy($user)
            ->performedOn($review)
            ->withProperties(['course_id' => $review->course_id])
            ->log('Review deleted');

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }
}
