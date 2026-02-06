<?php

namespace App\Http\Controllers\Api;

use App\Filters\PriceRangeFilter;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = QueryBuilder::for(Category::class)
            ->allowedFilters([
                AllowedFilter::partial('category_name'),
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts(['category_name', 'created_at'])
            ->where('is_active', true)
            ->withCount(['courses' => function ($query) {
                $query->where('status', 'published');
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'category_name' => $category->category_name,
                    'description' => $category->description,
                    'icon' => $category->icon,
                    'image_url' => $category->full_image_url,
                    'is_active' => $category->is_active,
                    'courses_count' => $category->courses_count,
                ];
            }),
        ]);
    }

    public function show(Category $category): JsonResponse
    {
        if (!$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $category->loadCount(['courses' => function ($query) {
            $query->where('status', 'published');
        }]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $category->id,
                'category_name' => $category->category_name,
                'description' => $category->description,
                'icon' => $category->icon,
                'image_url' => $category->full_image_url,
                'is_active' => $category->is_active,
                'courses_count' => $category->courses_count,
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ],
        ]);
    }

    public function courses(Request $request, Category $category): JsonResponse
    {
        if (!$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $courses = QueryBuilder::for(Course::class)
            ->allowedFilters([
                AllowedFilter::exact('level'),
                AllowedFilter::exact('pricing_type'),
                AllowedFilter::custom('price_range', new PriceRangeFilter()),
                AllowedFilter::exact('is_featured'),
            ])
            ->allowedSorts(['course_name', 'price', 'created_at'])
            ->where('category_id', $category->id)
            ->where('status', 'published')
            ->withCount('enrollments')
            ->withAvg('reviews', 'rating')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'course_name' => $course->course_name,
                    'course_code' => $course->course_code,
                    'description' => $course->description,
                    'image_url' => $course->thumbnail_url,
                    'level' => $course->level,
                    'duration_hours' => $course->duration_hours,
                    'pricing_type' => $course->pricing_type,
                    'is_free' => $course->pricing_type === 'free',
                    'price' => $course->price,
                    'instructor_name' => $course->instructor_name,
                    'is_featured' => $course->is_featured,
                    'status' => $course->status,
                    'enrollments_count' => $course->enrollments_count,
                    'average_rating' => round($course->reviews_avg_rating ?? 0, 1),
                    'created_at' => $course->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'total_pages' => $courses->lastPage(),
            ],
            'links' => [
                'first' => $courses->url(1),
                'last' => $courses->url($courses->lastPage()),
                'prev' => $courses->previousPageUrl(),
                'next' => $courses->nextPageUrl(),
            ],
        ]);
    }
}
