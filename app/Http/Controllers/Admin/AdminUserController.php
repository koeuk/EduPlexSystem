<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Admin::class)
                ->select('admins.*')
                ->with('user')
                ->join('users', 'admins.user_id', '=', 'users.id')
                ->allowedFilters([
                    AllowedFilter::partial('full_name', 'users.full_name'),
                    AllowedFilter::partial('email', 'users.email'),
                    AllowedFilter::exact('status', 'users.status'),
                    AllowedFilter::partial('department'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['users.full_name', 'users.email', 'users.username', 'department'])),
                ])
                ->allowedSorts(['created_at', 'users.full_name'])
                ->latest('admins.created_at')
                ->paginate($this->limit())
                ->withQueryString();

            DB::commit();

            return Inertia::render('Admin/Admins/Index', [
                'items' => $items,
                'statusOptions' => [
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                    ['value' => 'suspended', 'label' => 'Suspended'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function show(Admin $admin): Response
    {
        DB::beginTransaction();

        try {
            $admin->load('user');

            DB::commit();

            return Inertia::render('Admin/Admins/Show', [
                'item' => $admin,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create()
    {
        return Inertia::render('Admin/Admins/Create');
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
            'department' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'] ?? null,
                'user_type' => 'admin',
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'status' => 'active',
            ]);

            if ($request->hasFile('profile_picture')) {
                $user->addMediaFromRequest('profile_picture')
                    ->toMediaCollection('profile_picture');
            }

            Admin::create([
                'user_id' => $user->id,
                'department' => $validated['department'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('admin.admins.index')->withSuccess(__('Admin created successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function edit(Admin $admin): Response
    {
        $admin->load('user');

        return Inertia::render('Admin/Admins/Edit', [
            'item' => $admin,
        ]);
    }

    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($admin->user_id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($admin->user_id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,suspended'],
            'department' => ['nullable', 'string', 'max:255'],
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
                $admin->user->clearMediaCollection('profile_picture');
                $admin->user->addMediaFromRequest('profile_picture')
                    ->toMediaCollection('profile_picture');
            }

            $admin->user->update($userData);
            $admin->update(['department' => $validated['department'] ?? null]);

            DB::commit();

            return redirect()->route('admin.admins.index')->withSuccess(__('Admin updated successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function destroy(Admin $admin)
    {
        if ($admin->user_id === auth()->id()) {
            return back()->withError(__('You cannot delete your own account.'));
        }

        DB::beginTransaction();

        try {
            $admin->user->clearMediaCollection('profile_picture');
            $admin->user->delete();

            DB::commit();

            return redirect()->route('admin.admins.index')->withSuccess(__('Admin deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
