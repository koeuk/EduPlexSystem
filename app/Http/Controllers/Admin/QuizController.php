<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Quiz;
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

            return redirect()->route('admin.quizzes.index')
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
}
