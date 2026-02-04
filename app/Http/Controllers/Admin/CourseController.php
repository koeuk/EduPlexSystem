<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Course::class)
                ->select('*')
                ->with(['category', 'admin.user'])
                ->withCount(['enrollments', 'lessons', 'modules'])
                ->allowedFilters([
                    AllowedFilter::partial('course_name'),
                    AllowedFilter::partial('course_code'),
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('category_id'),
                    AllowedFilter::exact('level'),
                    AllowedFilter::exact('is_featured'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['course_name', 'course_code', 'instructor_name'])),
                ])
                ->allowedSorts(['created_at', 'course_name', 'price', 'enrollments_count'])
                ->latest()
                ->paginate($this->limit())
                ->withQueryString();

            $categories = Category::where('is_active', true)->get(['id', 'category_name']);

            DB::commit();

            return Inertia::render('Admin/Courses/Index', [
                'items' => $items,
                'categories' => $categories,
                'statusOptions' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'published', 'label' => 'Published'],
                    ['value' => 'archived', 'label' => 'Archived'],
                ],
                'levelOptions' => [
                    ['value' => 'beginner', 'label' => 'Beginner'],
                    ['value' => 'intermediate', 'label' => 'Intermediate'],
                    ['value' => 'advanced', 'label' => 'Advanced'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function show(Course $course): Response
    {
        DB::beginTransaction();

        try {
            $course->load([
                'category',
                'admin.user',
                'modules.lessons',
                'lessons',
                'enrollments.student.user',
                'reviews.student.user',
            ]);

            $stats = [
                'totalEnrollments' => $course->enrollments()->count(),
                'activeEnrollments' => $course->enrollments()->where('status', 'active')->count(),
                'completedEnrollments' => $course->enrollments()->where('status', 'completed')->count(),
                'averageRating' => $course->reviews()->avg('rating') ?? 0,
                'totalRevenue' => $course->payments()->where('status', 'completed')->sum('amount'),
            ];

            DB::commit();

            return Inertia::render('Admin/Courses/Show', [
                'item' => $course,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(): Response
    {
        $categories = Category::where('is_active', true)->get(['id', 'category_name']);

        return Inertia::render('Admin/Courses/Create', [
            'categories' => $categories,
            'levelOptions' => [
                ['value' => 'beginner', 'label' => 'Beginner'],
                ['value' => 'intermediate', 'label' => 'Intermediate'],
                ['value' => 'advanced', 'label' => 'Advanced'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_name' => ['required', 'string', 'max:255'],
            'course_code' => ['required', 'string', 'max:50', 'unique:courses'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'duration_hours' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'instructor_name' => ['nullable', 'string', 'max:255'],
            'enrollment_limit' => ['nullable', 'integer', 'min:1'],
            'is_featured' => ['boolean'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();

        try {
            $thumbnail = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail')->store('course-thumbnails', 'public');
            }

            Course::create([
                ...$validated,
                'thumbnail' => $thumbnail,
                'image_url' => $validated['image_url'] ?? null,
                'admin_id' => auth()->user()->admin->id,
                'status' => 'draft',
            ]);

            DB::commit();

            return redirect()->route('admin.courses.index')->withSuccess(__('Course created successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function edit(Course $course): Response
    {
        $categories = Category::where('is_active', true)->get(['id', 'category_name']);

        return Inertia::render('Admin/Courses/Edit', [
            'item' => $course,
            'categories' => $categories,
            'levelOptions' => [
                ['value' => 'beginner', 'label' => 'Beginner'],
                ['value' => 'intermediate', 'label' => 'Intermediate'],
                ['value' => 'advanced', 'label' => 'Advanced'],
            ],
            'statusOptions' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'published', 'label' => 'Published'],
                ['value' => 'archived', 'label' => 'Archived'],
            ],
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'course_name' => ['required', 'string', 'max:255'],
            'course_code' => ['required', 'string', 'max:50', Rule::unique('courses')->ignore($course->id)],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'duration_hours' => ['nullable', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'instructor_name' => ['nullable', 'string', 'max:255'],
            'enrollment_limit' => ['nullable', 'integer', 'min:1'],
            'is_featured' => ['boolean'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);

        DB::beginTransaction();

        try {
            if ($request->hasFile('thumbnail')) {
                if ($course->thumbnail) {
                    Storage::disk('public')->delete($course->thumbnail);
                }
                $validated['thumbnail'] = $request->file('thumbnail')->store('course-thumbnails', 'public');
            }

            $course->update($validated);

            DB::commit();

            return redirect()->route('admin.courses.index')->withSuccess(__('Course updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function destroy(Course $course)
    {
        if ($course->enrollments()->exists()) {
            return back()->withError(__('Cannot delete course with active enrollments.'));
        }

        DB::beginTransaction();

        try {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $course->delete();

            DB::commit();

            return redirect()->route('admin.courses.index')->withSuccess(__('Course deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function updateStatus(Request $request, Course $course)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,published,archived'],
        ]);

        DB::beginTransaction();

        try {
            $course->update(['status' => $validated['status']]);

            DB::commit();

            return back()->withSuccess(__('Course status updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
