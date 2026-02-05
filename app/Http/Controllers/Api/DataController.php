<?php

namespace App\Http\Controllers\Api;

use App\Enums\CoursePricingType;
use App\Enums\LessonType;
use App\Enums\StudentStatus;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataController extends Controller
{
    /**
     * Get all dropdown options
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $this->getCategoriesData(),
                'courses' => $this->getCoursesData(),
                'userStatuses' => UserStatus::options(),
                'studentStatuses' => StudentStatus::options(),
                'lessonTypes' => LessonType::options(),
                'coursePricingTypes' => CoursePricingType::options(),
            ],
        ]);
    }

    /**
     * Get categories for dropdown
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getCategoriesData(),
        ]);
    }

    /**
     * Get courses for dropdown
     */
    public function courses(Request $request): JsonResponse
    {
        $categoryId = $request->query('category_id');

        return response()->json([
            'success' => true,
            'data' => $this->getCoursesData($categoryId),
        ]);
    }

    /**
     * Get courses by category for dropdown
     */
    public function coursesByCategory(Category $category): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getCoursesData($category->id),
        ]);
    }

    /**
     * Get user status options
     */
    public function userStatuses(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => UserStatus::options(),
        ]);
    }

    /**
     * Get student status options
     */
    public function studentStatuses(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => StudentStatus::options(),
        ]);
    }

    /**
     * Get lesson type options
     */
    public function lessonTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => LessonType::options(),
        ]);
    }

    /**
     * Get course pricing type options
     */
    public function coursePricingTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => CoursePricingType::options(),
        ]);
    }

    /**
     * Get course level options
     */
    public function courseLevels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                ['value' => 'beginner', 'label' => 'Beginner'],
                ['value' => 'intermediate', 'label' => 'Intermediate'],
                ['value' => 'advanced', 'label' => 'Advanced'],
            ],
        ]);
    }

    /**
     * Get all course filter options
     */
    public function courseFilters(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $this->getCategoriesData(),
                'levels' => [
                    ['value' => 'beginner', 'label' => 'Beginner'],
                    ['value' => 'intermediate', 'label' => 'Intermediate'],
                    ['value' => 'advanced', 'label' => 'Advanced'],
                ],
                'pricing_types' => CoursePricingType::options(),
            ],
        ]);
    }

    /**
     * Get categories data
     */
    private function getCategoriesData(): array
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('category_name')
            ->get(['id', 'category_name'])
            ->map(fn($category) => [
                'value' => $category->id,
                'label' => $category->category_name,
            ])
            ->toArray();
    }

    /**
     * Get courses data
     */
    private function getCoursesData(?int $categoryId = null): array
    {
        $query = Course::query()
            ->where('status', 'published')
            ->orderBy('course_name');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query
            ->get(['id', 'course_name', 'category_id'])
            ->map(fn($course) => [
                'value' => $course->id,
                'label' => $course->course_name,
                'category_id' => $course->category_id,
            ])
            ->toArray();
    }
}
