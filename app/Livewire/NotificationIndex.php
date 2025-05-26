<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationIndex extends Component
{
    use WithPagination;

    public $filter = 'all'; // all, unread, read
    public $highlightId = null; // ID of notification to highlight

    public function mount()
    {
        // Get highlight parameter from URL
        $this->highlightId = request()->get('highlight');
    }

    public function markAsRead($notificationId)
    {
        if (Auth::check()) {
            $notificationService = new NotificationService();
            $notificationService->markAsRead($notificationId, Auth::id());
        }
    }

    public function markAllAsRead()
    {
        if (Auth::check()) {
            $notificationService = new NotificationService();
            $notificationService->markAllAsRead(Auth::id());
        }
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function render()
    {
        $notifications = collect();

        if (Auth::check()) {
            $query = \App\Models\Notification::forUser(Auth::id())
                ->with(['website', 'websiteUrl'])
                ->latest();

            if ($this->filter === 'unread') {
                $query->unread();
            } elseif ($this->filter === 'read') {
                $query->read();
            }

            $notifications = $query->paginate(20);
        }

        return view('livewire.notification-index', [
            'notifications' => $notifications,
            'highlightId' => $this->highlightId,
        ])->layout('layouts.main');
    }
}
