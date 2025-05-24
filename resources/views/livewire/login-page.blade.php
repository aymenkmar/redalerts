
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-6 sm:p-8">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-red-600">RedAlerts</h2>
            <p class="text-sm text-gray-600 mt-1">Login to access your dashboard</p>
        </div>

        @if ($errorMessage)
            <div class="bg-red-50 text-red-600 p-4 rounded mb-6">
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit.prevent="login" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    type="email"
                    wire:model="email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    required
                />
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input
                    type="{{ $showPassword ? 'text' : 'password' }}"
                    wire:model="password"
                    class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    required
                />
                <button
                    type="button"
                    wire:click="togglePasswordVisibility"
                    class="absolute right-3 top-9 text-gray-600 hover:text-red-600"
                    tabindex="-1"
                >
                    @if($showPassword)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                            <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                    @endif
                </button>
                @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="remember"
                    wire:model="remember"
                    class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
                />
                <label for="remember" class="ml-2 block text-sm text-gray-700">
                    Remember me
                </label>
            </div>

            <button
                type="submit"
                class="w-full bg-red-600 text-white py-2 rounded-md hover:bg-red-700 transition duration-200"
            >
                Log In
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="/" class="text-sm text-red-600 hover:text-red-800">
                Back to Home
            </a>
        </div>
    </div>
</div>
