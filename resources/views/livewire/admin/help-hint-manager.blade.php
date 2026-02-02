<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Page Help Hints</h1>
            <p class="mt-1 text-sm text-gray-500">Manage contextual help hints that appear on each page</p>
        </div>
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
        >
            <x-icon name="plus" class="w-4 h-4" />
            Add Hint
        </button>
    </div>

    {{-- Page Context Tabs --}}
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
            @foreach($this->contexts as $key => $label)
                <button
                    wire:click="selectContext('{{ $key }}')"
                    class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium transition-colors {{ $selectedContext === $key
                        ? 'border-pulse-orange-500 text-pulse-orange-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    {{ $label }}
                    @php
                        $count = \App\Models\PageHelpHint::where('page_context', $key)->whereNull('org_id')->count();
                    @endphp
                    @if($count > 0)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $selectedContext === $key ? 'bg-pulse-orange-100 text-pulse-orange-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $count }}
                        </span>
                    @endif
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Hints List --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if($this->hints->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <x-icon name="light-bulb" class="w-6 h-6 text-gray-400" />
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">No help hints yet</h3>
                <p class="text-sm text-gray-500 mb-4">Add hints to guide users through the {{ $this->contexts[$selectedContext] }} page.</p>
                <button
                    wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700"
                >
                    <x-icon name="plus" class="w-4 h-4" />
                    Add your first hint
                </button>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-8">#</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selector</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->hints as $index => $hint)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <code class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">{{ $hint->section }}</code>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $hint->title }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($hint->description, 60) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <code class="px-2 py-1 text-xs bg-purple-50 text-purple-700 rounded font-mono">{{ $hint->selector }}</code>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 capitalize">{{ $hint->position }}</td>
                            <td class="px-4 py-3 text-center">
                                <button
                                    wire:click="toggleActive({{ $hint->id }})"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-colors {{ $hint->is_active
                                        ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                        : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}"
                                >
                                    {{ $hint->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        wire:click="openEditModal({{ $hint->id }})"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                                        title="Edit"
                                    >
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </button>
                                    <button
                                        wire:click="delete({{ $hint->id }})"
                                        wire:confirm="Are you sure you want to delete this hint?"
                                        class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors"
                                        title="Delete"
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

    {{-- Info Card --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <x-icon name="information-circle" class="w-5 h-5 text-blue-500" />
            </div>
            <div class="text-sm text-blue-700">
                <p class="font-medium mb-1">How Help Hints Work</p>
                <ul class="list-disc list-inside space-y-1 text-blue-600">
                    <li><strong>Section:</strong> Unique identifier (lowercase, hyphens only) e.g., <code class="bg-blue-100 px-1 rounded">search-reports</code></li>
                    <li><strong>Selector:</strong> CSS selector targeting the element, e.g., <code class="bg-blue-100 px-1 rounded">[data-help="search-reports"]</code></li>
                    <li><strong>Position:</strong> Where the tooltip appears relative to the element</li>
                    <li>Users enable hints via the Help menu → "Show Page Hints"</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div
        class="fixed inset-0 z-50 overflow-y-auto"
        x-data="{ open: true }"
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

            {{-- Modal --}}
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit="save">
                    <div class="bg-white px-6 py-5">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ $editMode ? 'Edit Help Hint' : 'Add Help Hint' }}
                            </h3>
                            <button type="button" wire:click="closeModal" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="x-mark" class="w-5 h-5" />
                            </button>
                        </div>

                        <div class="space-y-4">
                            {{-- Section --}}
                            <div>
                                <label for="section" class="block text-sm font-medium text-gray-700 mb-1">Section ID</label>
                                <input
                                    type="text"
                                    id="section"
                                    wire:model="section"
                                    placeholder="e.g., search-reports"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm font-mono"
                                    {{ $editMode ? 'disabled' : '' }}
                                />
                                @error('section') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Selector --}}
                            <div>
                                <label for="selector" class="block text-sm font-medium text-gray-700 mb-1">CSS Selector</label>
                                <input
                                    type="text"
                                    id="selector"
                                    wire:model="selector"
                                    placeholder='e.g., [data-help="search-reports"]'
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm font-mono"
                                />
                                @error('selector') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Title --}}
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                                <input
                                    type="text"
                                    id="title"
                                    wire:model="title"
                                    placeholder="Search Reports"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                />
                                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea
                                    id="description"
                                    wire:model="description"
                                    rows="3"
                                    placeholder="Explain what this feature does and how to use it..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                ></textarea>
                                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Position & Sort Order --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Tooltip Position</label>
                                    <select
                                        id="position"
                                        wire:model="position"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                    >
                                        @foreach($this->positions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="sortOrder" class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                                    <input
                                        type="number"
                                        id="sortOrder"
                                        wire:model="sortOrder"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                                    />
                                </div>
                            </div>

                            {{-- Active Toggle --}}
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    wire:click="$toggle('isActive')"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2 {{ $isActive ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                                    role="switch"
                                >
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isActive ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                                <label class="text-sm text-gray-700">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                        >
                            {{ $editMode ? 'Save Changes' : 'Add Hint' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
