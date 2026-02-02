{{-- Help Widget - Bottom right FAB with expandable help panel --}}
<div
    x-data="helpWidget()"
    x-init="init()"
    class="fixed bottom-4 right-4 z-40"
>
    {{-- Expandable Panel --}}
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        @click.outside="isOpen = false"
        class="absolute bottom-16 right-0 w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden"
    >
        {{-- Header --}}
        <div class="bg-gradient-to-r from-pulse-orange-500 to-orange-600 px-5 py-4 text-white">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Help Center</h3>
                <button @click="isOpen = false" class="p-1 hover:bg-white/20 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Search --}}
            <div class="relative">
                <svg class="w-4 h-4 text-white/60 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    type="text"
                    x-model="searchQuery"
                    @input.debounce.300ms="searchArticles()"
                    @keydown.enter="goToSearch()"
                    placeholder="Search help articles..."
                    class="w-full pl-10 pr-4 py-2.5 bg-white/20 text-white placeholder-white/60 rounded-lg border border-white/30 focus:border-white focus:outline-none focus:ring-2 focus:ring-white/30 text-sm"
                >
            </div>
        </div>

        {{-- Content --}}
        <div class="max-h-[400px] overflow-y-auto">
            {{-- Search Results --}}
            <template x-if="searchQuery && searchResults.length > 0">
                <div class="p-4 border-b border-gray-100">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Search Results</h4>
                    <div class="space-y-2">
                        <template x-for="article in searchResults" :key="article.id">
                            <a
                                :href="`/help/article/${article.slug}`"
                                class="flex items-start gap-3 p-2 -mx-2 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                <div class="w-8 h-8 rounded-lg bg-pulse-orange-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-pulse-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-sm font-medium text-gray-900 truncate" x-text="article.title"></h5>
                                    <p class="text-xs text-gray-500 line-clamp-1" x-text="article.excerpt"></p>
                                </div>
                            </a>
                        </template>
                    </div>
                    <a
                        :href="`/help/search?q=${encodeURIComponent(searchQuery)}`"
                        class="block mt-3 text-center text-sm text-pulse-orange-600 hover:text-pulse-orange-700 font-medium"
                    >
                        View all results &rarr;
                    </a>
                </div>
            </template>

            {{-- No Search Results --}}
            <template x-if="searchQuery && searchResults.length === 0 && !isSearching">
                <div class="p-4 text-center border-b border-gray-100">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-gray-500">No articles found</p>
                    <a
                        :href="`/help/search?q=${encodeURIComponent(searchQuery)}`"
                        class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                    >
                        Try advanced search
                    </a>
                </div>
            </template>

            {{-- Quick Actions (shown when not searching) --}}
            <template x-if="!searchQuery">
                <div>
                    {{-- Featured Articles --}}
                    <div class="p-4 border-b border-gray-100" x-show="featuredArticles.length > 0">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Popular Articles</h4>
                        <div class="space-y-2">
                            <template x-for="article in featuredArticles" :key="article.id">
                                <a
                                    :href="`/help/article/${article.slug}`"
                                    class="flex items-center gap-3 p-2 -mx-2 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-sm font-medium text-gray-900 truncate" x-text="article.title"></h5>
                                        <p class="text-xs text-gray-500" x-text="article.category?.name || 'General'"></p>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </template>
                        </div>
                    </div>

                    {{-- Categories --}}
                    <div class="p-4 border-b border-gray-100" x-show="categories.length > 0">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Browse by Topic</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="category in categories" :key="category.id">
                                <a
                                    :href="`/help/category/${category.slug}`"
                                    class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 hover:border-pulse-orange-300 hover:bg-pulse-orange-50 transition-colors"
                                >
                                    <span class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-gray-600">
                                        <svg x-show="category.icon === 'academic-cap'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                                            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                        </svg>
                                        <svg x-show="category.icon === 'cog-6-tooth' || !category.icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <svg x-show="category.icon === 'chart-bar'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                        <svg x-show="category.icon === 'users'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        <svg x-show="category.icon === 'bell'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </span>
                                    <span class="text-xs font-medium text-gray-700 truncate" x-text="category.name"></span>
                                </a>
                            </template>
                        </div>
                    </div>

                    {{-- Quick Links --}}
                    <div class="p-4">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Links</h4>
                        <div class="space-y-1">
                            <a href="/help" class="flex items-center gap-3 p-2 -mx-2 rounded-lg hover:bg-gray-50 transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900">Help Center</span>
                                    <p class="text-xs text-gray-500">Browse all articles</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>

                            <button
                                @click="startPageTour()"
                                class="w-full flex items-center gap-3 p-2 -mx-2 rounded-lg hover:bg-gray-50 transition-colors text-left"
                            >
                                <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900">Page Tour</span>
                                    <p class="text-xs text-gray-500">Guided walkthrough</p>
                                </div>
                            </button>

                            <button
                                @click="openContactSupport()"
                                class="w-full flex items-center gap-3 p-2 -mx-2 rounded-lg hover:bg-gray-50 transition-colors text-left"
                            >
                                <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900">Contact Support</span>
                                    <p class="text-xs text-gray-500">Send us a message</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- FAB Button --}}
    <button
        @click="isOpen = !isOpen"
        :class="isOpen ? 'bg-gray-700 hover:bg-gray-800' : 'bg-pulse-orange-500 hover:bg-pulse-orange-600'"
        class="w-14 h-14 text-white rounded-full shadow-lg hover:shadow-xl transition-all flex items-center justify-center"
        :title="isOpen ? 'Close help' : 'Need help?'"
    >
        {{-- Question mark icon (when closed) --}}
        <svg x-show="!isOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{-- X icon (when open) --}}
        <svg x-show="isOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<script>
function helpWidget() {
    return {
        isOpen: false,
        searchQuery: '',
        searchResults: [],
        isSearching: false,
        featuredArticles: [],
        categories: [],

        init() {
            // Load featured articles and categories on init
            this.loadFeaturedContent();
        },

        async loadFeaturedContent() {
            try {
                // Fetch featured articles
                const articlesResponse = await fetch('/api/help/featured-articles');
                if (articlesResponse.ok) {
                    const data = await articlesResponse.json();
                    this.featuredArticles = data.articles || [];
                }
            } catch (e) {
                // Use fallback static data if API fails
                this.featuredArticles = [
                    { id: 1, title: 'Getting Started with Pulse', slug: 'getting-started', excerpt: 'Learn the basics of using Pulse', category: { name: 'Getting Started' } },
                    { id: 2, title: 'Creating Your First Survey', slug: 'creating-surveys', excerpt: 'Step-by-step guide to surveys', category: { name: 'Surveys' } },
                    { id: 3, title: 'Understanding Dashboard Widgets', slug: 'dashboard-widgets', excerpt: 'Customize your dashboard view', category: { name: 'Dashboard' } },
                ];
            }

            try {
                // Fetch categories
                const categoriesResponse = await fetch('/api/help/categories');
                if (categoriesResponse.ok) {
                    const data = await categoriesResponse.json();
                    this.categories = data.categories || [];
                }
            } catch (e) {
                // Use fallback static data if API fails
                this.categories = [
                    { id: 1, name: 'Getting Started', slug: 'getting-started', icon: 'academic-cap' },
                    { id: 2, name: 'Surveys', slug: 'surveys', icon: 'chart-bar' },
                    { id: 3, name: 'Contacts', slug: 'contacts', icon: 'users' },
                    { id: 4, name: 'Alerts', slug: 'alerts', icon: 'bell' },
                ];
            }
        },

        async searchArticles() {
            if (!this.searchQuery || this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            this.isSearching = true;

            try {
                const response = await fetch(`/api/help/search?q=${encodeURIComponent(this.searchQuery)}`);
                if (response.ok) {
                    const data = await response.json();
                    this.searchResults = data.articles || [];
                }
            } catch (e) {
                // Fallback: filter featured articles locally
                const query = this.searchQuery.toLowerCase();
                this.searchResults = this.featuredArticles.filter(a =>
                    a.title.toLowerCase().includes(query) ||
                    (a.excerpt && a.excerpt.toLowerCase().includes(query))
                );
            }

            this.isSearching = false;
        },

        goToSearch() {
            if (this.searchQuery) {
                window.location.href = `/help/search?q=${encodeURIComponent(this.searchQuery)}`;
            }
        },

        startPageTour() {
            this.isOpen = false;
            window.dispatchEvent(new CustomEvent('start-page-help', { detail: {} }));
        },

        openContactSupport() {
            this.isOpen = false;
            window.dispatchEvent(new CustomEvent('open-support-modal', { detail: { context: 'help-widget' } }));
        }
    };
}
</script>
