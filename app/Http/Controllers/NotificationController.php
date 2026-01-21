<?php

namespace App\Http\Controllers;

use App\Models\ProposalNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display all notifications for the user
     */
    public function index(Request $request): View
    {
        $notifications = ProposalNotification::where('user_id', Auth::id())
            ->with('proposal')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(ProposalNotification $notification): JsonResponse
    {
        // Ensure user owns this notification
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(Auth::user());

        return response()->json([
            'success' => true,
            'message' => __('decisions.messages.all_read'),
            'marked_count' => $count,
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount(Auth::user());

        return response()->json([
            'count' => $count,
        ]);
    }
}
