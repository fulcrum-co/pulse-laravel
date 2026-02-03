<x-layouts.app title="{{ app(\App\Services\TerminologyService::class)->get('unsubscribed_title_label') }}">
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-xl shadow-sm p-8">
                {{-- Success icon --}}
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-2xl font-semibold text-gray-900 mb-2">@term('unsubscribed_success_label')</h1>

                <p class="text-gray-600 mb-6">
                    @term('unsubscribed_body_primary_label')
                </p>

                <p class="text-sm text-gray-500 mb-6">
                    @term('unsubscribed_body_secondary_label')
                </p>

                <div class="space-y-3">
                    <a href="/alerts" class="inline-flex items-center justify-center w-full px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                        @term('view_notification_center_label')
                    </a>

                    @auth
                        <a href="/settings/notifications" class="inline-flex items-center justify-center w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            @term('manage_notification_prefs_label')
                        </a>
                    @endauth
                </div>
            </div>

            <p class="mt-6 text-sm text-gray-500">
                @term('changed_mind_label')
                @auth
                    <a href="/settings/notifications" class="text-pulse-orange-600 hover:text-pulse-orange-700 font-medium">@term('reenable_digest_label')</a>
                @else
                    <a href="/login" class="text-pulse-orange-600 hover:text-pulse-orange-700 font-medium">@term('auth_sign_in_action_label')</a> @term('login_to_update_prefs_label')
                @endauth
            </p>
        </div>
    </div>
</x-layouts.app>
