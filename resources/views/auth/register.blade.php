<x-layouts.app title="Register">
    <div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-pulse-orange-500 mb-2">Pulse</h1>
                    <h2 class="text-xl font-semibold text-gray-900">Create your account</h2>
                    <p class="text-gray-600 mt-1 text-sm">Start supporting learner wellness today</p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="/register" class="space-y-5">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 transition text-sm"
                            placeholder="John Smith">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 transition text-sm"
                            placeholder="you@organization.edu">
                    </div>

                    <div>
                        <label for="organization" class="block text-sm font-medium text-gray-700 mb-1.5">Organization name</label>
                        <input type="text" id="organization" name="organization" value="{{ old('organization') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 transition text-sm"
                            placeholder="Lincoln High Organization">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 transition text-sm"
                            placeholder="Create a strong password">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 transition text-sm"
                            placeholder="Confirm your password">
                    </div>

                    <div class="flex items-start">
                        <input type="checkbox" name="terms" required
                            class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 mt-0.5">
                        <span class="ml-2 text-sm text-gray-600">
                            I agree to the <a href="#" class="text-pulse-orange-500 hover:text-pulse-orange-600 font-medium">Terms of Service</a>
                            and <a href="#" class="text-pulse-orange-500 hover:text-pulse-orange-600 font-medium">Privacy Policy</a>
                        </span>
                    </div>

                    <button type="submit"
                        class="w-full bg-pulse-orange-500 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-pulse-orange-600 focus:ring-4 focus:ring-pulse-orange-100 transition-colors">
                        Create account
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a href="/login" class="text-pulse-orange-500 hover:text-pulse-orange-600 font-medium">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
