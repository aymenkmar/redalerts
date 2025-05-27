<div class="relative" x-data="{ open: false }" @click.away="open = false" wire:poll.30s="refreshNotifications">
    <style>
        /* Custom scrollbar styles - More visible */
        .notification-scroll {
            scrollbar-width: auto;
            scrollbar-color: #ef4444 #f1f5f9;
        }
        .notification-scroll::-webkit-scrollbar {
            width: 12px;
            background: #f8fafc;
        }
        .notification-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 6px;
            margin: 4px;
        }
        .notification-scroll::-webkit-scrollbar-thumb {
            background: #ef4444;
            border-radius: 6px;
            border: 2px solid #f8fafc;
            min-height: 30px;
        }
        .notification-scroll::-webkit-scrollbar-thumb:hover {
            background: #dc2626;
        }
        .notification-scroll::-webkit-scrollbar-thumb:active {
            background: #b91c1c;
        }
        /* Gradient overlay to indicate more content */
        .notification-container {
            position: relative;
        }
        .notification-container::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 12px;
            height: 15px;
            background: linear-gradient(transparent, rgba(255,255,255,0.9));
            pointer-events: none;
            z-index: 1;
        }
    </style>
    <!-- Notification Bell Button -->
    <button @click="open = !open; $wire.toggleDropdown()"
            class="relative p-2 text-gray-600 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 rounded-lg transition-colors duration-200">
        <!-- Bell Icon -->
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Unread Count Badge -->
        @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full min-w-[20px] h-5">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50 overflow-hidden">

        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                <div class="flex items-center space-x-2">
                    @if($unreadCount > 0)
                    <button wire:click="markAllAsRead"
                            class="text-xs text-red-600 hover:text-red-800 font-medium">
                        Mark all read
                    </button>
                    @endif
                    <button wire:click="refreshNotifications"
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="notification-container">
            <div class="notification-scroll" style="max-height: 200px; overflow-y: scroll; overflow-x: hidden; border: 1px solid #e5e7eb; border-left: none; border-right: none;">
                @if(count($notifications) > 0)
                @foreach($notifications as $index => $notification)
                <div class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors duration-150 {{ $notification->is_read ? 'opacity-75' : '' }}"
                     wire:click="goToNotification({{ $notification->id }})">
                    <div class="flex items-start space-x-3">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-1">
                            @if($notification->type === 'website_down' || $notification->type === 'website_still_down')
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                            @elseif($notification->type === 'website_up')
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            @elseif($notification->type === 'ssl_expiry')
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            @elseif($notification->type === 'domain_expiry')
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9 3-9m-9 9a9 9 0 919-9" />
                                    </svg>
                                </div>
                            @else
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $notification->title }}</p>
                                <div class="flex items-center space-x-2">
                                    @if(!$notification->is_read)
                                    <div class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></div>
                                    @endif
                                    <!-- Click indicator -->
                                    <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mt-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $notification->message }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $notification->time_ago }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
                    <p class="mt-1 text-sm text-gray-500">You're all caught up!</p>
                </div>
            @endif
            </div>
        </div>

        <!-- Footer -->
        @if(count($notifications) > 0)
        <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
            <div class="flex items-center justify-between">
                <a href="{{ route('notifications.index') }}" wire:navigate
                   class="text-sm text-red-600 hover:text-red-800 font-medium">
                    View all notifications
                </a>
                @if(count($notifications) > 5)
                <span class="text-xs text-gray-500">
                    Scroll for more ({{ count($notifications) }} total)
                </span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
