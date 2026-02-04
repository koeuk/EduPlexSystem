<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
            'module_id' => ['nullable', Rule::exists('course_modules', 'id')->where('course_id', $course->id)],
            'lesson_type' => ['required', 'in:video,text,quiz,assignment,document'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'video_duration' => ['nullable', 'integer', 'min:0'],
            'quiz_id' => ['nullable', 'exists:quizzes,id'],
            'is_mandatory' => ['boolean'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'video' => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:512000'],
            'video_thumbnail' => ['nullable', 'image', 'max:2048'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();

        try {
            $maxOrder = $course->lessons()->max('lesson_order') ?? 0;

            $lesson = $course->lessons()->create([
                'lesson_title' => $validated['lesson_title'],
                'module_id' => $validated['module_id'] ?? null,
                'lesson_type' => $validated['lesson_type'],
                'description' => $validated['description'] ?? null,
                'content' => $validated['content'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'video_duration' => $validated['video_duration'] ?? null,
                'quiz_id' => $validated['quiz_id'] ?? null,
                'is_mandatory' => $validated['is_mandatory'] ?? false,
                'duration_minutes' => $validated['duration_minutes'] ?? null,
                'lesson_order' => $maxOrder + 1,
            ]);

            if ($request->hasFile('video')) {
                $lesson->addMediaFromRequest('video')->toMediaCollection('video');
            }
            if ($request->hasFile('video_thumbnail')) {
                $lesson->addMediaFromRequest('video_thumbnail')->toMediaCollection('thumbnail');
            }

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
        if ($lesson->course_id !== $course->id) {
            abort(404);
        }
        $lesson->append(['video_url', 'video_thumbnail_url']);
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
        if ($lesson->course_id !== $course->id) {
            abort(404);
        }
        $validated = $request->validate([
            'lesson_title' => ['required', 'string', 'max:255'],
            'module_id' => ['nullable', Rule::exists('course_modules', 'id')->where('course_id', $course->id)],
            'lesson_type' => ['required', 'in:video,text,quiz,assignment,document'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'video_duration' => ['nullable', 'integer', 'min:0'],
            'quiz_id' => ['nullable', 'exists:quizzes,id'],
            'is_mandatory' => ['boolean'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'video' => ['nullable', 'file', 'mimes:mp4,webm,ogg', 'max:512000'],
            'video_thumbnail' => ['nullable', 'image', 'max:2048'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();

        try {
            $lesson->update([
                'lesson_title' => $validated['lesson_title'],
                'module_id' => $validated['module_id'] ?? null,
                'lesson_type' => $validated['lesson_type'],
                'description' => $validated['description'] ?? null,
                'content' => $validated['content'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'video_duration' => $validated['video_duration'] ?? null,
                'quiz_id' => $validated['quiz_id'] ?? null,
                'is_mandatory' => $validated['is_mandatory'] ?? false,
                'duration_minutes' => $validated['duration_minutes'] ?? null,
            ]);

            if ($request->hasFile('video')) {
                $lesson->clearMediaCollection('video');
                $lesson->addMediaFromRequest('video')->toMediaCollection('video');
            }
            if ($request->hasFile('video_thumbnail')) {
                $lesson->clearMediaCollection('thumbnail');
                $lesson->addMediaFromRequest('video_thumbnail')->toMediaCollection('thumbnail');
            }

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
        if ($lesson->course_id !== $course->id) {
            abort(404);
        }
        DB::beginTransaction();

        try {
            $lesson->clearMediaCollection('video');
            $lesson->clearMediaCollection('thumbnail');
            $lesson->clearMediaCollection('documents');

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
