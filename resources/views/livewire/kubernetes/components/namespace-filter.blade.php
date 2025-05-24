<div class="relative">
    <button
        wire:click="toggleNamespaceFilter"
        class="flex items-center space-x-2 bg-white rounded-md shadow px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 focus:outline-none"
    >
        <span>Namespace:</span>
        <span class="font-medium">
            @if(in_array('all', $selectedNamespaces))
                All namespaces
            @else
                {{ count($selectedNamespaces) }} selected
            @endif
        </span>
        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    @if($showNamespaceFilter)
    <div class="absolute left-0 mt-2 w-64 bg-white border border-gray-200 rounded shadow-lg" style="z-index: 45;">
        <div class="p-2 border-b border-gray-200">
            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="all-namespaces"
                    class="h-4 w-4 text-red-600 rounded border-gray-300 focus:ring-red-500"
                    wire:click="toggleNamespace('all')"
                    {{ in_array('all', $selectedNamespaces) ? 'checked' : '' }}
                >
                <label for="all-namespaces" class="ml-2 text-sm text-gray-700">All namespaces</label>
            </div>
        </div>
        <div class="max-h-60 overflow-y-auto p-2">
            @foreach($namespaces as $namespace)
            <div class="flex items-center mb-1">
                <input
                    type="checkbox"
                    id="namespace-{{ $namespace }}"
                    class="h-4 w-4 text-red-600 rounded border-gray-300 focus:ring-red-500"
                    wire:click="toggleNamespace('{{ $namespace }}')"
                    {{ in_array($namespace, $selectedNamespaces) && !in_array('all', $selectedNamespaces) ? 'checked' : '' }}
                    {{ in_array('all', $selectedNamespaces) ? 'disabled' : '' }}
                >
                <label for="namespace-{{ $namespace }}" class="ml-2 text-sm text-gray-700">{{ $namespace }}</label>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
