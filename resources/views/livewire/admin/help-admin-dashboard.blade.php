<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Help Center</h1>
            <p class="mt-1 text-sm text-gray-500">Manage help articles, categories, and page hints</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Articles Stats --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                    <x-icon name="document-text" class="w-6 h-6 text-purple-600" />
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $this->stats['articles']['total'] }}</h3>
                    <p class="text-sm text-gray-500">Help Articles</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-green-50 rounded-lg p-2">
                    <p class="text-lg font-semibold text-green-700">{{ $this->stats['articles']['published'] }}</p>
                    <p class="text-xs text-green-600">Published</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-2">
                    <p class="text-lg font-semibold text-gray-700">{{ $this->stats['articles']['draft'] }}</p>
                    <p class="text-xs text-gray-600">Drafts</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-2">
                    <p class="text-lg font-semibold text-yellow-700">{{ $this->stats['articles']['featured'] }}</p>
                    <p class="text-xs text-yellow-600">Featured</p>
                </div>
            </div>
            <a href="{{ route('admin.help-articles') }}" class="mt-4 block w-full text-center py-2 text-sm font-medium text-purple-600 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors">
                Manage Articles →
            </a>
        </div>

        {{-- Categories Stats --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                    <x-icon name="folder" class="w-6 h-6 text-blue-600" />
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $this->stats['categories']['total'] }}</h3>
                    <p class="text-sm text-gray-500">Categories</p>
                </div>
            </div>
            <div class="flex items-center justify-center gap-4 py-3">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $this->stats['categories']['active'] }}</p>
                    <p class="text-xs text-gray-500">Active</p>
                </div>
                <div class="w-px h-8 bg-gray-200"></div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-400">{{ $this->stats['categories']['total'] - $this->stats['categories']['active'] }}</p>
                    <p class="text-xs text-gray-500">Inactive</p>
                </div>
            </div>
            <a href="{{ route('admin.help-categories') }}" class="mt-4 block w-full text-center py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                Manage Categories →
            </a>
        </div>

        {{-- Page Hints Stats --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center">
                    <x-icon name="light-bulb" class="w-6 h-6 text-orange-600" />
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $this->stats['hints']['total'] }}</h3>
                    <p class="text-sm text-gray-500">Page Hints</p>
                </div>
            </div>
            <div class="flex items-center justify-center gap-4 py-3">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $this->stats['hints']['active'] }}</p>
                    <p class="text-xs text-gray-500">Active</p>
                </div>
                <div class="w-px h-8 bg-gray-200"></div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-purple-600">{{ $this->stats['hints']['pages'] }}</p>
                    <p class="text-xs text-gray-500">Pages</p>
                </div>
            </div>
            <a href="{{ route('admin.help-hints') }}" class="mt-4 block w-full text-center py-2 text-sm font-medium text-orange-600 hover:text-orange-700 hover:bg-orange-50 rounded-lg transition-colors">
                Manage Hints →
            </a>
        </div>
    </div>

    {{-- Content Sections --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Articles --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Recently Updated</h2>
                <a href="{{ route('admin.help-articles') }}" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">View all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($this->recentArticles as $article)
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
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
                            @if($article->is_published)
                                <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded">Published</span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded">Draft</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-500">
                        <p class="text-sm">No articles yet</p>
                        <a href="{{ route('admin.help-articles') }}" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">Create your first article</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Popular Articles --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Most Viewed</h2>
                <span class="text-sm text-gray-400">Published only</span>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($this->popularArticles as $article)
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $article->title }}</p>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="flex items-center gap-1 text-xs text-gray-500">
                                    <x-icon name="eye" class="w-3 h-3" />
                                    {{ number_format($article->view_count) }} views
                                </span>
                                <span class="flex items-center gap-1 text-xs text-green-600">
                                    <x-icon name="hand-thumb-up" class="w-3 h-3" />
                                    {{ $article->helpful_count }}
                                </span>
                                <span class="flex items-center gap-1 text-xs text-red-500">
                                    <x-icon name="hand-thumb-down" class="w-3 h-3" />
                                    {{ $article->not_helpful_count }}
                                </span>
                            </div>
                        </div>
                        @if($article->is_featured)
                            <x-icon name="star" class="w-4 h-4 text-yellow-500" />
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-gray-500">
                        <p class="text-sm">No published articles yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-gradient-to-r from-pulse-orange-500 to-orange-600 rounded-xl p-6 text-white">
        <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="{{ route('admin.help-articles') }}?action=create" class="flex items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <x-icon name="plus" class="w-5 h-5" />
                </div>
                <div>
                    <p class="font-medium">New Article</p>
                    <p class="text-sm text-white/70">Create help content</p>
                </div>
            </a>
            <a href="{{ route('admin.help-categories') }}?action=create" class="flex items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <x-icon name="folder-plus" class="w-5 h-5" />
                </div>
                <div>
                    <p class="font-medium">New Category</p>
                    <p class="text-sm text-white/70">Organize articles</p>
                </div>
            </a>
            <a href="{{ route('admin.help-hints') }}?action=create" class="flex items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-lg transition-colors">
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <x-icon name="light-bulb" class="w-5 h-5" />
                </div>
                <div>
                    <p class="font-medium">New Hint</p>
                    <p class="text-sm text-white/70">Add page guidance</p>
                </div>
            </a>
        </div>
    </div>
</div>
