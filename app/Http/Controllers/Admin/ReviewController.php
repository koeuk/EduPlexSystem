<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReviewController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(CourseReview::class)
                ->select('*')
                ->with(['student.user', 'course'])
                ->allowedFilters([
                    AllowedFilter::exact('rating'),
                    AllowedFilter::exact('course_id'),
                    AllowedFilter::exact('would_recommend'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['review_text', 'student.user.full_name', 'course.course_name'])),
                ])
                ->allowedSorts(['created_at', 'rating'])
                ->latest()
                ->paginate($this->limit())
                ->withQueryString();

            $courses = Course::where('status', 'published')->get(['id', 'course_name']);

            $avgRating = CourseReview::avg('rating') ?? 0;
            $totalReviews = CourseReview::count();
            $recommendRate = $totalReviews > 0
                ? CourseReview::where('would_recommend', true)->count() / $totalReviews * 100
                : 0;

            DB::commit();

            return Inertia::render('Admin/Reviews/Index', [
                'items' => $items,
                'courses' => $courses,
                'stats' => [
                    'avgRating' => round($avgRating, 1),
                    'totalReviews' => $totalReviews,
                    'recommendRate' => round($recommendRate, 1),
                ],
                'ratingOptions' => [
                    ['value' => '1', 'label' => '1 Star'],
                    ['value' => '2', 'label' => '2 Stars'],
                    ['value' => '3', 'label' => '3 Stars'],
                    ['value' => '4', 'label' => '4 Stars'],
                    ['value' => '5', 'label' => '5 Stars'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function destroy(CourseReview $review)
    {
        DB::beginTransaction();

        try {
            $review->delete();

            DB::commit();

            return redirect()->route('admin.reviews.index')
                ->withSuccess(__('Review deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
