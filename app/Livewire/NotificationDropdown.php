<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationDropdown extends Component
{
    public $unreadCount = 0;
    public $notifications = [];
    public $showDropdown = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        if (Auth::check()) {
            $notificationService = new NotificationService();
            $this->unreadCount = $notificationService->getUnreadCount(Auth::id());
            $this->notifications = $notificationService->getRecentNotifications(Auth::id(), 20); // Load more for scrolling
        }
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;

        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    public function markAsRead($notificationId)
    {
        if (Auth::check()) {
            $notificationService = new NotificationService();
            $notificationService->markAsRead($notificationId, Auth::id());
            $this->loadNotifications();
        }
    }

    public function goToNotification($notificationId)
    {
        if (Auth::check()) {
            // Mark as read first
            $notificationService = new NotificationService();
            $notificationService->markAsRead($notificationId, Auth::id());

            // Redirect to notifications page with the specific notification highlighted
            return $this->redirect(route('notifications.index', ['highlight' => $notificationId]));
        }
    }

    public function markAllAsRead()
    {
        if (Auth::check()) {
            $notificationService = new NotificationService();
            $notificationService->markAllAsRead(Auth::id());
            $this->loadNotifications();
        }
    }

    public function refreshNotifications()
    {
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notification-dropdown');
    }
}
