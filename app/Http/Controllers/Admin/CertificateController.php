<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CertificateController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Certificate::class)
                ->select('*')
                ->with(['student.user', 'course'])
                ->allowedFilters([
                    AllowedFilter::partial('certificate_code'),
                    AllowedFilter::exact('course_id'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['certificate_code', 'student.user.full_name', 'course.course_name'])),
                ])
                ->allowedSorts(['issue_date', 'certificate_code'])
                ->latest('issue_date')
                ->paginate($this->limit())
                ->withQueryString();

            $courses = Course::where('status', 'published')->get(['id', 'course_name']);

            DB::commit();

            return Inertia::render('Admin/Certificates/Index', [
                'items' => $items,
                'courses' => $courses,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(): Response
    {
        $students = Student::with('user')
            ->whereHas('enrollments', fn($q) => $q->where('status', 'completed')->where('certificate_issued', false))
            ->get();

        $courses = Course::where('status', 'published')->get();

        return Inertia::render('Admin/Certificates/Create', [
            'students' => $students,
            'courses' => $courses,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        DB::beginTransaction();

        try {
            $exists = Certificate::where('student_id', $validated['student_id'])
                ->where('course_id', $validated['course_id'])
                ->exists();

            if ($exists) {
                return back()->withError(__('Certificate already exists for this student and course.'));
            }

            $enrollment = Enrollment::where('student_id', $validated['student_id'])
                ->where('course_id', $validated['course_id'])
                ->first();

            if (!$enrollment) {
                return back()->withError(__('Student is not enrolled in this course.'));
            }

            $certificateCode = Certificate::generateCode();

            Certificate::create([
                'student_id' => $validated['student_id'],
                'course_id' => $validated['course_id'],
                'issue_date' => now(),
                'certificate_code' => $certificateCode,
                'verification_url' => url("/verify/{$certificateCode}"),
            ]);

            $enrollment->update(['certificate_issued' => true]);

            DB::commit();

            return redirect()->route('admin.certificates.index')
                ->withSuccess(__('Certificate issued successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function show(Certificate $certificate): Response
    {
        $certificate->load(['student.user', 'course']);

        return Inertia::render('Admin/Certificates/Show', [
            'certificate' => $certificate,
        ]);
    }

    public function destroy(Certificate $certificate)
    {
        DB::beginTransaction();

        try {
            $enrollment = Enrollment::where('student_id', $certificate->student_id)
                ->where('course_id', $certificate->course_id)
                ->first();

            if ($enrollment) {
                $enrollment->update(['certificate_issued' => false]);
            }

            $certificate->delete();

            DB::commit();

            return redirect()->route('admin.certificates.index')
                ->withSuccess(__('Certificate revoked successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
