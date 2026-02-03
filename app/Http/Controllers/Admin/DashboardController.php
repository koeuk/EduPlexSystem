<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        DB::beginTransaction();

        try {
            $totalStudents = Student::count();
            $totalCourses = Course::count();
            $publishedCourses = Course::where('status', 'published')->count();
            $totalEnrollments = Enrollment::count();
            $activeEnrollments = Enrollment::where('status', 'active')->count();
            $completedEnrollments = Enrollment::where('status', 'completed')->count();
            $totalRevenue = Payment::where('status', 'completed')->sum('amount');

            $monthlyEnrollments = Enrollment::select(
                DB::raw('YEAR(enrollment_date) as year'),
                DB::raw('MONTH(enrollment_date) as month'),
                DB::raw('COUNT(*) as count')
            )
                ->where('enrollment_date', '>=', now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $monthlyRevenue = Payment::select(
                DB::raw('YEAR(payment_date) as year'),
                DB::raw('MONTH(payment_date) as month'),
                DB::raw('SUM(amount) as total')
            )
                ->where('status', 'completed')
                ->where('payment_date', '>=', now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $recentEnrollments = Enrollment::with(['student.user', 'course'])
                ->latest('enrollment_date')
                ->take(5)
                ->get();

            $recentPayments = Payment::with(['student.user', 'course'])
                ->where('status', 'completed')
                ->latest('payment_date')
                ->take(5)
                ->get();

            $topCourses = Course::withCount('enrollments')
                ->orderByDesc('enrollments_count')
                ->take(5)
                ->get();

            $courseStatusCounts = Course::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status');

            DB::commit();

            return Inertia::render('Admin/Dashboard', [
                'stats' => [
                    'totalStudents' => $totalStudents,
                    'totalCourses' => $totalCourses,
                    'publishedCourses' => $publishedCourses,
                    'totalEnrollments' => $totalEnrollments,
                    'activeEnrollments' => $activeEnrollments,
                    'completedEnrollments' => $completedEnrollments,
                    'totalRevenue' => $totalRevenue,
                ],
                'monthlyEnrollments' => $monthlyEnrollments,
                'monthlyRevenue' => $monthlyRevenue,
                'recentEnrollments' => $recentEnrollments,
                'recentPayments' => $recentPayments,
                'topCourses' => $topCourses,
                'courseStatusCounts' => $courseStatusCounts,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
