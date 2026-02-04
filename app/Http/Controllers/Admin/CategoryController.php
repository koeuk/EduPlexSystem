<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Category::class)
                ->select('*')
                ->withCount('courses')
                ->allowedFilters([
                    AllowedFilter::partial('category_name'),
                    AllowedFilter::exact('is_active'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['category_name', 'description'])),
                ])
                ->allowedSorts(['created_at', 'category_name', 'courses_count'])
                ->latest()
                ->paginate($this->limit())
                ->withQueryString();

            DB::commit();

            return Inertia::render('Admin/Categories/Index', [
                'items' => $items,
                'statusOptions' => [
                    ['value' => '1', 'label' => 'Active'],
                    ['value' => '0', 'label' => 'Inactive'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        DB::beginTransaction();

        try {
            Category::create($validated);

            DB::commit();

            return redirect()->route('admin.categories.index')->withSuccess(__('Category created successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Admin/Categories/Edit', [
            'item' => $category,
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'category_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        DB::beginTransaction();

        try {
            $category->update($validated);

            DB::commit();

            return redirect()->route('admin.categories.index')->withSuccess(__('Category updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function destroy(Category $category)
    {
        if ($category->courses()->exists()) {
            return back()->withError(__('Cannot delete category with associated courses.'));
        }

        DB::beginTransaction();

        try {
            $category->delete();

            DB::commit();

            return redirect()->route('admin.categories.index')->withSuccess(__('Category deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
