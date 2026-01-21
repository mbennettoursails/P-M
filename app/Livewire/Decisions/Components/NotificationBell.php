<?php

namespace App\Livewire\Decisions\Components;

use App\Models\ProposalNotification;
use App\Services\NotificationService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    public bool $isOpen = false;
    public int $unreadCount = 0;

    protected $listeners = ['notification-received' => 'refreshCount', 'vote-cast' => 'refreshCount', 'comment-added' => 'refreshCount'];

    public function mount() { $this->refreshCount(); }

    public function refreshCount() { $this->unreadCount = ProposalNotification::getUnreadCountForUser(Auth::user()); }
    public function toggle() { $this->isOpen = !$this->isOpen; }
    public function close() { $this->isOpen = false; }

    public function markAsRead(string $uuid, NotificationService $notificationService)
    {
        $notification = ProposalNotification::where('uuid', $uuid)->where('user_id', Auth::id())->first();
        if ($notification) { $notificationService->markAsRead($notification); $this->refreshCount(); }
    }

    public function markAllAsRead(NotificationService $notificationService)
    {
        $notificationService->markAllAsRead(Auth::user());
        $this->refreshCount();
        $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.all_read')]);
    }

    public function goToNotification(string $uuid)
    {
        $notification = ProposalNotification::where('uuid', $uuid)->where('user_id', Auth::id())->first();
        if ($notification) {
            $notification->markAsRead();
            if ($notification->action_url) return redirect($notification->action_url);
        }
    }

    public function getNotificationsProperty()
    {
        return app(NotificationService::class)->getRecentNotifications(Auth::user(), 10);
    }

    public function render()
    {
        return view('livewire.decisions.components.notification-bell', ['notifications' => $this->notifications]);
    }
}
