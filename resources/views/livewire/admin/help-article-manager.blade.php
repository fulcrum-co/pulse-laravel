<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.help') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <x-icon name="arrow-left" class="w-5 h-5" />
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">@term('help_center_label') @term('articles_label')</h1>
                <p class="mt-1 text-sm text-gray-500">@term('manage_kb_articles_label')</p>
            </div>
        </div>
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
        >
            <x-icon name="plus" class="w-4 h-4" />
            @term('new_article_label')
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="relative flex-1 max-w-md">
            <x-icon name="magnifying-glass" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ app(\App\Services\TerminologyService::class)->get('search_articles_placeholder') }}"
                class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            />
        </div>
        <div class="flex items-center gap-3">
            <select
                wire:model.live="categoryFilter"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">@term('all_categories_label')</option>
                @foreach($this->categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select
                wire:model.live="statusFilter"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">@term('all_status_label')</option>
                <option value="published">@term('published_label')</option>
                <option value="draft">@term('drafts_label')</option>
                <option value="featured">@term('featured_label')</option>
            </select>
        </div>
    </div>

    {{-- Articles List --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($this->articles->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <x-icon name="document-text" class="w-6 h-6 text-gray-400" />
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">@term('no_articles_found_label')</h3>
                <p class="text-sm text-gray-500 mb-4">
                    @if($search || $categoryFilter || $statusFilter)
                        @term('adjust_filters_label')
                    @else
                        @term('create_first_help_article_label')
                    @endif
                </p>
                @if(!$search && !$categoryFilter && !$statusFilter)
                    <button
                        wire:click="openCreateModal"
                        class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700"
                    >
                        <x-icon name="plus" class="w-4 h-4" />
                        @term('create_first_article_label')
                    </button>
                @endif
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('article_singular')</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('category_label')</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">@term('views_label')</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">@term('status_label')</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">@term('actions_label')</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->articles as $article)
                        <tr
                            @click="window.location.href = '{{ route('help.article', $article->slug) }}'"
                            class="hover:bg-gray-50 transition-colors cursor-pointer"
                        >
                            <td class="px-4 py-3">
                                <div class="flex items-start gap-3">
                                    @if($article->is_featured)
                                        <x-icon name="star" class="w-4 h-4 text-yellow-500 flex-shrink-0 mt-0.5" />
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $article->title }}</p>
                                        <p class="text-xs text-gray-500 truncate max-w-md">{{ $article->excerpt }}</p>
                                        <p class="text-xs text-gray-400 mt-1">@term('updated_label') {{ $article->updated_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($article->category)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                        {{ $article->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">@term('uncategorized_label')</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm text-gray-600">{{ number_format($article->view_count) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center" @click.stop>
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        wire:click="togglePublished({{ $article->id }})"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-colors {{ $article->is_published
                                            ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                            : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}"
                                    >
                                        {{ $article->is_published ? app(\App\Services\TerminologyService::class)->get('published_label') : app(\App\Services\TerminologyService::class)->get('draft_label') }}
                                    </button>
                                    <button
                                        wire:click="toggleFeatured({{ $article->id }})"
                                        class="p-1 rounded transition-colors {{ $article->is_featured
                                            ? 'text-yellow-500 hover:text-yellow-600'
                                            : 'text-gray-300 hover:text-yellow-500' }}"
                                        title="{{ $article->is_featured ? app(\App\Services\TerminologyService::class)->get('remove_featured_label') : app(\App\Services\TerminologyService::class)->get('mark_featured_label') }}"
                                    >
                                        <x-icon name="star" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex items-center justify-end gap-1">
                                    <a
                                        href="/help/article/{{ $article->slug }}"
                                        target="_blank"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                                        title="{{ app(\App\Services\TerminologyService::class)->get('preview_label') }}"
                                    >
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </a>
                                    <button
                                        wire:click="openEditModal({{ $article->id }})"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                                        title="{{ app(\App\Services\TerminologyService::class)->get('edit_action') }}"
                                    >
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </button>
                                    <button
                                        wire:click="duplicate({{ $article->id }})"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                                        title="{{ app(\App\Services\TerminologyService::class)->get('duplicate_action') }}"
                                    >
                                        <x-icon name="document-duplicate" class="w-4 h-4" />
                                    </button>
                                    <button
                                        wire:click="delete({{ $article->id }})"
                                        wire:confirm="{{ app(\App\Services\TerminologyService::class)->get('confirm_delete_article_label') }}"
                                        class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors"
                                        title="{{ app(\App\Services\TerminologyService::class)->get('delete_action') }}"
                                    >
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($this->articles->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $this->articles->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <form wire:submit="save">
                    <div class="bg-white px-6 py-5 max-h-[80vh] overflow-y-auto">
                        <div class="flex items-center justify-between mb-5 sticky top-0 bg-white pb-3 border-b border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $editMode ? app(\App\Services\TerminologyService::class)->get('edit_article_label') : app(\App\Services\TerminologyService::class)->get('new_article_label') }}
                            </h3>
                            <button type="button" wire:click="closeModal" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="x-mark" class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="space-y-5">
                            {{-- Title --}}
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">@term('title_label')</label>
                                <input
                                    type="text"
                                    id="title"
                                    wire:model.live="title"
                                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('article_title_placeholder') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                />
                                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Slug & Category --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">@term('slug_label')</label>
                                    <input
                                        type="text"
                                        id="slug"
                                        wire:model="slug"
                                        placeholder="{{ app(\App\Services\TerminologyService::class)->get('slug_placeholder') }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm font-mono"
                                    />
                                    @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="categoryId" class="block text-sm font-medium text-gray-700 mb-1">@term('category_label')</label>
                                    <select
                                        id="categoryId"
                                        wire:model="categoryId"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                    >
                                        <option value="">{{ app(\App\Services\TerminologyService::class)->get('select_category_placeholder') }}</option>
                                        @foreach($this->categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div>
                                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">@term('content_markdown_label')</label>
                                <textarea
                                    id="content"
                                    wire:model="content"
                                    rows="12"
                                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('content_markdown_placeholder') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm font-mono"
                                ></textarea>
                                @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                <p class="mt-1 text-xs text-gray-500">@term('supports_markdown_label')</p>
                            </div>

                            {{-- Excerpt --}}
                            <div>
                                <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">@term('excerpt_optional_label')</label>
                                <textarea
                                    id="excerpt"
                                    wire:model="excerpt"
                                    rows="2"
                                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('excerpt_placeholder') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                ></textarea>
                                <p class="mt-1 text-xs text-gray-500">@term('auto_generated_excerpt_label')</p>
                            </div>

                            {{-- Video URL --}}
                            <div>
                                <label for="videoUrl" class="block text-sm font-medium text-gray-700 mb-1">@term('video_url_optional_label')</label>
                                <input
                                    type="url"
                                    id="videoUrl"
                                    wire:model="videoUrl"
                                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('video_url_placeholder') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                />
                            </div>

                            {{-- Keywords --}}
                            <div>
                                <label for="keywordsInput" class="block text-sm font-medium text-gray-700 mb-1">@term('search_keywords_label')</label>
                                <input
                                    type="text"
                                    id="keywordsInput"
                                    wire:model="keywordsInput"
                                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('search_keywords_placeholder') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                />
                                <p class="mt-1 text-xs text-gray-500">@term('search_keywords_help_label')</p>
                            </div>

                            {{-- Target Roles --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">@term('target_roles_optional_label')</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($this->availableRoles as $role => $label)
                                        <label class="inline-flex items-center gap-2 px-3 py-1.5 border rounded-lg cursor-pointer transition-colors {{ in_array($role, $targetRoles) ? 'border-pulse-orange-500 bg-pulse-orange-50 text-pulse-orange-700' : 'border-gray-200 hover:border-gray-300' }}">
                                            <input
                                                type="checkbox"
                                                wire:model="targetRoles"
                                                value="{{ $role }}"
                                                class="sr-only"
                                            />
                                            <span class="text-sm">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-1 text-xs text-gray-500">@term('target_roles_help_label')</p>
                            </div>

                            {{-- Publishing Options --}}
                            <div class="flex items-center gap-6 pt-4 border-t border-gray-100">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <button
                                        type="button"
                                        wire:click="$toggle('isPublished')"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2 {{ $isPublished ? 'bg-green-500' : 'bg-gray-200' }}"
                                        role="switch"
                                    >
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isPublished ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                    <span class="text-sm font-medium text-gray-700">@term('published_label')</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <button
                                        type="button"
                                        wire:click="$toggle('isFeatured')"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2 {{ $isFeatured ? 'bg-yellow-500' : 'bg-gray-200' }}"
                                        role="switch"
                                    >
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isFeatured ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                    <span class="text-sm font-medium text-gray-700">@term('featured_label')</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            @term('cancel_action')
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                        >
                            {{ $editMode ? app(\App\Services\TerminologyService::class)->get('save_changes_label') : app(\App\Services\TerminologyService::class)->get('create_article_label') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
