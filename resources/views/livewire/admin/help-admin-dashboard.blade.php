<div class="bg-white rounded-xl border border-gray-200">
    {{-- Header --}}
    <div class="px-6 py-5 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-900">Help Center</h1>
        <p class="mt-1 text-sm text-gray-500">Manage help articles, categories, and tooltips</p>
    </div>

    {{-- Stats Row --}}
    <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap gap-6">
        <a href="{{ route('admin.help-articles') }}" class="flex items-center gap-3 hover:opacity-75 transition-opacity">
            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                <x-icon name="document-text" class="w-5 h-5 text-purple-600" />
            </div>
            <div>
                <p class="text-lg font-bold text-gray-900">{{ $this->stats['articles']['total'] }}</p>
                <p class="text-xs text-gray-500">Articles</p>
            </div>
            <div class="ml-2 flex items-center gap-1.5 text-xs">
                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded">{{ $this->stats['articles']['published'] }}</span>
                @if($this->stats['articles']['draft'] > 0)
                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded">{{ $this->stats['articles']['draft'] }}</span>
                @endif
            </div>
        </a>

        <div class="w-px h-10 bg-gray-200"></div>

        <a href="{{ route('admin.help-categories') }}" class="flex items-center gap-3 hover:opacity-75 transition-opacity">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                <x-icon name="folder" class="w-5 h-5 text-blue-600" />
            </div>
            <div>
                <p class="text-lg font-bold text-gray-900">{{ $this->stats['categories']['total'] }}</p>
                <p class="text-xs text-gray-500">Categories</p>
            </div>
        </a>

        <div class="w-px h-10 bg-gray-200"></div>

        <a href="{{ route('admin.help-hints') }}" class="flex items-center gap-3 hover:opacity-75 transition-opacity">
            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                <x-icon name="light-bulb" class="w-5 h-5 text-orange-600" />
            </div>
            <div>
                <p class="text-lg font-bold text-gray-900">{{ $this->stats['hints']['total'] }}</p>
                <p class="text-xs text-gray-500">Tooltips</p>
            </div>
            <span class="ml-2 text-xs text-gray-400">{{ $this->stats['hints']['pages'] }} pages</span>
        </a>
    </div>

    {{-- View Toggle & Content --}}
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-900">All Articles</h2>
        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="flex items-center bg-gray-100 rounded-lg p-1">
                <button wire:click="setView('list')"
                        class="p-1.5 rounded {{ $view === 'list' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }} transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <button wire:click="setView('grid')"
                        class="p-1.5 rounded {{ $view === 'grid' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }} transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </button>
                <button wire:click="setView('table')"
                        class="p-1.5 rounded {{ $view === 'table' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }} transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- List View --}}
    @if($view === 'list')
        <div class="divide-y divide-gray-100">
            @forelse($this->allArticles as $article)
                <a href="{{ route('help.article', $article->slug) }}" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors block">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $article->title }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            @if($article->category)
                                <span class="text-xs text-gray-500">{{ $article->category->name }}</span>
                                <span class="text-gray-300">·</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $article->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        @if($article->is_featured)
                            <x-icon name="star" class="w-4 h-4 text-yellow-500" />
                        @endif
                        @if($article->is_published)
                            <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded">Published</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded">Draft</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">
                    <x-icon name="document-text" class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                    <p class="text-sm">No articles yet</p>
                    <a href="{{ route('admin.help-articles') }}?action=create" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">Create your first article</a>
                </div>
            @endforelse
        </div>
    @endif

    {{-- Grid View --}}
    @if($view === 'grid')
        <div class="p-6">
            @if($this->allArticles->isEmpty())
                <div class="py-12 text-center text-gray-500">
                    <x-icon name="document-text" class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                    <p class="text-sm">No articles yet</p>
                    <a href="{{ route('admin.help-articles') }}?action=create" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">Create your first article</a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($this->allArticles as $article)
                        <a href="{{ route('help.article', $article->slug) }}" class="group relative border border-gray-200 rounded-lg p-4 hover:border-gray-300 hover:shadow-sm transition-all block">
                            <div class="flex items-start justify-between mb-2">
                                @if($article->is_published)
                                    <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded">Published</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded">Draft</span>
                                @endif
                                @if($article->is_featured)
                                    <x-icon name="star" class="w-4 h-4 text-yellow-500" />
                                @endif
                            </div>
                            <h3 class="font-medium text-gray-900 mb-1 line-clamp-2 group-hover:text-purple-600 transition-colors">{{ $article->title }}</h3>
                            @if($article->excerpt)
                                <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $article->excerpt }}</p>
                            @endif
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span>{{ $article->category?->name ?? 'Uncategorized' }}</span>
                                <span>{{ $article->updated_at->diffForHumans() }}</span>
                            </div>
                            @if($article->view_count > 0)
                                <div class="mt-2 pt-2 border-t border-gray-100 flex items-center gap-3 text-xs text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <x-icon name="eye" class="w-3 h-3" />
                                        {{ number_format($article->view_count) }}
                                    </span>
                                    <span class="flex items-center gap-1 text-green-600">
                                        <x-icon name="hand-thumb-up" class="w-3 h-3" />
                                        {{ $article->helpful_count }}
                                    </span>
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Table View --}}
    @if($view === 'table')
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->allArticles as $article)
                        <tr
                            @click="window.location.href = '{{ route('help.article', $article->slug) }}'"
                            class="hover:bg-gray-50 cursor-pointer transition-colors"
                        >
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900">{{ $article->title }}</span>
                                    @if($article->is_featured)
                                        <x-icon name="star" class="w-4 h-4 text-yellow-500" />
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $article->category?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                @if($article->is_published)
                                    <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded">Published</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ number_format($article->view_count) }}
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="flex items-center gap-1 text-green-600">
                                        <x-icon name="hand-thumb-up" class="w-3 h-3" />
                                        {{ $article->helpful_count }}
                                    </span>
                                    <span class="flex items-center gap-1 text-red-500">
                                        <x-icon name="hand-thumb-down" class="w-3 h-3" />
                                        {{ $article->not_helpful_count }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-400">
                                {{ $article->updated_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <x-icon name="document-text" class="w-12 h-12 mx-auto text-gray-300 mb-3" />
                                <p class="text-sm">No articles yet</p>
                                <a href="{{ route('admin.help-articles') }}?action=create" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">Create your first article</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- Categories Footer --}}
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-700">Categories</h3>
            <a href="{{ route('admin.help-categories') }}" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">Manage →</a>
        </div>
        @if($this->categories->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($this->categories as $category)
                    <a href="{{ route('help.category', $category->slug) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs hover:border-purple-300 hover:bg-purple-50 transition-colors">
                        <x-icon :name="$category->icon" class="w-3.5 h-3.5 text-gray-400" />
                        <span class="font-medium text-gray-600">{{ $category->name }}</span>
                        <span class="text-gray-400">({{ $category->articles_count }})</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
