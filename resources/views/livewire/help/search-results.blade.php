<div>
    <!-- Header -->
    <div class="mb-8">
        <nav class="mb-4">
            <a href="{{ route('help.index') }}" class="text-sm text-gray-500 hover:text-purple-600">← @term('back_label') @term('help_center_label')</a>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">@term('search_results_label')</h1>
        @if($search)
        <p class="mt-1 text-gray-600">@term('showing_results_for_label') "{{ $search }}"</p>
        @endif
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap items-center gap-4">
        <div class="flex-1 min-w-64">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ app(\App\Services\TerminologyService::class)->get('search_articles_placeholder') }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            >
        </div>
        <div>
            <select
                wire:model.live="category"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
            >
                <option value="">@term('all_categories_label')</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Results -->
    @if($articles->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">@term('no_results_found_label')</h3>
        <p class="mt-1 text-gray-500">@term('adjust_search_label')</p>
        <a href="{{ route('help.index') }}" class="mt-4 inline-flex items-center text-purple-600 hover:text-purple-700 font-medium">
            @term('browse_help_center_label') →
        </a>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
        @foreach($articles as $article)
        <a href="{{ route('help.article', $article->slug) }}" class="block p-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-start gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-medium text-gray-900 hover:text-purple-600">{{ $article->title }}</h3>
                        @if($article->is_featured)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                            Featured
                        </span>
                        @endif
                    </div>
                    @if($article->category)
                    <span class="text-xs text-purple-600">{{ $article->category->name }}</span>
                    @endif
                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $article->excerpt }}</p>
                    <div class="mt-2 flex items-center gap-4 text-xs text-gray-400">
                        <span>{{ $article->getReadingTimeMinutes() }} @term('min_read_label')</span>
                        <span>{{ number_format($article->view_count) }} @term('views_label')</span>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $articles->links() }}
    </div>
    @endif

    <!-- Contact Support -->
    <div class="mt-8 p-6 bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl text-center">
        <p class="text-gray-700">@term('cant_find_label')</p>
        <button
            @click="$dispatch('open-support-modal', { context: 'help-search' })"
            class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            @term('contact_support_label')
        </button>
    </div>
</div>
