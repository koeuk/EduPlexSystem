<?php

namespace App\Http\Controllers\Admin;

use App\Filters\UniversalSearchFilter;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        DB::beginTransaction();

        try {
            $items = QueryBuilder::for(Notification::class)
                ->select('*')
                ->with('user')
                ->allowedFilters([
                    AllowedFilter::partial('title'),
                    AllowedFilter::exact('type'),
                    AllowedFilter::exact('is_read'),
                    AllowedFilter::custom('search', new UniversalSearchFilter(['title', 'message', 'user.full_name'])),
                ])
                ->allowedSorts(['created_at', 'type'])
                ->latest('created_at')
                ->paginate($this->limit())
                ->withQueryString();

            DB::commit();

            return Inertia::render('Admin/Notifications/Index', [
                'items' => $items,
                'typeOptions' => [
                    ['value' => 'info', 'label' => 'Info'],
                    ['value' => 'success', 'label' => 'Success'],
                    ['value' => 'warning', 'label' => 'Warning'],
                    ['value' => 'error', 'label' => 'Error'],
                    ['value' => 'enrollment', 'label' => 'Enrollment'],
                    ['value' => 'completion', 'label' => 'Completion'],
                    ['value' => 'reminder', 'label' => 'Reminder'],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function create(): Response
    {
        $users = User::where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'email', 'user_type']);

        return Inertia::render('Admin/Notifications/Create', [
            'users' => $users,
            'typeOptions' => [
                ['value' => 'info', 'label' => 'Info'],
                ['value' => 'success', 'label' => 'Success'],
                ['value' => 'warning', 'label' => 'Warning'],
                ['value' => 'error', 'label' => 'Error'],
                ['value' => 'enrollment', 'label' => 'Enrollment'],
                ['value' => 'completion', 'label' => 'Completion'],
                ['value' => 'reminder', 'label' => 'Reminder'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'in:info,success,warning,error,enrollment,completion,reminder'],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['user_ids'] as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'type' => $validated['type'],
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.notifications.index')
                ->withSuccess(__('Notifications sent successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function sendBulk(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['required', 'in:info,success,warning,error,enrollment,completion,reminder'],
            'target' => ['required', 'in:all,students,admins'],
        ]);

        DB::beginTransaction();

        try {
            $query = User::where('status', 'active');

            if ($validated['target'] === 'students') {
                $query->where('user_type', 'student');
            } elseif ($validated['target'] === 'admins') {
                $query->where('user_type', 'admin');
            }

            $users = $query->get();

            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'type' => $validated['type'],
                    'is_read' => false,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.notifications.index')
                ->withSuccess(__('Bulk notifications sent to :count users.', ['count' => $users->count()]));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage())->withInput();
        }
    }

    public function markAsRead(Notification $notification)
    {
        DB::beginTransaction();

        try {
            $notification->update(['is_read' => true]);

            DB::commit();

            return back()->withSuccess(__('Notification marked as read.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }

    public function destroy(Notification $notification)
    {
        DB::beginTransaction();

        try {
            $notification->delete();

            DB::commit();

            return redirect()->route('admin.notifications.index')
                ->withSuccess(__('Notification deleted successfully.'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withError($e->getMessage());
        }
    }
}
