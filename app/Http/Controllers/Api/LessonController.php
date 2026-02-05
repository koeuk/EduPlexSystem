<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
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

        $lesson->load(['module', 'quiz.questions.options']);

        $progress = LessonProgress::where('student_id', $student->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if (!$progress) {
            $progress = LessonProgress::create([
                'student_id' => $student->id,
                'lesson_id' => $lesson->id,
                'course_id' => $lesson->course_id,
                'status' => 'in_progress',
                'progress_percentage' => 0,
                'time_spent_minutes' => 0,
                'first_accessed' => now(),
                'last_accessed' => now(),
            ]);
        } else {
            $progress->update(['last_accessed' => now()]);
        }

        activity()
            ->causedBy($user)
            ->performedOn($lesson)
            ->withProperties([
                'lesson_id' => $lesson->id,
                'course_id' => $lesson->course_id,
            ])
            ->log('Viewed lesson');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $lesson->id,
                'lesson_title' => $lesson->lesson_title,
                'lesson_type' => $lesson->lesson_type,
                'lesson_order' => $lesson->lesson_order,
                'description' => $lesson->description,
                'content' => $lesson->content,
                'image_url' => $lesson->image_url ? asset('storage/' . $lesson->image_url) : null,
                'video_url' => $lesson->video_url ? asset('storage/' . $lesson->video_url) : null,
                'video_duration' => $lesson->video_duration,
                'formatted_duration' => $lesson->formatted_duration,
                'thumbnail' => $lesson->video_thumbnail_url,
                'documents' => $lesson->getMedia('documents')->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'name' => $media->name,
                        'file_name' => $media->file_name,
                        'mime_type' => $media->mime_type,
                        'size' => $media->size,
                        'url' => $media->getUrl(),
                    ];
                }),
                'duration_minutes' => $lesson->duration_minutes,
                'is_mandatory' => $lesson->is_mandatory,
                'module' => $lesson->module ? [
                    'id' => $lesson->module->id,
                    'module_title' => $lesson->module->module_title,
                ] : null,
                'quiz' => $lesson->quiz ? [
                    'id' => $lesson->quiz->id,
                    'quiz_title' => $lesson->quiz->quiz_title,
                    'instructions' => $lesson->quiz->instructions,
                    'time_limit_minutes' => $lesson->quiz->time_limit_minutes,
                    'passing_score' => $lesson->quiz->passing_score,
                    'max_attempts' => $lesson->quiz->max_attempts,
                    'total_questions' => $lesson->quiz->questions->count(),
                ] : null,
                'progress' => [
                    'id' => $progress->id,
                    'status' => $progress->status,
                    'progress_percentage' => $progress->progress_percentage,
                    'time_spent_minutes' => $progress->time_spent_minutes,
                    'video_last_position' => $progress->video_last_position,
                    'scroll_position' => $progress->scroll_position,
                    'completed_at' => $progress->completed_at,
                ],
            ],
        ]);
    }

    public function updateProgress(Request $request, Lesson $lesson): JsonResponse
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

    public function getProgress(Request $request, Lesson $lesson): JsonResponse
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
