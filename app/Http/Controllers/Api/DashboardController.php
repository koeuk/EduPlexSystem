<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $enrolledCourses = Enrollment::where('student_id', $student->id)
            ->whereIn('status', ['active', 'completed'])
            ->count();

        $completedCourses = Enrollment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->count();

        $inProgressCourses = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->where('progress_percentage', '<', 100)
            ->count();

        $certificates = Certificate::where('student_id', $student->id)->count();

        $totalLearningTime = LessonProgress::where('student_id', $student->id)
            ->sum('time_spent_minutes');

        $averageProgress = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->avg('progress_percentage') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'enrolled_courses' => $enrolledCourses,
                'completed_courses' => $completedCourses,
                'in_progress_courses' => $inProgressCourses,
                'certificates' => $certificates,
                'total_learning_time_minutes' => $totalLearningTime,
                'total_learning_time_hours' => round($totalLearningTime / 60, 1),
                'average_progress' => round($averageProgress, 1),
            ],
        ]);
    }

    public function recentActivity(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $recentProgress = LessonProgress::where('student_id', $student->id)
            ->with(['lesson.course', 'lesson.module'])
            ->orderBy('last_accessed', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recentProgress->map(function ($progress) {
                return [
                    'id' => $progress->id,
                    'status' => $progress->status,
                    'progress_percentage' => $progress->progress_percentage,
                    'time_spent_minutes' => $progress->time_spent_minutes,
                    'last_accessed' => $progress->last_accessed,
                    'completed_at' => $progress->completed_at,
                    'lesson' => [
                        'id' => $progress->lesson->id,
                        'lesson_title' => $progress->lesson->lesson_title,
                        'lesson_type' => $progress->lesson->lesson_type,
                        'image_url' => $progress->lesson->video_thumbnail_url,
                    ],
                    'course' => [
                        'id' => $progress->lesson->course->id,
                        'course_name' => $progress->lesson->course->course_name,
                        'image_url' => $progress->lesson->course->thumbnail_url,
                    ],
                    'module' => $progress->lesson->module ? [
                        'id' => $progress->lesson->module->id,
                        'module_title' => $progress->lesson->module->module_title,
                    ] : null,
                ];
            }),
        ]);
    }

    public function activityLog(Request $request): JsonResponse
    {
        $user = $request->user();

        $activities = QueryBuilder::for(Activity::class)
            ->allowedFilters([
                AllowedFilter::partial('description'),
            ])
            ->allowedSorts(['created_at'])
            ->where('causer_type', 'App\\Models\\User')
            ->where('causer_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'subject_type' => $activity->subject_type ? class_basename($activity->subject_type) : null,
                    'subject_id' => $activity->subject_id,
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at,
                ];
            }),
        ]);
    }

    public function continueLearning(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $enrollments = Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->where('progress_percentage', '<', 100)
            ->with('course')
            ->orderBy('last_accessed', 'desc')
            ->limit(5)
            ->get();

        $courses = $enrollments->map(function ($enrollment) use ($student) {
            // Get the last accessed lesson or the next incomplete lesson
            $lastProgress = LessonProgress::where('student_id', $student->id)
                ->where('course_id', $enrollment->course_id)
                ->orderBy('last_accessed', 'desc')
                ->first();

            $nextLesson = null;
            if ($lastProgress) {
                $nextLesson = $lastProgress->lesson;
            } else {
                // Get first lesson of the course
                $nextLesson = $enrollment->course->lessons()
                    ->orderBy('lesson_order')
                    ->first();
            }

            return [
                'enrollment_id' => $enrollment->id,
                'progress_percentage' => $enrollment->progress_percentage,
                'last_accessed' => $enrollment->last_accessed,
                'course' => [
                    'id' => $enrollment->course->id,
                    'course_name' => $enrollment->course->course_name,
                    'image_url' => $enrollment->course->thumbnail_url,
                ],
                'next_lesson' => $nextLesson ? [
                    'id' => $nextLesson->id,
                    'lesson_title' => $nextLesson->lesson_title,
                    'lesson_type' => $nextLesson->lesson_type,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }
}
