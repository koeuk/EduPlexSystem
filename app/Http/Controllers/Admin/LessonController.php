<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LessonController extends Controller
{
    public function index(Course $course): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Lesson::class)
                ->select('*')
                ->where('course_id', $course->id)
                ->with(['module', 'quiz'])
                ->allowedFilters([
                    AllowedFilter::partial('lesson_title'),
                    AllowedFilter::exact('lesson_type'),
                    AllowedFilter::exact('module_id'),
                    AllowedFilter::exact('is_mandatory'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['lesson_title', 'description'])),
                ])
                ->allowedSorts(['lesson_order', 'created_at'])
                ->orderBy('lesson_order')
                ->paginate($this->limit())
                ->withQueryString();

            $modules = $course->modules()->orderBy('module_order')->get(['id', 'module_title']);

            DB::commit();

            return Inertia::render('Admin/Lessons/Index', [
                'course' => $course,
                'items' => $items,
                'modules' => $modules,
                'lessonTypeOptions' => [
                    ['value' => 'video', 'label' => 'Video'],
                    ['value' => 'text', 'label' => 'Text'],
                    ['value' => 'quiz', 'label' => 'Quiz'],
                    ['value' => 'assignment', 'label' => 'Assignment'],
                    ['value' => 'document', 'label' => 'Document'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(Course $course): Response
    {
        $modules = $course->modules()->orderBy('module_order')->get(['id', 'module_title']);
        $quizzes = Quiz::orderBy('quiz_title')->get(['id', 'quiz_title']);

        return Inertia::render('Admin/Lessons/Create', [
            'course' => $course,
            'modules' => $modules,
            'quizzes' => $quizzes,
            'lessonTypeOptions' => [
                ['value' => 'video', 'label' => 'Video'],
                ['value' => 'text', 'label' => 'Text'],
                ['value' => 'quiz', 'label' => 'Quiz'],
                ['value' => 'assignment', 'label' => 'Assignment'],
                ['value' => 'document', 'label' => 'Document'],
            ],
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'lesson_title' => ['required', 'string', 'max:255'],
            'module_id' => ['nullable', 'exists:course_modules,id'],
            'lesson_type' => ['required', 'in:video,text,quiz,assignment,document'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url'],
            'video_duration' => ['nullable', 'integer', 'min:0'],
            'quiz_id' => ['nullable', 'exists:quizzes,id'],
            'is_mandatory' => ['boolean'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'video_thumbnail' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();

        try {
            $maxOrder = $course->lessons()->max('lesson_order') ?? 0;

            $videoThumbnail = null;
            if ($request->hasFile('video_thumbnail')) {
                $videoThumbnail = $request->file('video_thumbnail')->store('lesson-thumbnails', 'public');
            }

            $course->lessons()->create([
                ...$validated,
                'video_thumbnail' => $videoThumbnail,
                'lesson_order' => $maxOrder + 1,
            ]);

            DB::commit();

            return redirect()->route('admin.courses.lessons.index', $course)
                ->withSuccess(__('Lesson created successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function edit(Course $course, Lesson $lesson): Response
    {
        $modules = $course->modules()->orderBy('module_order')->get(['id', 'module_title']);
        $quizzes = Quiz::orderBy('quiz_title')->get(['id', 'quiz_title']);

        return Inertia::render('Admin/Lessons/Edit', [
            'course' => $course,
            'item' => $lesson,
            'modules' => $modules,
            'quizzes' => $quizzes,
            'lessonTypeOptions' => [
                ['value' => 'video', 'label' => 'Video'],
                ['value' => 'text', 'label' => 'Text'],
                ['value' => 'quiz', 'label' => 'Quiz'],
                ['value' => 'assignment', 'label' => 'Assignment'],
                ['value' => 'document', 'label' => 'Document'],
            ],
        ]);
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $validated = $request->validate([
            'lesson_title' => ['required', 'string', 'max:255'],
            'module_id' => ['nullable', 'exists:course_modules,id'],
            'lesson_type' => ['required', 'in:video,text,quiz,assignment,document'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url'],
            'video_duration' => ['nullable', 'integer', 'min:0'],
            'quiz_id' => ['nullable', 'exists:quizzes,id'],
            'is_mandatory' => ['boolean'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'video_thumbnail' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('video_thumbnail')) {
                if ($lesson->video_thumbnail) {
                    Storage::disk('public')->delete($lesson->video_thumbnail);
                }
                $validated['video_thumbnail'] = $request->file('video_thumbnail')->store('lesson-thumbnails', 'public');
            }

            $lesson->update($validated);

            DB::commit();

            return redirect()->route('admin.courses.lessons.index', $course)
                ->withSuccess(__('Lesson updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        DB::beginTransaction();

        try {
            if ($lesson->video_thumbnail) {
                Storage::disk('public')->delete($lesson->video_thumbnail);
            }

            $lesson->delete();

            $course->lessons()
                ->where('lesson_order', '>', $lesson->lesson_order)
                ->decrement('lesson_order');

            DB::commit();

            return redirect()->route('admin.courses.lessons.index', $course)
                ->withSuccess(__('Lesson deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function reorder(Request $request, Course $course)
    {
        $validated = $request->validate([
            'lessons' => ['required', 'array'],
            'lessons.*.id' => ['required', 'exists:lessons,id'],
            'lessons.*.order' => ['required', 'integer', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['lessons'] as $lessonData) {
                Lesson::where('id', $lessonData['id'])
                    ->where('course_id', $course->id)
                    ->update(['lesson_order' => $lessonData['order']]);
            }

            DB::commit();

            return back()->withSuccess(__('Lessons reordered successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
