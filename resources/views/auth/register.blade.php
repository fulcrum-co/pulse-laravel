<x-layouts.app title="Register">
    <div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-pulse-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
                    <p class="text-gray-600 mt-2">Start supporting student wellness today</p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="/register" class="space-y-6">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-500 focus:border-pulse-500 transition">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-500 focus:border-pulse-500 transition">
                    </div>

                    <div>
                        <label for="organization" class="block text-sm font-medium text-gray-700 mb-2">Organization name</label>
                        <input type="text" id="organization" name="organization" value="{{ old('organization') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-500 focus:border-pulse-500 transition"
                            placeholder="School or District name">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-500 focus:border-pulse-500 transition">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-500 focus:border-pulse-500 transition">
                    </div>

                    <div class="flex items-start">
                        <input type="checkbox" name="terms" required
                            class="w-4 h-4 text-pulse-600 border-gray-300 rounded focus:ring-pulse-500 mt-1">
                        <span class="ml-2 text-sm text-gray-600">
                            I agree to the <a href="#" class="text-pulse-600 hover:text-pulse-700">Terms of Service</a>
                            and <a href="#" class="text-pulse-600 hover:text-pulse-700">Privacy Policy</a>
                        </span>
                    </div>

                    <button type="submit"
                        class="w-full bg-pulse-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-pulse-700 focus:ring-4 focus:ring-pulse-200 transition">
                        Create account
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Already have an account?
                        <a href="/login" class="text-pulse-600 hover:text-pulse-700 font-medium">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
