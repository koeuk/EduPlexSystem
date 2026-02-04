<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class QuestionController extends Controller
{
    public function index(Quiz $quiz): Response
    {
        DB::beginTransaction();

        try {
            $quiz->loadCount('questions');

            $items = QueryBuilder::for(QuizQuestion::class)
                ->select('*')
                ->where('quiz_id', $quiz->id)
                ->with('options')
                ->allowedFilters([
                    AllowedFilter::partial('question_text'),
                    AllowedFilter::exact('question_type'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['question_text'])),
                ])
                ->allowedSorts(['question_order', 'points'])
                ->orderBy('question_order')
                ->paginate($this->limit())
                ->withQueryString();

            DB::commit();

            return Inertia::render('Admin/Quizzes/Questions/Index', [
                'quiz' => $quiz,
                'items' => $items,
                'questionTypeOptions' => [
                    ['value' => 'multiple_choice', 'label' => 'Multiple Choice'],
                    ['value' => 'true_false', 'label' => 'True/False'],
                    ['value' => 'short_answer', 'label' => 'Short Answer'],
                    ['value' => 'essay', 'label' => 'Essay'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(Quiz $quiz): Response
    {
        $quiz->loadCount('questions');

        return Inertia::render('Admin/Quizzes/Questions/Create', [
            'quiz' => $quiz,
            'questionTypeOptions' => [
                ['value' => 'multiple_choice', 'label' => 'Multiple Choice'],
                ['value' => 'true_false', 'label' => 'True/False'],
                ['value' => 'short_answer', 'label' => 'Short Answer'],
                ['value' => 'essay', 'label' => 'Essay'],
            ],
        ]);
    }

    public function store(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:multiple_choice,true_false,short_answer,essay'],
            'points' => ['required', 'integer', 'min:1'],
            'explanation' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'options' => ['required_if:question_type,multiple_choice,true_false', 'array'],
            'options.*.option_text' => ['required', 'string'],
            'options.*.is_correct' => ['boolean'],
        ]);

        DB::beginTransaction();

        try {
            $maxOrder = $quiz->questions()->max('question_order') ?? 0;

            $imageUrl = null;
            if ($request->hasFile('image')) {
                $imageUrl = $request->file('image')->store('questions', 'public');
            }

            $question = $quiz->questions()->create([
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'points' => $validated['points'],
                'explanation' => $validated['explanation'] ?? null,
                'image_url' => $imageUrl,
                'question_order' => $maxOrder + 1,
            ]);

            if (!empty($validated['options'])) {
                foreach ($validated['options'] as $index => $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'option_order' => $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.quizzes.questions.index', $quiz)
                ->withSuccess(__('Question created successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function edit(Quiz $quiz, QuizQuestion $question): Response
    {
        $quiz->loadCount('questions');
        $question->load('options');

        return Inertia::render('Admin/Quizzes/Questions/Edit', [
            'quiz' => $quiz,
            'item' => $question,
            'questionTypeOptions' => [
                ['value' => 'multiple_choice', 'label' => 'Multiple Choice'],
                ['value' => 'true_false', 'label' => 'True/False'],
                ['value' => 'short_answer', 'label' => 'Short Answer'],
                ['value' => 'essay', 'label' => 'Essay'],
            ],
        ]);
    }

    public function update(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:multiple_choice,true_false,short_answer,essay'],
            'points' => ['required', 'integer', 'min:1'],
            'explanation' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'options' => ['required_if:question_type,multiple_choice,true_false', 'array'],
            'options.*.id' => ['nullable', 'exists:quiz_options,id'],
            'options.*.option_text' => ['required', 'string'],
            'options.*.is_correct' => ['boolean'],
        ]);

        DB::beginTransaction();

        try {
            $imageUrl = $question->image_url;
            if ($request->hasFile('image')) {
                $imageUrl = $request->file('image')->store('questions', 'public');
            }

            $question->update([
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'points' => $validated['points'],
                'explanation' => $validated['explanation'] ?? null,
                'image_url' => $imageUrl,
            ]);

            $question->options()->delete();

            if (!empty($validated['options'])) {
                foreach ($validated['options'] as $index => $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'option_order' => $index,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.quizzes.questions.index', $quiz)
                ->withSuccess(__('Question updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function destroy(Quiz $quiz, QuizQuestion $question)
    {
        if ($question->answers()->exists()) {
            return back()->withError(__('Cannot delete question with existing answers.'));
        }

        DB::beginTransaction();

        try {
            $question->delete();

            $quiz->questions()
                ->where('question_order', '>', $question->question_order)
                ->decrement('question_order');

            DB::commit();

            return redirect()->route('admin.quizzes.questions.index', $quiz)
                ->withSuccess(__('Question deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function reorder(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'questions' => ['required', 'array'],
            'questions.*.id' => ['required', 'exists:quiz_questions,id'],
            'questions.*.order' => ['required', 'integer', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['questions'] as $questionData) {
                QuizQuestion::where('id', $questionData['id'])
                    ->where('quiz_id', $quiz->id)
                    ->update(['question_order' => $questionData['order']]);
            }

            DB::commit();

            return back()->withSuccess(__('Questions reordered successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
