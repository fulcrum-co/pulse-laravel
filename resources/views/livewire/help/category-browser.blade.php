<div>
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center gap-2 text-sm">
            <li>
                <a href="{{ route('help.index') }}" class="text-gray-500 hover:text-purple-600">@term('help_center_label')</a>
            </li>
            <li class="text-gray-400">/</li>
            <li class="text-gray-900 font-medium">{{ $category->name }}</li>
        </ol>
    </nav>

    <!-- Category Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                <x-help-icon :name="$category->icon" class="w-8 h-8" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $category->name }}</h1>
                @if($category->description)
                <p class="mt-1 text-gray-600">{{ $category->description }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Subcategories (if any) -->
    @if($subcategories->isNotEmpty())
    <section class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('subcategories_label')</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($subcategories as $sub)
            <a href="{{ route('help.category', $sub->slug) }}" class="flex items-center gap-3 p-4 bg-white rounded-lg border border-gray-200 hover:shadow-md hover:border-purple-300 transition-all">
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                    <x-help-icon :name="$sub->icon" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="font-medium text-gray-900">{{ $sub->name }}</h3>
                    <p class="text-xs text-gray-500">{{ $sub->published_articles_count }} @term('articles_label')</p>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Articles -->
    <section>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('articles_in_category_label')</h2>

        @if($articles->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">@term('no_articles_yet_label')</h3>
            <p class="mt-1 text-gray-500">@term('check_back_soon_label')</p>
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @foreach($articles as $article)
            <a href="{{ route('help.article', $article->slug) }}" class="flex items-start gap-4 p-4 hover:bg-gray-50 transition-colors">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-medium text-gray-900 hover:text-purple-600">{{ $article->title }}</h3>
                        @if($article->is_featured)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                            @term('featured_label')
                        </span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $article->excerpt }}</p>
                    <div class="mt-2 flex items-center gap-4 text-xs text-gray-400">
                        <span>{{ $article->getReadingTimeMinutes() }} @term('min_read_label')</span>
                        <span>{{ number_format($article->view_count) }} @term('views_label')</span>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $articles->links() }}
        </div>
        @endif
    </section>
</div>
