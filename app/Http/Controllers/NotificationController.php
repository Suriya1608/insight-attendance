<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Return latest 20 notifications + unread count (AJAX).
     */
    public function index(): JsonResponse
    {
        $user = $this->currentUser();

        $notifications = AppNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'title', 'message', 'type', 'url', 'is_read', 'created_at']);

        $unreadCount = AppNotification::unreadCount($user->id);

        return response()->json([
            'unread_count'  => $unreadCount,
            'notifications' => $notifications->map(fn ($n) => [
                'id'           => $n->getRouteKey(),   // encrypted ID for mark-read URL
                'title'        => $n->title,
                'message'      => $n->message,
                'type'         => $n->type,
                'url'          => $n->url,
                'is_read'      => $n->is_read,
                'time'         => $n->created_at->diffForHumans(),
                'formatted_at' => $n->created_at->format('d M Y, h:i A'),
            ]),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(AppNotification $notification): JsonResponse
    {
        if ($notification->user_id !== $this->currentUser()->id) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): JsonResponse
    {
        $user = $this->currentUser();

        AppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['ok' => true, 'unread_count' => 0]);
    }

    /**
     * Return just the unread count (for polling).
     */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread_count' => AppNotification::unreadCount($this->currentUser()->id),
        ]);
    }
}
