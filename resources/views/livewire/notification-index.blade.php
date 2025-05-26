<div class="min-h-screen bg-gray-50" x-data="{ highlightId: {{ $highlightId ?? 'null' }} }" x-init="
    if (highlightId) {
        setTimeout(() => {
            const element = document.getElementById('notification-' + highlightId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                element.classList.add('ring-2', 'ring-red-500', 'ring-opacity-50');
                setTimeout(() => {
                    element.classList.remove('ring-2', 'ring-red-500', 'ring-opacity-50');
                }, 3000);
            }
        }, 100);
    }
">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('main-dashboard') }}" wire:navigate class="text-red-600 hover:text-red-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800">Notifications</h1>
                </div>

                <!-- Profile Dropdown -->
                <div class="flex items-center space-x-4">
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                            <div class="h-8 w-8 rounded-full bg-red-600 flex items-center justify-center text-white font-semibold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="{{ route('profile') }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-6 py-8">
        <!-- Filter Tabs -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button wire:click="setFilter('all')"
                            class="py-2 px-1 border-b-2 font-medium text-sm {{ $filter === 'all' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        All Notifications
                    </button>
                    <button wire:click="setFilter('unread')"
                            class="py-2 px-1 border-b-2 font-medium text-sm {{ $filter === 'unread' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Unread
                    </button>
                    <button wire:click="setFilter('read')"
                            class="py-2 px-1 border-b-2 font-medium text-sm {{ $filter === 'read' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Read
                    </button>
                </nav>
            </div>
        </div>

        <!-- Actions -->
        @if($notifications->total() > 0)
        <div class="mb-6 flex justify-between items-center">
            <p class="text-sm text-gray-600">{{ $notifications->total() }} notifications</p>
            <button wire:click="markAllAsRead" class="text-sm text-red-600 hover:text-red-800 font-medium">
                Mark all as read
            </button>
        </div>
        @endif

        <!-- Notifications List -->
        <div class="space-y-4">
            @if($notifications->count() > 0)
                @foreach($notifications as $notification)
                <div id="notification-{{ $notification->id }}"
                     class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-all duration-200 {{ $notification->is_read ? 'opacity-75' : '' }} {{ $highlightId == $notification->id ? 'bg-red-50 border-red-200' : '' }}"
                     wire:click="markAsRead({{ $notification->id }})">
                    <div class="flex items-start space-x-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-1">
                            @if($notification->type === 'website_down' || $notification->type === 'website_still_down')
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                    </svg>
                                </div>
                            @elseif($notification->type === 'website_up')
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            @elseif($notification->type === 'ssl_expiry')
                                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                            @elseif($notification->type === 'domain_expiry')
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m-9 9a9 9 0 919-9" />
                                    </svg>
                                </div>
                            @else
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                    <svg class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">{{ $notification->title }}</h3>
                                <div class="flex items-center space-x-2">
                                    @if(!$notification->is_read)
                                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                    @endif
                                    <span class="text-sm text-gray-500">{{ $notification->time_ago }}</span>
                                </div>
                            </div>
                            <p class="text-gray-600 mt-2">{{ $notification->message }}</p>

                            @if($notification->website)
                            <div class="mt-3 flex items-center space-x-4 text-sm text-gray-500">
                                <span>Website: {{ $notification->website->name }}</span>
                                @if($notification->websiteUrl)
                                <span>URL: {{ $notification->websiteUrl->url }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No notifications</h3>
                    <p class="mt-1 text-sm text-gray-500">You're all caught up!</p>
                </div>
            @endif
        </div>
    </div>
</div>
