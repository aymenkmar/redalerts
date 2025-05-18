<div class="relative">
    <div class="flex items-center bg-white rounded-md shadow px-3 py-2">
        <svg class="h-4 w-4 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input 
            type="text" 
            wire:model.live.debounce.300ms="searchTerm" 
            placeholder="Search..." 
            class="text-sm text-gray-700 border-0 focus:ring-0 focus:outline-none w-full"
        >
        @if($searchTerm)
        <button wire:click="$set('searchTerm', '')" class="text-gray-400 hover:text-gray-600">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        @endif
    </div>
</div>
