<?php

namespace App\Http\Controllers\Notifications;

use App\Domain\Notifications\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Display a listing of the authenticated user's notifications.
     */
    public function index(Request $request): JsonResponse|Response
    {
        $user = $request->user();

        $notifications = $user->relationLoaded('notifications')
            ? $user->notifications
            : $user->notifications()->latest()->get();

        $unreadCount = $user->relationLoaded('unreadNotifications')
            ? $user->unreadNotifications->count()
            : $user->unreadNotifications()->count();

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'data' => $notifications,
                'unread_count' => $unreadCount,
            ]);
        }

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $notification = null;
        if ($user->relationLoaded('accessibleNotifications')) {
            $notification = $user->accessibleNotifications->firstWhere('id', $id);
        } elseif ($user->relationLoaded('notifications')) {
            $notification = $user->notifications->firstWhere('id', $id);
        }

        if (! $notification && ! $user->relationLoaded('notifications')) {
            $notification = Notification::find($id)
                ?? DatabaseNotification::find($id);
        }

        if (! $notification) {
            abort(404, 'Notification not found.');
        }

        Gate::authorize('update', $notification);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => $notification,
        ]);
    }

    /**
     * Mark all unread notifications for the authenticated user as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->relationLoaded('unreadNotifications')) {
            foreach ($user->unreadNotifications as $notification) {
                $notification->markAsRead();
            }
        } else {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }
}
