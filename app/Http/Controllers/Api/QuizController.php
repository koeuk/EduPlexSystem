<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class QuizController extends Controller
{
    public function show(Request $request, Quiz $quiz): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        // Check if student is enrolled in a course that has this quiz
        $lesson = $quiz->lessons()->first();
        if ($lesson) {
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
        }

        $quiz->load('questions.options');

        $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->count();

        $canAttempt = !$quiz->max_attempts || $attemptCount < $quiz->max_attempts;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $quiz->id,
                'quiz_title' => $quiz->quiz_title,
                'instructions' => $quiz->instructions,
                'time_limit_minutes' => $quiz->time_limit_minutes,
                'passing_score' => $quiz->passing_score,
                'max_attempts' => $quiz->max_attempts,
                'show_correct_answers' => $quiz->show_correct_answers,
                'randomize_questions' => $quiz->randomize_questions,
                'total_points' => $quiz->questions->sum('points'),
                'total_questions' => $quiz->questions->count(),
                'attempts_used' => $attemptCount,
                'can_attempt' => $canAttempt,
                'questions' => $quiz->questions->map(function ($question) use ($quiz) {
                    $options = $question->options;
                    if ($quiz->randomize_questions) {
                        $options = $options->shuffle();
                    }
                    return [
                        'id' => $question->id,
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'points' => $question->points,
                        'question_order' => $question->question_order,
                        'image_url' => $question->image_url,
                        'options' => $options->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'option_text' => $option->option_text,
                                'option_order' => $option->option_order,
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }

    public function startAttempt(Request $request, Quiz $quiz): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $attemptCount = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->count();

        if ($quiz->max_attempts && $attemptCount >= $quiz->max_attempts) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum attempts reached',
            ], 422);
        }

        // Check for incomplete attempt
        $incompleteAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNull('submitted_at')
            ->first();

        if ($incompleteAttempt) {
            return response()->json([
                'success' => true,
                'message' => 'Resuming existing attempt',
                'data' => [
                    'attempt_id' => $incompleteAttempt->id,
                    'attempt_number' => $incompleteAttempt->attempt_number,
                    'started_at' => $incompleteAttempt->started_at,
                ],
            ]);
        }

        DB::beginTransaction();

        try {
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
                'attempt_number' => $attemptCount + 1,
                'started_at' => now(),
                'max_points' => $quiz->questions->sum('points'),
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($attempt)
                ->withProperties([
                    'quiz_id' => $quiz->id,
                    'attempt_number' => $attempt->attempt_number,
                ])
                ->log('Quiz attempt started');

            DB::commit();

            $quiz->load('questions.options');

            $questions = $quiz->questions;
            if ($quiz->randomize_questions) {
                $questions = $questions->shuffle();
            }

            return response()->json([
                'success' => true,
                'message' => 'Quiz attempt started',
                'data' => [
                    'attempt_id' => $attempt->id,
                    'attempt_number' => $attempt->attempt_number,
                    'started_at' => $attempt->started_at,
                    'time_limit_minutes' => $quiz->time_limit_minutes,
                    'questions' => $questions->map(function ($question) {
                        return [
                            'id' => $question->id,
                            'question_text' => $question->question_text,
                            'question_type' => $question->question_type,
                            'points' => $question->points,
                            'image_url' => $question->image_url,
                            'options' => $question->options->shuffle()->map(function ($option) {
                                return [
                                    'id' => $option->id,
                                    'option_text' => $option->option_text,
                                ];
                            }),
                        ];
                    }),
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to start quiz attempt',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function submitAttempt(Request $request, QuizAttempt $attempt): JsonResponse
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:quiz_questions,id',
            'answers.*.selected_option_id' => 'nullable|exists:quiz_options,id',
            'answers.*.answer_text' => 'nullable|string',
        ]);

        $user = $request->user();
        $student = $user->student;

        if (!$student || $attempt->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Attempt not found',
            ], 404);
        }

        if ($attempt->submitted_at) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz already submitted',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $quiz = $attempt->quiz;
            $totalPoints = 0;
            $maxPoints = 0;
            $answers = [];

            foreach ($validated['answers'] as $answerData) {
                $question = $quiz->questions()->find($answerData['question_id']);
                if (!$question) {
                    continue;
                }

                $maxPoints += $question->points;
                $isCorrect = false;
                $pointsEarned = 0;

                if ($question->question_type === 'multiple_choice' || $question->question_type === 'true_false') {
                    if ($answerData['selected_option_id']) {
                        $option = QuizOption::find($answerData['selected_option_id']);
                        if ($option && $option->is_correct) {
                            $isCorrect = true;
                            $pointsEarned = $question->points;
                        }
                    }
                }

                $totalPoints += $pointsEarned;

                $answer = QuizAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $answerData['question_id'],
                    'selected_option_id' => $answerData['selected_option_id'] ?? null,
                    'answer_text' => $answerData['answer_text'] ?? null,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                ]);

                $answers[] = $answer;
            }

            $scorePercentage = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
            $passed = $scorePercentage >= $quiz->passing_score;
            $timeTaken = now()->diffInMinutes($attempt->started_at);

            $attempt->update([
                'submitted_at' => now(),
                'score_percentage' => $scorePercentage,
                'total_points' => $totalPoints,
                'max_points' => $maxPoints,
                'passed' => $passed,
                'time_taken_minutes' => $timeTaken,
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($attempt)
                ->withProperties([
                    'quiz_id' => $quiz->id,
                    'score_percentage' => $scorePercentage,
                    'passed' => $passed,
                ])
                ->log('Quiz submitted');

            DB::commit();

            $responseData = [
                'attempt_id' => $attempt->id,
                'score_percentage' => round($scorePercentage, 2),
                'total_points' => $totalPoints,
                'max_points' => $maxPoints,
                'passed' => $passed,
                'passing_score' => $quiz->passing_score,
                'time_taken_minutes' => $timeTaken,
            ];

            if ($quiz->show_correct_answers) {
                $responseData['answers'] = collect($answers)->map(function ($answer) {
                    $question = $answer->question;
                    $correctOption = $question->options()->where('is_correct', true)->first();

                    return [
                        'question_id' => $answer->question_id,
                        'question_text' => $question->question_text,
                        'selected_option_id' => $answer->selected_option_id,
                        'correct_option_id' => $correctOption?->id,
                        'is_correct' => $answer->is_correct,
                        'points_earned' => $answer->points_earned,
                        'explanation' => $question->explanation,
                    ];
                });
            }

            return response()->json([
                'success' => true,
                'message' => $passed ? 'Congratulations! You passed the quiz.' : 'Quiz submitted. You did not pass.',
                'data' => $responseData,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit quiz',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function getAttempt(Request $request, QuizAttempt $attempt): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student || $attempt->student_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Attempt not found',
            ], 404);
        }

        $attempt->load(['quiz', 'answers.question.options']);

        $quiz = $attempt->quiz;

        $responseData = [
            'id' => $attempt->id,
            'quiz' => [
                'id' => $quiz->id,
                'quiz_title' => $quiz->quiz_title,
            ],
            'attempt_number' => $attempt->attempt_number,
            'score_percentage' => $attempt->score_percentage,
            'total_points' => $attempt->total_points,
            'max_points' => $attempt->max_points,
            'passed' => $attempt->passed,
            'started_at' => $attempt->started_at,
            'submitted_at' => $attempt->submitted_at,
            'time_taken_minutes' => $attempt->time_taken_minutes,
        ];

        if ($quiz->show_correct_answers && $attempt->submitted_at) {
            $responseData['answers'] = $attempt->answers->map(function ($answer) {
                $correctOption = $answer->question->options()->where('is_correct', true)->first();

                return [
                    'question_id' => $answer->question_id,
                    'question_text' => $answer->question->question_text,
                    'question_type' => $answer->question->question_type,
                    'image_url' => $answer->question->image_url,
                    'selected_option_id' => $answer->selected_option_id,
                    'selected_option_text' => $answer->selectedOption?->option_text,
                    'correct_option_id' => $correctOption?->id,
                    'correct_option_text' => $correctOption?->option_text,
                    'answer_text' => $answer->answer_text,
                    'is_correct' => $answer->is_correct,
                    'points_earned' => $answer->points_earned,
                    'max_points' => $answer->question->points,
                    'explanation' => $answer->question->explanation,
                ];
            });
        }

        return response()->json([
            'success' => true,
            'data' => $responseData,
        ]);
    }

    public function getAttempts(Request $request, Quiz $quiz): JsonResponse
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found',
            ], 404);
        }

        $attempts = QueryBuilder::for(QuizAttempt::class)
            ->allowedSorts(['started_at', 'score_percentage'])
            ->where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->whereNotNull('submitted_at')
            ->latest('started_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attempts->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'attempt_number' => $attempt->attempt_number,
                    'score_percentage' => $attempt->score_percentage,
                    'total_points' => $attempt->total_points,
                    'max_points' => $attempt->max_points,
                    'passed' => $attempt->passed,
                    'started_at' => $attempt->started_at,
                    'submitted_at' => $attempt->submitted_at,
                    'time_taken_minutes' => $attempt->time_taken_minutes,
                ];
            }),
        ]);
    }
}
