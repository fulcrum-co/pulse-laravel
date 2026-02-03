<x-layouts.app title="@term('home_label')">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-pulse-orange-500 to-pulse-orange-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    @term('home_page_hero_title')
                </h1>
                <p class="text-xl md:text-2xl text-orange-100 mb-8 max-w-3xl mx-auto">
                    @term('home_page_hero_description')
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/register" class="bg-white text-pulse-orange-600 px-8 py-3 rounded-lg font-semibold hover:bg-orange-50 transition text-lg">
                        @term('home_page_get_started_label')
                    </a>
                    <a href="#features" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white/10 transition text-lg">
                        @term('home_page_learn_more_label')
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    @term('home_page_how_it_works_title')
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    @term('home_page_how_it_works_description')
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gray-50 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-pulse-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-pulse-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">@term('home_page_feature_ai_title')</h3>
                    <p class="text-gray-600">
                        @term('home_page_feature_ai_description')
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gray-50 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">@term('home_page_feature_insights_title')</h3>
                    <p class="text-gray-600">
                        @term('home_page_feature_insights_description')
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gray-50 rounded-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-pulse-purple-600/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-pulse-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">@term('home_page_feature_intervention_title')</h3>
                    <p class="text-gray-600">
                        @term('home_page_feature_intervention_description')
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-pulse-orange-500 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">
                <div>
                    <div class="text-4xl font-bold mb-2">@term('home_page_stats_participants_supported_value')</div>
                    <div class="text-orange-100">@term('home_page_stats_participants_supported_label')</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">@term('home_page_stats_organizations_value')</div>
                    <div class="text-orange-100">@term('home_page_stats_organizations_label')</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">@term('home_page_stats_conversations_value')</div>
                    <div class="text-orange-100">@term('home_page_stats_conversations_label')</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">@term('home_page_stats_satisfaction_value')</div>
                    <div class="text-orange-100">@term('home_page_stats_satisfaction_label')</div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="py-24 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                @term('home_page_cta_title')
            </h2>
            <p class="text-xl text-gray-600 mb-8">
                @term('home_page_cta_description')
            </p>
            <a href="/register" class="inline-block bg-pulse-orange-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-pulse-orange-600 transition text-lg">
                @term('home_page_cta_action')
            </a>
        </div>
    </div>
</x-layouts.app>
