@if($totalItems > 0)
<div class="flex flex-col sm:flex-row justify-between items-center mt-4 px-6 py-3 bg-white border-t border-gray-200">
    <div class="text-sm text-gray-700 mb-2 sm:mb-0">
        Showing 
        <span class="font-medium">{{ ($currentPage - 1) * $perPage + 1 }}</span>
        to 
        <span class="font-medium">{{ min($currentPage * $perPage, $totalItems) }}</span>
        of 
        <span class="font-medium">{{ $totalItems }}</span>
        results
    </div>
    
    <div class="flex space-x-1">
        <button 
            wire:click="previousPage" 
            class="px-3 py-1 rounded border {{ $currentPage <= 1 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
            {{ $currentPage <= 1 ? 'disabled' : '' }}
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        
        @php
            $maxPage = ceil($totalItems / $perPage);
            $pagesToShow = 5;
            $halfPagesToShow = floor($pagesToShow / 2);
            
            if ($maxPage <= $pagesToShow) {
                $startPage = 1;
                $endPage = $maxPage;
            } elseif ($currentPage <= $halfPagesToShow) {
                $startPage = 1;
                $endPage = $pagesToShow;
            } elseif ($currentPage >= $maxPage - $halfPagesToShow) {
                $startPage = $maxPage - $pagesToShow + 1;
                $endPage = $maxPage;
            } else {
                $startPage = $currentPage - $halfPagesToShow;
                $endPage = $currentPage + $halfPagesToShow;
            }
            
            $startPage = max(1, $startPage);
            $endPage = min($maxPage, $endPage);
        @endphp
        
        @if($startPage > 1)
            <button wire:click="goToPage(1)" class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50">
                1
            </button>
            
            @if($startPage > 2)
                <span class="px-3 py-1 text-gray-500">...</span>
            @endif
        @endif
        
        @for($i = $startPage; $i <= $endPage; $i++)
            <button 
                wire:click="goToPage({{ $i }})" 
                class="px-3 py-1 rounded border {{ $currentPage == $i ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
            >
                {{ $i }}
            </button>
        @endfor
        
        @if($endPage < $maxPage)
            @if($endPage < $maxPage - 1)
                <span class="px-3 py-1 text-gray-500">...</span>
            @endif
            
            <button wire:click="goToPage({{ $maxPage }})" class="px-3 py-1 rounded border bg-white text-gray-700 hover:bg-gray-50">
                {{ $maxPage }}
            </button>
        @endif
        
        <button 
            wire:click="nextPage" 
            class="px-3 py-1 rounded border {{ $currentPage >= $maxPage ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white text-gray-700 hover:bg-gray-50' }}"
            {{ $currentPage >= $maxPage ? 'disabled' : '' }}
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
    
    <div class="hidden sm:flex items-center space-x-2 mt-2 sm:mt-0">
        <span class="text-sm text-gray-700">Items per page:</span>
        <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm focus:ring-red-500 focus:border-red-500">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>
</div>
@endif
