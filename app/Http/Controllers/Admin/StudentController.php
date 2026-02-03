<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StudentController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Student::class)
                ->select('students.*')
                ->with('user')
                ->join('users', 'students.user_id', '=', 'users.id')
                ->allowedFilters([
                    AllowedFilter::partial('student_id_number'),
                    AllowedFilter::partial('full_name', 'users.full_name'),
                    AllowedFilter::partial('email', 'users.email'),
                    AllowedFilter::exact('student_status'),
                    AllowedFilter::exact('status', 'users.status'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['users.full_name', 'users.email', 'student_id_number'])),
                ])
                ->allowedSorts(['created_at', 'student_id_number', 'enrollment_date'])
                ->latest('students.created_at')
                ->paginate($this->limit())
                ->withQueryString();

            DB::commit();

            return Inertia::render('Admin/Students/Index', [
                'items' => $items,
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

    public function show(Student $student): Response
    {
        DB::beginTransaction();

        try {
            $student->load([
                'user',
                'enrollments.course',
                'payments.course',
                'certificates.course',
                'quizAttempts.quiz',
            ]);

            $lessonProgress = $student->lessonProgress()
                ->with(['lesson', 'course'])
                ->latest('last_accessed')
                ->take(10)
                ->get();

            DB::commit();

            return Inertia::render('Admin/Students/Show', [
                'item' => $student,
                'lessonProgress' => $lessonProgress,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Students/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string'],
            'student_id_number' => ['required', 'string', 'max:50', 'unique:students'],
            'enrollment_date' => ['nullable', 'date'],
            'student_status' => ['required', 'in:active,inactive,graduated,suspended'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();

        try {
            $profilePicture = null;
            if ($request->hasFile('profile_picture')) {
                $profilePicture = $request->file('profile_picture')->store('profile-pictures', 'public');
            }

            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'] ?? null,
                'user_type' => 'student',
                'profile_picture' => $profilePicture,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => 'active',
            ]);

            Student::create([
                'user_id' => $user->id,
                'student_id_number' => $validated['student_id_number'],
                'enrollment_date' => $validated['enrollment_date'] ?? now(),
                'student_status' => $validated['student_status'],
            ]);

            DB::commit();

            return redirect()->route('admin.students.index')->withSuccess(__('Student created successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function edit(Student $student): Response
    {
        $student->load('user');

        return Inertia::render('Admin/Students/Edit', [
            'item' => $student,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($student->user_id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($student->user_id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'student_id_number' => ['required', 'string', 'max:50', Rule::unique('students')->ignore($student->id)],
            'enrollment_date' => ['nullable', 'date'],
            'student_status' => ['required', 'in:active,inactive,graduated,suspended'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();

        try {
            $userData = [
                'username' => $validated['username'],
                'email' => $validated['email'],
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => $validated['status'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            if ($request->hasFile('profile_picture')) {
                if ($student->user->profile_picture) {
                    Storage::disk('public')->delete($student->user->profile_picture);
                }
                $userData['profile_picture'] = $request->file('profile_picture')->store('profile-pictures', 'public');
            }

            $student->user->update($userData);
            $student->update([
                'student_id_number' => $validated['student_id_number'],
                'enrollment_date' => $validated['enrollment_date'],
                'student_status' => $validated['student_status'],
            ]);

            DB::commit();

            return redirect()->route('admin.students.index')->withSuccess(__('Student updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function destroy(Student $student)
    {
        DB::beginTransaction();

        try {
            if ($student->user->profile_picture) {
                Storage::disk('public')->delete($student->user->profile_picture);
            }
            $student->user->delete();

            DB::commit();

            return redirect()->route('admin.students.index')->withSuccess(__('Student deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
