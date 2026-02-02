<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-br from-purple-700 via-purple-600 to-indigo-700 text-white -mx-8 -mt-8 px-8">
        <div class="max-w-5xl mx-auto py-16">
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Help Center</h1>
                <p class="mt-4 text-xl text-purple-200">Find answers to your questions and learn how to use Pulse</p>

                <!-- Search -->
                <div class="mt-8 max-w-xl mx-auto">
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search for articles..."
                            class="w-full pl-12 pr-4 py-4 text-gray-900 placeholder-gray-500 bg-white rounded-xl shadow-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        >
                        <svg class="absolute left-4 top-4 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    @if($search)
                        <a href="{{ route('help.search', ['q' => $search]) }}" class="mt-3 inline-block text-purple-200 hover:text-white text-sm">
                            View all search results →
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto py-12">
        <!-- Featured Articles -->
        @if($featuredArticles->isNotEmpty())
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Articles</h2>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($featuredArticles as $article)
                <a href="{{ route('help.article', $article->slug) }}" class="group bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md hover:border-purple-300 transition-all">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                            Featured
                        </span>
                        @if($article->category)
                        <span class="text-xs text-gray-500">{{ $article->category->name }}</span>
                        @endif
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">
                        {{ $article->title }}
                    </h3>
                    <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ $article->excerpt }}</p>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $article->getReadingTimeMinutes() }} min read
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Categories Grid -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Browse by Category</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($categories as $category)
                <a href="{{ route('help.category', $category->slug) }}" class="group flex items-start gap-4 p-5 bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-purple-300 transition-all">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <x-help-icon :name="$category->icon" class="w-6 h-6" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">
                            {{ $category->name }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $category->description }}</p>
                        <p class="mt-2 text-xs text-gray-400">{{ $category->published_articles_count }} articles</p>
                    </div>
                </a>
                @endforeach
            </div>
        </section>

        <!-- Popular Articles -->
        @if($popularArticles->isNotEmpty())
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Most Popular</h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y divide-gray-100">
                @foreach($popularArticles as $index => $article)
                <a href="{{ route('help.article', $article->slug) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-semibold text-sm">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-medium text-gray-900 truncate">{{ $article->title }}</h3>
                        @if($article->category)
                        <p class="text-xs text-gray-500">{{ $article->category->name }}</p>
                        @endif
                    </div>
                    <div class="flex-shrink-0 text-xs text-gray-400">
                        {{ number_format($article->view_count) }} views
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif

        <!-- Contact Section -->
        <section class="mt-12 bg-gradient-to-r from-orange-50 to-amber-50 rounded-2xl p-8 text-center">
            <h2 class="text-xl font-semibold text-gray-900">Can't find what you're looking for?</h2>
            <p class="mt-2 text-gray-600">Our support team is here to help</p>
            <button
                @click="$dispatch('open-support-modal', { context: 'help-center' })"
                class="mt-4 inline-flex items-center gap-2 px-6 py-3 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                Contact Support
            </button>
        </section>
    </div>
</div>
