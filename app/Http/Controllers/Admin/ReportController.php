<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Reports/Index');
    }

    public function students(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Student::class)
                ->select('students.*')
                ->with('user')
                ->withCount(['enrollments', 'certificates'])
                ->withSum('payments', 'amount')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->allowedFilters([
                    AllowedFilter::exact('student_status'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['users.full_name', 'users.email', 'student_id_number'])),
                ])
                ->allowedSorts(['created_at', 'enrollment_date', 'enrollments_count'])
                ->latest('students.created_at')
                ->paginate($this->limit())
                ->withQueryString();

            $stats = [
                'totalStudents' => Student::count(),
                'activeStudents' => Student::where('student_status', 'active')->count(),
                'graduatedStudents' => Student::where('student_status', 'graduated')->count(),
            ];

            DB::commit();

            return Inertia::render('Admin/Reports/Students', [
                'items' => $items,
                'stats' => $stats,
                'statusOptions' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                    ['value' => 'graduated', 'label' => 'Graduated'],
                    ['value' => 'suspended', 'label' => 'Suspended'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function enrollments(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Enrollment::class)
                ->select('enrollments.*')
                ->with(['student.user', 'course'])
                ->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('course_id'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['student.user.full_name', 'course.course_name'])),
                ])
                ->allowedSorts(['enrollment_date', 'progress_percentage'])
                ->latest('enrollment_date')
                ->paginate($this->limit())
                ->withQueryString();

            $courses = Course::where('status', 'published')->get(['id', 'course_name']);

            $stats = [
                'totalEnrollments' => Enrollment::count(),
                'activeEnrollments' => Enrollment::where('status', 'active')->count(),
                'completedEnrollments' => Enrollment::where('status', 'completed')->count(),
                'avgProgress' => round(Enrollment::where('status', 'active')->avg('progress_percentage') ?? 0, 1),
            ];

            DB::commit();

            return Inertia::render('Admin/Reports/Enrollments', [
                'items' => $items,
                'courses' => $courses,
                'stats' => $stats,
                'statusOptions' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'dropped', 'label' => 'Dropped'],
                    ['value' => 'expired', 'label' => 'Expired'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function revenue(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Payment::class)
                ->select('*')
                ->with(['student.user', 'course'])
                ->where('status', 'completed')
                ->allowedFilters([
                    AllowedFilter::exact('course_id'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['transaction_id', 'student.user.full_name', 'course.course_name'])),
                ])
                ->allowedSorts(['payment_date', 'amount'])
                ->latest('payment_date')
                ->paginate($this->limit())
                ->withQueryString();

            $courses = Course::where('status', 'published')->get(['id', 'course_name']);

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

            $revenueByCourse = Course::withSum(['payments' => fn($q) => $q->where('status', 'completed')], 'amount')
                ->having('payments_sum_amount', '>', 0)
                ->orderByDesc('payments_sum_amount')
                ->take(10)
                ->get(['id', 'course_name', 'payments_sum_amount']);

            $stats = [
                'totalRevenue' => Payment::where('status', 'completed')->sum('amount'),
                'thisMonthRevenue' => Payment::where('status', 'completed')
                    ->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount'),
                'avgPayment' => round(Payment::where('status', 'completed')->avg('amount') ?? 0, 2),
            ];

            DB::commit();

            return Inertia::render('Admin/Reports/Revenue', [
                'items' => $items,
                'courses' => $courses,
                'monthlyRevenue' => $monthlyRevenue,
                'revenueByCourse' => $revenueByCourse,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function coursePerformance(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Course::class)
                ->select('*')
                ->with('category')
                ->withCount(['enrollments', 'lessons', 'reviews'])
                ->withAvg('reviews', 'rating')
                ->withSum(['payments' => fn($q) => $q->where('status', 'completed')], 'amount')
                ->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('category_id'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['course_name', 'course_code'])),
                ])
                ->allowedSorts(['enrollments_count', 'reviews_avg_rating', 'payments_sum_amount'])
                ->orderByDesc('enrollments_count')
                ->paginate($this->limit())
                ->withQueryString();

            $categories = Category::where('is_active', true)->get(['id', 'category_name']);

            DB::commit();

            return Inertia::render('Admin/Reports/CoursePerformance', [
                'items' => $items,
                'categories' => $categories,
                'statusOptions' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'published', 'label' => 'Published'],
                    ['value' => 'archived', 'label' => 'Archived'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function exportStudents(Request $request): StreamedResponse
    {
        $students = Student::with('user')
            ->withCount(['enrollments', 'certificates'])
            ->get();

        return $this->exportCsv($students, 'students', [
            'ID' => 'id',
            'Student ID' => 'student_id_number',
            'Name' => fn($s) => $s->user->full_name,
            'Email' => fn($s) => $s->user->email,
            'Status' => 'student_status',
            'Enrollment Date' => fn($s) => $s->enrollment_date?->format('Y-m-d'),
            'Enrollments' => 'enrollments_count',
            'Certificates' => 'certificates_count',
        ]);
    }

    public function exportEnrollments(Request $request): StreamedResponse
    {
        $enrollments = Enrollment::with(['student.user', 'course'])->get();

        return $this->exportCsv($enrollments, 'enrollments', [
            'ID' => 'id',
            'Student' => fn($e) => $e->student->user->full_name,
            'Email' => fn($e) => $e->student->user->email,
            'Course' => fn($e) => $e->course->course_name,
            'Enrollment Date' => fn($e) => $e->enrollment_date?->format('Y-m-d'),
            'Status' => 'status',
            'Progress' => fn($e) => $e->progress_percentage . '%',
            'Payment Status' => 'payment_status',
        ]);
    }

    public function exportRevenue(Request $request): StreamedResponse
    {
        $payments = Payment::with(['student.user', 'course'])
            ->where('status', 'completed')
            ->get();

        return $this->exportCsv($payments, 'revenue', [
            'ID' => 'id',
            'Transaction ID' => 'transaction_id',
            'Student' => fn($p) => $p->student->user->full_name,
            'Course' => fn($p) => $p->course->course_name,
            'Amount' => fn($p) => '$' . number_format($p->amount, 2),
            'Payment Method' => 'payment_method',
            'Date' => fn($p) => $p->payment_date?->format('Y-m-d'),
            'Status' => 'status',
        ]);
    }

    private function exportCsv($data, string $filename, array $columns): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}_" . date('Y-m-d') . '.csv',
        ];

        return response()->stream(function () use ($data, $columns) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, array_keys($columns));

            foreach ($data as $item) {
                $row = [];
                foreach ($columns as $column) {
                    if (is_callable($column)) {
                        $row[] = $column($item);
                    } else {
                        $row[] = $item->{$column};
                    }
                }
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
