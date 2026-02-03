<x-layouts.app title="{{ app(\App\Services\TerminologyService::class)->get('auth_register_title_label') }}">
    <div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-pulse-orange-500 mb-2">@term('app_name_label')</h1>
                    <h2 class="text-xl font-semibold text-gray-900">@term('auth_create_account_label')</h2>
                    <p class="text-gray-600 mt-1 text-sm">@term('auth_register_prompt_label')</p>
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
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">@term('auth_full_name_label')</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 transition text-sm"
                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('auth_full_name_placeholder') }}">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">@term('auth_email_label')</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 transition text-sm"
                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('auth_email_placeholder') }}">
                    </div>

                    <div>
                        <label for="organization" class="block text-sm font-medium text-gray-700 mb-1.5">@term('auth_organization_name_label')</label>
                        <input type="text" id="organization" name="organization" value="{{ old('organization') }}" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 transition text-sm"
                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('auth_organization_placeholder') }}">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">@term('auth_password_label')</label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 transition text-sm"
                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('auth_password_create_placeholder') }}">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">@term('auth_confirm_password_label')</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 transition text-sm"
                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('auth_confirm_password_placeholder') }}">
                    </div>

                    <div class="flex items-start">
                        <input type="checkbox" name="terms" required
                            class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 mt-0.5">
                        <span class="ml-2 text-sm text-gray-600">
                            {!! app(\App\Services\TerminologyService::class)->get('auth_terms_label') !!}
                        </span>
                    </div>

                    <button type="submit"
                        class="w-full bg-pulse-orange-500 text-white py-2.5 px-4 rounded-lg font-medium hover:bg-pulse-orange-600 focus:ring-4 focus:ring-pulse-orange-100 transition-colors">
                        @term('auth_create_account_action_label')
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600">
                        @term('auth_already_account_label')
                        <a href="/login" class="text-pulse-orange-500 hover:text-pulse-orange-600 font-medium">@term('auth_sign_in_action_label')</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
