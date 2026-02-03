<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'user_type' => 'student',
                'status' => 'active',
            ]);

            if ($request->hasFile('profile_picture')) {
                $user->addMediaFromRequest('profile_picture')
                    ->toMediaCollection('profile_picture');
            }

            $student = Student::create([
                'user_id' => $user->id,
                'student_id_number' => 'STU-' . date('Y') . '-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                'enrollment_date' => now(),
                'student_status' => 'active',
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($student)
                ->withProperties(['student_id' => $student->id])
                ->log('Student registered');

            $token = $user->createToken('mobile-app')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'full_name' => $user->full_name,
                        'profile_picture' => $user->profile_picture_url,
                    ],
                    'student' => [
                        'id' => $student->id,
                        'student_id_number' => $student->student_id_number,
                    ],
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => ['email' => 'The provided credentials are incorrect.'],
            ], 401);
        }

        if ($user->user_type !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
                'errors' => ['email' => 'This app is for students only.'],
            ], 403);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Account inactive',
                'errors' => ['email' => 'Your account is not active.'],
            ], 403);
        }

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('Student logged in');

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                    'profile_picture' => $user->profile_picture_url,
                ],
                'student' => $user->student ? [
                    'id' => $user->student->id,
                    'student_id_number' => $user->student->student_id_number,
                    'student_status' => $user->student->student_status,
                ] : null,
                'token' => $token,
            ],
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('student');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'phone' => $user->phone,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'gender' => $user->gender,
                'address' => $user->address,
                'profile_picture' => $user->profile_picture_url,
                'student' => $user->student ? [
                    'id' => $user->student->id,
                    'student_id_number' => $user->student->student_id_number,
                    'enrollment_date' => $user->student->enrollment_date?->format('Y-m-d'),
                    'student_status' => $user->student->student_status,
                ] : null,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $user->update($validated);

            if ($request->hasFile('profile_picture')) {
                $user->clearMediaCollection('profile_picture');
                $user->addMediaFromRequest('profile_picture')
                    ->toMediaCollection('profile_picture');
            }

            activity()
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties(['updated_fields' => array_keys($validated)])
                ->log('Profile updated');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'phone' => $user->phone,
                    'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                    'gender' => $user->gender,
                    'address' => $user->address,
                    'profile_picture' => $user->fresh()->profile_picture_url,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'errors' => ['current_password' => 'The current password is incorrect.'],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('Password changed');

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('Student logged out');

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
