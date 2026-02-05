<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LessonType;
use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Services\VideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LessonController extends Controller
{
    public function __construct(
        protected VideoService $videoService
    ) {}
    public function index(Course $course, CourseModule $module): Response
    {
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Lesson::class)
                ->select('*')
                ->where('course_id', $course->id)
                ->where('module_id', $module->id)
                ->with(['quiz'])
                ->allowedFilters([
                    AllowedFilter::partial('lesson_title'),
                    AllowedFilter::exact('lesson_type'),
                    AllowedFilter::exact('is_mandatory'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['lesson_title', 'description'])),
                ])
                ->allowedSorts(['lesson_order', 'created_at'])
                ->orderBy('lesson_order')
                ->paginate($this->limit())
                ->withQueryString();

            $quizzes = Quiz::orderBy('quiz_title')->get(['id', 'quiz_title']);

            DB::commit();

            return Inertia::render('Admin/Courses/Modules/Lessons/Index', [
                'course' => $course,
                'module' => $module,
                'items' => $items,
                'quizzes' => $quizzes,
                'lessonTypeOptions' => $this->getLessonTypeOptions(),
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(Course $course, CourseModule $module): Response
    {
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $quizzes = Quiz::orderBy('quiz_title')->get(['id', 'quiz_title']);

        return Inertia::render('Admin/Courses/Modules/Lessons/Create', [
            'course' => $course,
            'module' => $module,
            'quizzes' => $quizzes,
            'lessonTypeOptions' => $this->getLessonTypeOptions(),
        ]);
    }

    public function edit(Course $course, CourseModule $module, Lesson $lesson): Response
    {
        if ($module->course_id !== $course->id || $lesson->module_id !== $module->id) {
            abort(404);
        }

        $quizzes = Quiz::orderBy('quiz_title')->get(['id', 'quiz_title']);

        return Inertia::render('Admin/Courses/Modules/Lessons/Edit', [
            'course' => $course,
            'module' => $module,
            'item' => $lesson,
            'quizzes' => $quizzes,
            'lessonTypeOptions' => $this->getLessonTypeOptions(),
        ]);
    }

    public function show(Course $course, CourseModule $module, Lesson $lesson): Response
    {
        if ($module->course_id !== $course->id || $lesson->module_id !== $module->id) {
            abort(404);
        }

        $lesson->load('quiz');

        return Inertia::render('Admin/Courses/Modules/Lessons/Show', [
            'course' => $course,
            'module' => $module,
            'item' => $lesson,
        ]);
    }

    private function getLessonTypeOptions(): array
    {
        return LessonType::options();
    }

    public function store(Request $request, Course $course, CourseModule $module)
    {
        if ($module->course_id !== $course->id) {
            abort(404);
        }

        $validated = $request->validate([
            'lesson_title' => ['required', 'string', 'max:255'],
            'lesson_type' => ['required', Rule::in(LessonType::values())],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'video_duration' => ['nullable', 'integer', 'min:0'],
            'quiz_id' => ['nullable', 'exists:quizzes,id'],
            'is_mandatory' => ['boolean'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'video' => $this->videoService->getValidationRules(),
        ]);

        DB::beginTransaction();

        try {
            $maxOrder = $module->lessons()->max('lesson_order') ?? 0;

            $imageUrl = null;
            if ($request->hasFile('image')) {
                $imageUrl = $request->file('image')->store('lessons', 'public');
            }

            $videoUrl = null;
            if ($request->hasFile('video')) {
                $videoData = $this->videoService->upload($request->file('video'), 'lessons');
                $videoUrl = $videoData['path'];
            }

            $module->lessons()->create([
                'course_id' => $course->id,
                'lesson_title' => $validated['lesson_title'],
                'lesson_type' => $validated['lesson_type'],
                'description' => $validated['description'] ?? null,
                'content' => $validated['content'] ?? null,
                'image_url' => $imageUrl,
                'video_url' => $videoUrl,
                'video_duration' => $validated['video_duration'] ?? null,
                'quiz_id' => $validated['quiz_id'] ?? null,
                'is_mandatory' => $validated['is_mandatory'] ?? false,
                'duration_minutes' => $validated['duration_minutes'] ?? null,
                'lesson_order' => $maxOrder + 1,
            ]);

            DB::commit();

            return redirect()->route('admin.courses.modules.lessons.index', [$course, $module])
                ->withSuccess(__('Lesson created successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function update(Request $request, Course $course, CourseModule $module, Lesson $lesson)
    {
        if ($module->course_id !== $course->id || $lesson->module_id !== $module->id) {
            abort(404);
        }

        $validated = $request->validate([
            'lesson_title' => ['required', 'string', 'max:255'],
            'lesson_type' => ['required', Rule::in(LessonType::values())],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'video_duration' => ['nullable', 'integer', 'min:0'],
            'quiz_id' => ['nullable', 'exists:quizzes,id'],
            'is_mandatory' => ['boolean'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'video' => $this->videoService->getValidationRules(),
        ]);

        DB::beginTransaction();

        try {
            $updateData = [
                'lesson_title' => $validated['lesson_title'],
                'lesson_type' => $validated['lesson_type'],
                'description' => $validated['description'] ?? null,
                'content' => $validated['content'] ?? null,
                'video_duration' => $validated['video_duration'] ?? null,
                'quiz_id' => $validated['quiz_id'] ?? null,
                'is_mandatory' => $validated['is_mandatory'] ?? false,
                'duration_minutes' => $validated['duration_minutes'] ?? null,
            ];

            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($lesson->image_url && Storage::disk('public')->exists($lesson->image_url)) {
                    Storage::disk('public')->delete($lesson->image_url);
                }
                $updateData['image_url'] = $request->file('image')->store('lessons', 'public');
            }

            if ($request->hasFile('video')) {
                // Delete old video
                $this->videoService->delete($lesson->video_url);
                // Upload new video
                $videoData = $this->videoService->upload($request->file('video'), 'lessons');
                $updateData['video_url'] = $videoData['path'];
            }

            $lesson->update($updateData);

            DB::commit();

            return redirect()->route('admin.courses.modules.lessons.index', [$course, $module])
                ->withSuccess(__('Lesson updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function destroy(Course $course, CourseModule $module, Lesson $lesson)
    {
        if ($module->course_id !== $course->id || $lesson->module_id !== $module->id) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            $lessonOrder = $lesson->lesson_order;
            $lesson->delete();

            $module->lessons()
                ->where('lesson_order', '>', $lessonOrder)
                ->decrement('lesson_order');

            DB::commit();

            return redirect()->route('admin.courses.modules.lessons.index', [$course, $module])
                ->withSuccess(__('Lesson deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
