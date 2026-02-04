<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EnrollmentController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Enrollment::class)
                ->select('enrollments.*')
                ->with(['student.user', 'course'])
                ->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('payment_status'),
                    AllowedFilter::exact('course_id'),
                    AllowedFilter::exact('student_id'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['student.user.full_name', 'student.user.email', 'course.course_name'])),
                ])
                ->allowedSorts(['enrollment_date', 'progress_percentage', 'status'])
                ->latest('enrollment_date')
                ->paginate($this->limit())
                ->withQueryString();

            $courses = Course::where('status', 'published')->get(['id', 'course_name', 'course_code']);

            DB::commit();

            return Inertia::render('Admin/Enrollments/Index', [
                'items' => $items,
                'courses' => $courses,
                'statusOptions' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'completed', 'label' => 'Completed'],
                    ['value' => 'dropped', 'label' => 'Dropped'],
                    ['value' => 'expired', 'label' => 'Expired'],
                ],
                'paymentStatusOptions' => [
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'paid', 'label' => 'Paid'],
                    ['value' => 'failed', 'label' => 'Failed'],
                    ['value' => 'refunded', 'label' => 'Refunded'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function show(Enrollment $enrollment): Response
    {
        DB::beginTransaction();

        try {
            $enrollment->load(['student.user', 'course.lessons']);

            $lessonProgress = $enrollment->student->lessonProgress()
                ->where('course_id', $enrollment->course_id)
                ->with('lesson')
                ->get();

            DB::commit();

            return Inertia::render('Admin/Enrollments/Show', [
                'item' => $enrollment,
                'lessonProgress' => $lessonProgress,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(): Response
    {
        $students = Student::with('user')
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->where('student_status', 'active')
            ->get();

        $courses = Course::where('status', 'published')->get();

        return Inertia::render('Admin/Enrollments/Create', [
            'students' => $students,
            'courses' => $courses,
            'paymentStatusOptions' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'paid', 'label' => 'Paid'],
                ['value' => 'failed', 'label' => 'Failed'],
                ['value' => 'refunded', 'label' => 'Refunded'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'payment_status' => ['required', 'in:pending,paid,failed,refunded'],
        ]);

        DB::beginTransaction();

        try {
            $exists = Enrollment::where('student_id', $validated['student_id'])
                ->where('course_id', $validated['course_id'])
                ->exists();

            if ($exists) {
                return back()->withError(__('Student is already enrolled in this course.'));
            }

            $course = Course::find($validated['course_id']);
            if ($course->enrollment_limit) {
                $currentEnrollments = $course->enrollments()->count();
                if ($currentEnrollments >= $course->enrollment_limit) {
                    return back()->withError(__('Course enrollment limit has been reached.'));
                }
            }

            $student = Student::with('user')->find($validated['student_id']);

            $enrollment = Enrollment::create([
                'student_id' => $validated['student_id'],
                'course_id' => $validated['course_id'],
                'enrollment_date' => now(),
                'status' => 'active',
                'payment_status' => $validated['payment_status'],
                'progress_percentage' => 0,
            ]);

            // Send notification to all admins
            $admins = User::where('user_type', 'admin')
                ->where('status', 'active')
                ->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => __('New Course Enrollment'),
                    'message' => __(':student has enrolled in :course', [
                        'student' => $student->user->full_name,
                        'course' => $course->course_name,
                    ]),
                    'type' => 'enrollment',
                    'related_id' => $enrollment->id,
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.enrollments.index')
                ->withSuccess(__('Student enrolled successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function update(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,completed,dropped,expired'],
            'payment_status' => ['required', 'in:pending,paid,failed,refunded'],
        ]);

        DB::beginTransaction();

        try {
            $enrollment->update($validated);

            DB::commit();

            return back()->withSuccess(__('Enrollment updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function destroy(Enrollment $enrollment)
    {
        DB::beginTransaction();

        try {
            $enrollment->delete();

            DB::commit();

            return redirect()->route('admin.enrollments.index')
                ->withSuccess(__('Enrollment deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
