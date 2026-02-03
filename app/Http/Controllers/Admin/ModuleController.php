<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ModuleController extends Controller
{
    public function index(Course $course): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(CourseModule::class)
                ->select('*')
                ->where('course_id', $course->id)
                ->withCount('lessons')
                ->allowedFilters([
                    AllowedFilter::partial('module_title'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['module_title', 'description'])),
                ])
                ->allowedSorts(['module_order', 'created_at'])
                ->orderBy('module_order')
                ->paginate($this->limit())
                ->withQueryString();

            DB::commit();

            return Inertia::render('Admin/Modules/Index', [
                'course' => $course,
                'items' => $items,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(Course $course): Response
    {
        return Inertia::render('Admin/Modules/Create', [
            'course' => $course,
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'module_title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $maxOrder = $course->modules()->max('module_order') ?? 0;

            $course->modules()->create([
                ...$validated,
                'module_order' => $maxOrder + 1,
            ]);

            DB::commit();

            return redirect()->route('admin.courses.modules.index', $course)
                ->withSuccess(__('Module created successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function edit(Course $course, CourseModule $module): Response
    {
        return Inertia::render('Admin/Modules/Edit', [
            'course' => $course,
            'item' => $module,
        ]);
    }

    public function update(Request $request, Course $course, CourseModule $module)
    {
        $validated = $request->validate([
            'module_title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $module->update($validated);

            DB::commit();

            return redirect()->route('admin.courses.modules.index', $course)
                ->withSuccess(__('Module updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function destroy(Course $course, CourseModule $module)
    {
        DB::beginTransaction();

        try {
            $module->delete();

            $course->modules()
                ->where('module_order', '>', $module->module_order)
                ->decrement('module_order');

            DB::commit();

            return redirect()->route('admin.courses.modules.index', $course)
                ->withSuccess(__('Module deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function reorder(Request $request, Course $course)
    {
        $validated = $request->validate([
            'modules' => ['required', 'array'],
            'modules.*.id' => ['required', 'exists:course_modules,id'],
            'modules.*.order' => ['required', 'integer', 'min:0'],
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['modules'] as $moduleData) {
                CourseModule::where('id', $moduleData['id'])
                    ->where('course_id', $course->id)
                    ->update(['module_order' => $moduleData['order']]);
            }

            DB::commit();

            return back()->withSuccess(__('Modules reordered successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
