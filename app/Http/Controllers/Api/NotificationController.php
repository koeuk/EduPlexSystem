<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = QueryBuilder::for(Notification::class)
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::exact('is_read'),
            ])
            ->allowedSorts(['created_at'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($request->input('per_page', 20));

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'related_id' => $notification->related_id,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at,
                ];
            }),
            'unread_count' => $unreadCount,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'total_pages' => $notifications->lastPage(),
            ],
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $user = $request->user();

        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $user = $request->user();

        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
}
