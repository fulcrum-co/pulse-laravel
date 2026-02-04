<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        {{-- Logo/Header --}}
        <div class="text-center mb-8">
            @if($organization && $organization->logo_url)
                <img src="{{ $organization->logo_url }}" alt="{{ $organization->org_name }}" class="h-16 mx-auto mb-4">
            @else
                <div class="w-16 h-16 bg-gradient-to-br from-pulse-orange-500 to-pulse-orange-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <x-icon name="user-plus" class="w-8 h-8 text-white" />
                </div>
            @endif
            <h1 class="text-2xl font-bold text-gray-900">Create Your Account</h1>
            <p class="text-gray-600 mt-2">
                @if($organization)
                    Join {{ $organization->org_name }} to access resources and courses.
                @else
                    Sign up to access our resource library.
                @endif
            </p>
        </div>

        {{-- Registration Form --}}
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <form wire:submit="register" class="space-y-5">
                {{-- Name Fields --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="firstName"
                            wire:model="firstName"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="John"
                        >
                        @error('firstName')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="lastName"
                            wire:model="lastName"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="Smith"
                        >
                        @error('lastName')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        wire:model="email"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        placeholder="john@example.com"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        wire:model="password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        placeholder="Min. 8 characters"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="passwordConfirmation" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="passwordConfirmation"
                        wire:model="passwordConfirmation"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        placeholder="Repeat your password"
                    >
                    @error('passwordConfirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Terms --}}
                <div class="flex items-start gap-3">
                    <input
                        type="checkbox"
                        id="agreeToTerms"
                        wire:model="agreeToTerms"
                        class="mt-1 h-4 w-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500"
                    >
                    <label for="agreeToTerms" class="text-sm text-gray-600">
                        I agree to the <a href="#" class="text-pulse-orange-500 hover:underline">Terms of Service</a>
                        and <a href="#" class="text-pulse-orange-500 hover:underline">Privacy Policy</a>
                    </label>
                </div>
                @error('agreeToTerms')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full px-6 py-3 bg-pulse-orange-500 text-white font-semibold rounded-lg hover:bg-pulse-orange-600 focus:ring-2 focus:ring-offset-2 focus:ring-pulse-orange-500 transition-colors"
                >
                    <span wire:loading.remove wire:target="register">Create Account</span>
                    <span wire:loading wire:target="register" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating account...
                    </span>
                </button>
            </form>

            {{-- Divider --}}
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">Already have an account?</span>
                </div>
            </div>

            {{-- Login Link --}}
            <a
                href="{{ route('login') }}"
                class="block w-full px-6 py-3 text-center border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
            >
                Sign In
            </a>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-gray-500 mt-6">
            By creating an account, you'll be able to track your progress,
            save your favorite resources, and receive personalized recommendations.
        </p>
    </div>
</div>
