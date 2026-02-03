<div class="max-w-4xl mx-auto">
    {{-- Admin Edit Bar --}}
    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->role === 'admin'))
    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-2 text-amber-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium">@term('admin_view_label')</span>
            <span class="text-sm text-amber-600">·</span>
            <span class="text-sm text-amber-600">@term('article_id_label'): {{ $article->id }}</span>
        </div>
        <div class="flex items-center gap-2">
            {{-- Share Dropdown --}}
            <div x-data="{ open: false, copied: false }" class="relative">
                <button
                    @click="open = !open"
                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-amber-300 hover:bg-amber-50 text-amber-700 text-sm font-medium rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    @term('share_label')
                </button>
                <div
                    x-show="open"
                    @click.outside="open = false"
                    x-transition
                    class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                >
                    <div class="px-3 py-2 border-b border-gray-100">
                        <p class="text-xs font-medium text-gray-500 uppercase">@term('share_article_label')</p>
                    </div>
                    <button
                        @click="
                            navigator.clipboard.writeText('{{ route('help.article', $article->slug) }}');
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        "
                        class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                        </svg>
                        <span x-text="copied ? @js(app(\App\Services\TerminologyService::class)->get('copied_label')) : @js(app(\App\Services\TerminologyService::class)->get('copy_link_label'))"></span>
                    </button>
                    <a
                        href="mailto:?subject={{ urlencode($article->title) }}&body={{ urlencode('Check out this help article: ' . route('help.article', $article->slug)) }}"
                        class="w-full flex items-center gap-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        @term('email_link_label')
                    </a>
                    <div class="px-3 py-2 border-t border-gray-100 mt-1">
                        <p class="text-xs text-gray-400 truncate">{{ route('help.article', $article->slug) }}</p>
                    </div>
                </div>
            </div>
            {{-- Edit Button --}}
            <a
                href="{{ route('admin.help-articles') }}?edit={{ $article->id }}"
                class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                @term('edit_action')
            </a>
        </div>
    </div>
    @endif

    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center gap-2 text-sm">
            <li>
                <a href="{{ route('help.index') }}" class="text-gray-500 hover:text-purple-600">@term('help_center_label')</a>
            </li>
            @if($article->category)
            <li class="text-gray-400">/</li>
            <li>
                <a href="{{ route('help.category', $article->category->slug) }}" class="text-gray-500 hover:text-purple-600">
                    {{ $article->category->name }}
                </a>
            </li>
            @endif
            <li class="text-gray-400">/</li>
            <li class="text-gray-900 font-medium truncate">{{ $article->title }}</li>
        </ol>
    </nav>

    <!-- Article Content -->
    <article class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-8">
            <!-- Header -->
            <header class="mb-8">
                @if($article->is_featured)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 mb-3">
                    @term('featured_label')
                </span>
                @endif
                <h1 class="text-3xl font-bold text-gray-900">{{ $article->title }}</h1>
                <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $article->getReadingTimeMinutes() }} @term('min_read_label')
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ number_format($article->view_count) }} @term('views_label')
                    </span>
                    @if($article->published_at)
                    <span>@term('updated_label') {{ $article->published_at->diffForHumans() }}</span>
                    @endif
                </div>
            </header>

            <!-- Video (if available) -->
            @if($article->video_url)
            <div class="mb-8 aspect-video rounded-lg overflow-hidden bg-gray-100">
                <iframe
                    src="{{ $article->video_url }}"
                    class="w-full h-full"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            </div>
            @endif

            <!-- Content -->
            <div class="prose prose-purple max-w-none">
                {!! \Illuminate\Support\Str::markdown($article->content) !!}
            </div>
        </div>

        <!-- Feedback Section -->
        <div class="border-t border-gray-200 px-8 py-6 bg-gray-50">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">@term('was_this_helpful_label')</p>
                <div class="flex items-center gap-3">
                    <button
                        wire:click="markHelpful(true)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                        </svg>
                        @term('yes_label') ({{ $article->helpful_count }})
                    </button>
                    <button
                        wire:click="markHelpful(false)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-red-50 hover:border-red-300 hover:text-red-700 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5" />
                        </svg>
                        @term('no_label') ({{ $article->not_helpful_count }})
                    </button>
                </div>
            </div>
        </div>
    </article>

    <!-- Related Articles -->
    @if($relatedArticles->isNotEmpty())
    <section class="mt-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('related_articles_label')</h2>
        <div class="grid gap-4 md:grid-cols-3">
            @foreach($relatedArticles as $related)
            <a href="{{ route('help.article', $related->slug) }}" class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md hover:border-purple-300 transition-all">
                <h3 class="font-medium text-gray-900 hover:text-purple-600">{{ $related->title }}</h3>
                <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $related->excerpt }}</p>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- Contact Support -->
    <div class="mt-8 p-6 bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl text-center">
        <p class="text-gray-700">@term('still_need_help_label')</p>
        <button
            @click="$dispatch('open-support-modal', { context: 'help-article' })"
            class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            @term('contact_support_label')
        </button>
    </div>
</div>
