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
            $this->notifications = $notificationService->getRecentNotifications(Auth::id(), 10);
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
