<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.help') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <x-icon name="arrow-left" class="w-5 h-5" />
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">@term('help_center_label') @term('categories_label')</h1>
                <p class="mt-1 text-sm text-gray-500">@term('create_categories_help_label')</p>
            </div>
        </div>
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
        >
            <x-icon name="plus" class="w-4 h-4" />
            @term('add_action') @term('category_singular')
        </button>
    </div>

    {{-- Categories List --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($this->categories->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <x-icon name="folder" class="w-6 h-6 text-gray-400" />
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">@term('no_categories_yet_label')</h3>
                <p class="text-sm text-gray-500 mb-4">@term('create_categories_help_label')</p>
                <button
                    wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700"
                >
                    <x-icon name="plus" class="w-4 h-4" />
                    @term('create_first_category_label')
                </button>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('category_label')</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('slug_label')</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('parent_label')</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">@term('articles_label')</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">@term('status_label')</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">@term('actions_label')</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->categories as $category)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                        <x-icon :name="$category->icon ?? 'book-open'" class="w-4 h-4 text-gray-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $category->name }}</p>
                                        @if($category->description)
                                            <p class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($category->description, 50) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">{{ $category->slug }}</code>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $category->parent?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ $category->articles->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button
                                    wire:click="toggleActive({{ $category->id }})"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-colors {{ $category->is_active
                                        ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                        : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}"
                                >
                                    {{ $category->is_active ? app(\App\Services\TerminologyService::class)->get('active_label') : app(\App\Services\TerminologyService::class)->get('inactive_label') }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        wire:click="openEditModal({{ $category->id }})"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                                        title="{{ app(\App\Services\TerminologyService::class)->get('edit_action') }}"
                                    >
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </button>
                                    <button
                                        wire:click="delete({{ $category->id }})"
                                        wire:confirm="{{ app(\App\Services\TerminologyService::class)->get('confirm_delete_category_label') }}"
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
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit="save">
                    <div class="bg-white px-6 py-5">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $editMode ? app(\App\Services\TerminologyService::class)->get('edit_action') . ' ' . app(\App\Services\TerminologyService::class)->get('category_singular') : app(\App\Services\TerminologyService::class)->get('add_action') . ' ' . app(\App\Services\TerminologyService::class)->get('category_singular') }}
                            </h3>
                            <button type="button" wire:click="closeModal" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="x-mark" class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="space-y-4">
                            {{-- Name --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">@term('name_label')</label>
                                <input
                                    type="text"
                                    id="name"
                                    wire:model.live="name"
                                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('category_name_placeholder') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                />
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Slug --}}
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">@term('slug_label')</label>
                                <input
                                    type="text"
                                    id="slug"
                                    wire:model="slug"
                                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('category_slug_placeholder') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm font-mono"
                                />
                                @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">@term('description_label')</label>
                                <textarea
                                    id="description"
                                    wire:model="description"
                                    rows="2"
                                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('category_description_placeholder') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                ></textarea>
                                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Icon & Parent --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">@term('icon_label')</label>
                                    <select
                                        id="icon"
                                        wire:model="icon"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                    >
                                        @foreach($this->icons as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="parentId" class="block text-sm font-medium text-gray-700 mb-1">@term('parent_category_label')</label>
                                    <select
                                        id="parentId"
                                        wire:model="parentId"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                    >
                                        <option value="">@term('none_top_level_label')</option>
                                        @foreach($this->parentCategories as $parent)
                                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Sort Order & Active --}}
                            <div class="flex items-center gap-6">
                                <div class="flex-1">
                                    <label for="sortOrder" class="block text-sm font-medium text-gray-700 mb-1">@term('sort_order_label')</label>
                                    <input
                                        type="number"
                                        id="sortOrder"
                                        wire:model="sortOrder"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                    />
                                </div>
                                <div class="flex items-center gap-3 pt-6">
                                    <button
                                        type="button"
                                        wire:click="$toggle('isActive')"
                                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2 {{ $isActive ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                                        role="switch"
                                    >
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isActive ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                    </button>
                                    <label class="text-sm text-gray-700">@term('active_label')</label>
                                </div>
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
                            {{ $editMode ? app(\App\Services\TerminologyService::class)->get('save_changes_label') : app(\App\Services\TerminologyService::class)->get('create_action') . ' ' . app(\App\Services\TerminologyService::class)->get('category_singular') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
