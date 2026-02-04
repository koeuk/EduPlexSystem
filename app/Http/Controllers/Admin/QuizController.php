<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class QuizController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Quiz::class)
                ->select('*')
                ->withCount(['questions', 'attempts'])
                ->allowedFilters([
                    AllowedFilter::partial('quiz_title'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['quiz_title', 'instructions'])),
                ])
                ->allowedSorts(['created_at', 'quiz_title', 'questions_count'])
                ->latest('created_at')
                ->paginate($this->limit())
                ->withQueryString();

            DB::commit();

            return Inertia::render('Admin/Quizzes/Index', [
                'items' => $items,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Quizzes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'quiz_title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'passing_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'show_correct_answers' => ['boolean'],
            'randomize_questions' => ['boolean'],
        ]);

        DB::beginTransaction();

        try {
            $quiz = Quiz::create($validated);

            DB::commit();

            return redirect()->route('admin.quizzes.questions.index', $quiz)
                ->withSuccess(__('Quiz created successfully. Now add questions.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function show(Quiz $quiz): Response
    {
        $quiz->load(['questions.options']);
        $quiz->loadCount(['questions', 'attempts']);

        return Inertia::render('Admin/Quizzes/Show', [
            'item' => $quiz,
            'questions' => $quiz->questions,
        ]);
    }

    public function edit(Quiz $quiz): Response
    {
        $quiz->loadCount(['questions', 'attempts']);

        return Inertia::render('Admin/Quizzes/Edit', [
            'item' => $quiz,
        ]);
    }

    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'quiz_title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'passing_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:1'],
            'show_correct_answers' => ['boolean'],
            'randomize_questions' => ['boolean'],
        ]);

        DB::beginTransaction();

        try {
            $quiz->update($validated);

            DB::commit();

            return redirect()->route('admin.quizzes.edit', $quiz)
                ->withSuccess(__('Quiz updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function destroy(Quiz $quiz)
    {
        if ($quiz->attempts()->exists()) {
            return back()->withError(__('Cannot delete quiz with existing attempts.'));
        }

        if ($quiz->lessons()->exists()) {
            return back()->withError(__('Cannot delete quiz that is attached to lessons.'));
        }

        DB::beginTransaction();

        try {
            $quiz->delete();

            DB::commit();

            return redirect()->route('admin.quizzes.index')
                ->withSuccess(__('Quiz deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function attempts(Request $request, Quiz $quiz): Response
    {
        $quiz->loadCount('questions');

        // Calculate stats
        $quiz->passed_count = $quiz->attempts()->where('passed', true)->count();
        $quiz->failed_count = $quiz->attempts()->where('passed', false)->whereNotNull('submitted_at')->count();
        $quiz->average_score = $quiz->attempts()->whereNotNull('submitted_at')->avg('score_percentage');

        $query = QuizAttempt::where('quiz_id', $quiz->id)
            ->with('student')
            ->latest('started_at');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'passed') {
                $query->where('passed', true);
            } elseif ($status === 'failed') {
                $query->where('passed', false)->whereNotNull('submitted_at');
            } elseif ($status === 'in_progress') {
                $query->whereNull('submitted_at');
            }
        }

        $items = $query->paginate($this->limit())->withQueryString();

        return Inertia::render('Admin/Quizzes/Attempts/Index', [
            'quiz' => $quiz,
            'items' => $items,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function showAttempt(Quiz $quiz, QuizAttempt $attempt): Response
    {
        if ($attempt->quiz_id !== $quiz->id) {
            abort(404);
        }

        $quiz->loadCount('questions');

        $attempt->load([
            'student',
            'answers.question.options',
            'answers.selectedOption',
        ]);

        return Inertia::render('Admin/Quizzes/Attempts/Show', [
            'quiz' => $quiz,
            'item' => $attempt,
        ]);
    }
}
