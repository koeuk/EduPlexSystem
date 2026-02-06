<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonProgressController extends Controller
{
    /**
     * Get all in-progress lessons for authenticated student
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

        $progress = LessonProgress::where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->with(['lesson.module', 'course'])
            ->orderBy('last_accessed', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $progress->map(function ($item) {
                return [
                    'id' => $item->id,
                    'status' => $item->status,
                    'progress_percentage' => $item->progress_percentage,
                    'time_spent_minutes' => $item->time_spent_minutes,
                    'video_last_position' => $item->video_last_position,
                    'last_accessed' => $item->last_accessed,
                    'lesson' => $item->lesson ? [
                        'id' => $item->lesson->id,
                        'lesson_title' => $item->lesson->lesson_title,
                        'lesson_type' => $item->lesson->lesson_type,
                        'duration_minutes' => $item->lesson->duration_minutes,
                        'image_url' => $item->lesson->video_thumbnail_url,
                        'module' => $item->lesson->module ? [
                            'id' => $item->lesson->module->id,
                            'module_title' => $item->lesson->module->module_title,
                        ] : null,
                    ] : null,
                    'course' => $item->course ? [
                        'id' => $item->course->id,
                        'course_name' => $item->course->course_name,
                        'image_url' => $item->course->thumbnail_url,
                    ] : null,
                ];
            }),
            'pagination' => [
                'current_page' => $progress->currentPage(),
                'per_page' => $progress->perPage(),
                'total' => $progress->total(),
                'total_pages' => $progress->lastPage(),
            ],
        ]);
    }

    /**
     * Get completed lessons for authenticated student
     */
    public function completed(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $progress = LessonProgress::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with(['lesson.module', 'course'])
            ->orderBy('completed_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $progress->map(function ($item) {
                return [
                    'id' => $item->id,
                    'status' => $item->status,
                    'time_spent_minutes' => $item->time_spent_minutes,
                    'completed_at' => $item->completed_at,
                    'lesson' => $item->lesson ? [
                        'id' => $item->lesson->id,
                        'lesson_title' => $item->lesson->lesson_title,
                        'lesson_type' => $item->lesson->lesson_type,
                        'duration_minutes' => $item->lesson->duration_minutes,
                        'module' => $item->lesson->module ? [
                            'id' => $item->lesson->module->id,
                            'module_title' => $item->lesson->module->module_title,
                        ] : null,
                    ] : null,
                    'course' => $item->course ? [
                        'id' => $item->course->id,
                        'course_name' => $item->course->course_name,
                        'image_url' => $item->course->thumbnail_url,
                    ] : null,
                ];
            }),
            'pagination' => [
                'current_page' => $progress->currentPage(),
                'per_page' => $progress->perPage(),
                'total' => $progress->total(),
                'total_pages' => $progress->lastPage(),
            ],
        ]);
    }

    /**
     * Get progress for a specific lesson
     */
    public function show(Request $request, Lesson $lesson): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $progress = LessonProgress::where('student_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if (!$progress) {
            return response()->json([
                'success' => true,
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $progress->id,
                'status' => $progress->status,
                'progress_percentage' => $progress->progress_percentage,
                'time_spent_minutes' => $progress->time_spent_minutes,
                'video_last_position' => $progress->video_last_position,
                'scroll_position' => $progress->scroll_position,
                'first_accessed' => $progress->first_accessed,
                'last_accessed' => $progress->last_accessed,
                'completed_at' => $progress->completed_at,
            ],
        ]);
    }

    /**
     * Update progress for a lesson
     */
    public function update(Request $request, Lesson $lesson): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:not_started,in_progress,completed',
            'progress_percentage' => 'sometimes|numeric|min:0|max:100',
            'time_spent_minutes' => 'sometimes|integer|min:0',
            'video_last_position' => 'sometimes|integer|min:0',
            'scroll_position' => 'sometimes|integer|min:0',
        ]);

        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('course_id', $lesson->course_id)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course',
            ], 403);
        }

        DB::beginTransaction();

        try {
            $progress = LessonProgress::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'lesson_id' => $lesson->id,
                ],
                [
                    'course_id' => $lesson->course_id,
                    'status' => 'not_started',
                    'progress_percentage' => 0,
                    'time_spent_minutes' => 0,
                    'first_accessed' => now(),
                ]
            );

            $updateData = array_merge($validated, ['last_accessed' => now()]);

            if (isset($validated['status']) && $validated['status'] === 'completed' && !$progress->completed_at) {
                $updateData['completed_at'] = now();
                $updateData['progress_percentage'] = 100;

                activity()
                    ->causedBy($user)
                    ->performedOn($lesson)
                    ->withProperties([
                        'lesson_id' => $lesson->id,
                        'course_id' => $lesson->course_id,
                    ])
                    ->log('Lesson completed');
            }

            $progress->update($updateData);

            // Update enrollment progress
            $this->updateEnrollmentProgress($enrollment);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progress updated successfully',
                'data' => [
                    'id' => $progress->id,
                    'status' => $progress->status,
                    'progress_percentage' => $progress->progress_percentage,
                    'time_spent_minutes' => $progress->time_spent_minutes,
                    'video_last_position' => $progress->video_last_position,
                    'scroll_position' => $progress->scroll_position,
                    'completed_at' => $progress->completed_at,
                    'enrollment_progress' => $enrollment->fresh()->progress_percentage,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update progress',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get progress summary by course
     */
    public function byCourse(Request $request, int $courseId): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $progress = LessonProgress::where('student_id', $student->id)
            ->where('course_id', $courseId)
            ->with('lesson.module')
            ->get();

        $totalLessons = Lesson::where('course_id', $courseId)->count();
        $completedLessons = $progress->where('status', 'completed')->count();
        $inProgressLessons = $progress->where('status', 'in_progress')->count();
        $totalTimeSpent = $progress->sum('time_spent_minutes');

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'in_progress_lessons' => $inProgressLessons,
                    'not_started_lessons' => $totalLessons - $completedLessons - $inProgressLessons,
                    'total_time_spent_minutes' => $totalTimeSpent,
                    'completion_percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0,
                ],
                'lessons' => $progress->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'lesson_id' => $item->lesson_id,
                        'lesson_title' => $item->lesson?->lesson_title,
                        'module_title' => $item->lesson?->module?->module_title,
                        'status' => $item->status,
                        'progress_percentage' => $item->progress_percentage,
                        'time_spent_minutes' => $item->time_spent_minutes,
                        'last_accessed' => $item->last_accessed,
                        'completed_at' => $item->completed_at,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Update enrollment progress based on completed lessons
     */
    private function updateEnrollmentProgress(Enrollment $enrollment): void
    {
        $totalLessons = Lesson::where('course_id', $enrollment->course_id)->count();

        if ($totalLessons === 0) {
            return;
        }

        $completedLessons = LessonProgress::where('student_id', $enrollment->student_id)
            ->where('course_id', $enrollment->course_id)
            ->where('status', 'completed')
            ->count();

        $progressPercentage = ($completedLessons / $totalLessons) * 100;

        $enrollment->progress_percentage = $progressPercentage;

        if ($progressPercentage >= 100) {
            $enrollment->status = 'completed';
            $enrollment->completion_date = now();
        }

        $enrollment->save();
    }
}
