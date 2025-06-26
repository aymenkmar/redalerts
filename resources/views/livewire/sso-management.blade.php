<div class="p-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('main-dashboard') }}" wire:navigate
           class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-200 shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Dashboard
        </a>
    </div>

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">SSO User Management</h2>
        <button wire:click="toggleAddForm"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md transition duration-200">
            @if($showAddForm)
                Cancel
            @else
                Add New SSO User/Domain
            @endif
        </button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Add/Edit Form -->
    @if($showAddForm)
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">
                {{ $editingId ? 'Edit SSO Setting' : 'Add New SSO Setting' }}
            </h3>

            <form wire:submit.prevent="save" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select wire:model="type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="email">Specific Email</option>
                            <option value="domain">Entire Domain</option>
                        </select>
                        @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $type === 'email' ? 'Email Address' : 'Domain Name' }}
                        </label>
                        <input type="text"
                               wire:model="value"
                               placeholder="{{ $type === 'email' ? 'user@example.com' : 'example.com' }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('value') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                    <input type="text"
                           wire:model="description"
                           placeholder="e.g., Admin user, Company domain, etc."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex space-x-3">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition duration-200">
                        {{ $editingId ? 'Update' : 'Add' }} SSO Setting
                    </button>
                    <button type="button"
                            wire:click="resetForm"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition duration-200">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- SSO Settings List -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Current SSO Settings</h3>
        </div>

        @if($settings->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($settings as $setting)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $setting->type === 'email' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ ucfirst($setting->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $setting->value }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $setting->description ?: '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button wire:click="toggleStatus({{ $setting->id }})"
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full transition duration-200
                                                {{ $setting->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                        {{ $setting->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <button wire:click="edit({{ $setting->id }})"
                                            class="text-blue-600 hover:text-blue-900 transition duration-200">
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $setting->id }})"
                                            onclick="return confirm('Are you sure you want to delete this SSO setting?')"
                                            class="text-red-600 hover:text-red-900 transition duration-200">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $settings->links() }}
            </div>
        @else
            <div class="px-6 py-8 text-center text-gray-500">
                <p>No SSO settings configured yet.</p>
                <p class="text-sm mt-1">Click "Add New SSO User/Domain" to get started.</p>
            </div>
        @endif
    </div>
</div>
