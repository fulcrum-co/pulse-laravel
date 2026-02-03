<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Contact Lists</h1>
            <p class="text-sm text-gray-500">Create and manage lists of learners and teachers for targeting courses and collections</p>
        </div>
        <button
            wire:click="openCreateModal"
            class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg text-sm font-medium hover:bg-pulse-orange-600"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create List
        </button>
    </div>

    <!-- Search and Filters -->
    <div class="flex items-center gap-4">
        <div class="relative flex-1 max-w-md">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search lists..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
            >
        </div>

        <select
            wire:model.live="filterType"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
        >
            <option value="">All Types</option>
            <option value="learner">Learners</option>
            <option value="teacher">Teachers</option>
            <option value="mixed">Mixed</option>
        </select>
    </div>

    <!-- Lists Grid -->
    @if($lists->isEmpty())
    <div class="text-center py-12 bg-white rounded-xl border border-gray-200">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <p class="text-gray-500 mb-2">No contact lists yet</p>
        <p class="text-sm text-gray-400">Create a list to start organizing contacts</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($lists as $list)
        <div class="bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2">
                    @if($list->list_type === 'learner')
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    @elseif($list->list_type === 'teacher')
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    @else
                    <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    @endif
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $list->name }}</h3>
                        <span class="text-xs text-gray-500">{{ ucfirst($list->list_type) }}</span>
                    </div>
                </div>

                @if($list->is_dynamic)
                <span class="px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded-full">Dynamic</span>
                @endif
            </div>

            @if($list->description)
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $list->description }}</p>
            @endif

            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">
                    <span class="font-medium text-gray-900">{{ $list->member_count }}</span> members
                </span>

                <div class="flex items-center gap-1">
                    <button
                        wire:click="openMembersModal({{ $list->id }})"
                        class="p-1.5 text-gray-400 hover:text-gray-600 rounded"
                        title="View members"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                    <button
                        wire:click="openEditModal({{ $list->id }})"
                        class="p-1.5 text-gray-400 hover:text-pulse-orange-500 rounded"
                        title="Edit list"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button
                        wire:click="deleteList({{ $list->id }})"
                        wire:confirm="Are you sure you want to delete this list?"
                        class="p-1.5 text-gray-400 hover:text-red-500 rounded"
                        title="Delete list"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($lists->hasPages())
    <div class="mt-4">
        {{ $lists->links() }}
    </div>
    @endif
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editingList ? 'Edit Contact List' : 'Create Contact List' }}
                    </h3>
                    <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input
                            type="text"
                            wire:model="listName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="e.g., High Risk 9th Graders"
                        >
                        @error('listName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                        <textarea
                            wire:model="listDescription"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="Brief description of this list's purpose..."
                        ></textarea>
                    </div>

                    <!-- List Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">List Type</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                type="button"
                                wire:click="$set('listType', 'learner')"
                                class="p-3 rounded-lg border-2 text-center transition-all {{ $listType === 'learner' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                            >
                                <svg class="w-5 h-5 mx-auto mb-1 {{ $listType === 'learner' ? 'text-pulse-orange-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="text-xs font-medium {{ $listType === 'learner' ? 'text-pulse-orange-600' : 'text-gray-700' }}">Learners</span>
                            </button>
                            <button
                                type="button"
                                wire:click="$set('listType', 'teacher')"
                                class="p-3 rounded-lg border-2 text-center transition-all {{ $listType === 'teacher' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                            >
                                <svg class="w-5 h-5 mx-auto mb-1 {{ $listType === 'teacher' ? 'text-pulse-orange-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="text-xs font-medium {{ $listType === 'teacher' ? 'text-pulse-orange-600' : 'text-gray-700' }}">Teachers</span>
                            </button>
                            <button
                                type="button"
                                wire:click="$set('listType', 'mixed')"
                                class="p-3 rounded-lg border-2 text-center transition-all {{ $listType === 'mixed' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                            >
                                <svg class="w-5 h-5 mx-auto mb-1 {{ $listType === 'mixed' ? 'text-pulse-orange-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <span class="text-xs font-medium {{ $listType === 'mixed' ? 'text-pulse-orange-600' : 'text-gray-700' }}">Mixed</span>
                            </button>
                        </div>
                    </div>

                    <!-- Dynamic Toggle -->
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <label class="text-sm font-medium text-gray-700">Dynamic List</label>
                            <p class="text-xs text-gray-500">Auto-updates based on filter criteria</p>
                        </div>
                        <button
                            type="button"
                            wire:click="$toggle('isDynamic')"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $isDynamic ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                        >
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $isDynamic ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>

                    <!-- Dynamic Filters (only for learner lists) -->
                    @if($isDynamic && $listType === 'learner')
                    <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                        <h4 class="text-sm font-medium text-gray-900">Filter Criteria</h4>

                        <!-- Grade Levels -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-2">Grade Levels</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableGrades as $grade)
                                <button
                                    type="button"
                                    wire:click="toggleFilterArrayValue('grade_levels', '{{ $grade }}')"
                                    class="px-2 py-1 text-xs rounded-full border transition-colors {{ in_array($grade, $filterCriteria['grade_levels'] ?? []) ? 'bg-pulse-orange-100 border-pulse-orange-300 text-pulse-orange-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}"
                                >
                                    {{ $grade }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Risk Levels -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-2">Risk Levels</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($riskLevels as $level)
                                <button
                                    type="button"
                                    wire:click="toggleFilterArrayValue('risk_levels', '{{ $level }}')"
                                    class="px-2 py-1 text-xs rounded-full border transition-colors {{ in_array($level, $filterCriteria['risk_levels'] ?? []) ? 'bg-pulse-orange-100 border-pulse-orange-300 text-pulse-orange-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}"
                                >
                                    {{ ucfirst($level) }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Classrooms -->
                        @if($availableClassrooms->isNotEmpty())
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-2">Classrooms</label>
                            <select
                                wire:change="toggleFilterArrayValue('classroom_ids', $event.target.value)"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"
                            >
                                <option value="">Select classroom...</option>
                                @foreach($availableClassrooms as $classroom)
                                <option value="{{ $classroom->id }}">{{ $classroom->name }}</option>
                                @endforeach
                            </select>
                            @if(!empty($filterCriteria['classroom_ids']))
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($filterCriteria['classroom_ids'] as $classroomId)
                                @php $classroom = $availableClassrooms->firstWhere('id', $classroomId); @endphp
                                @if($classroom)
                                <span class="inline-flex items-center px-2 py-0.5 text-xs bg-gray-100 text-gray-700 rounded">
                                    {{ $classroom->name }}
                                    <button wire:click="toggleFilterArrayValue('classroom_ids', {{ $classroomId }})" class="ml-1 text-gray-400 hover:text-gray-600">&times;</button>
                                </span>
                                @endif
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endif

                        <!-- Preview Count -->
                        @if($previewCount > 0)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <p class="text-sm text-green-700">
                                <span class="font-medium">{{ $previewCount }}</span> contacts match these criteria
                            </p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        wire:click="closeModal"
                        class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="saveList"
                        class="px-4 py-2 text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        {{ $editingList ? 'Update List' : 'Create List' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Members Modal -->
    @if($showMembersModal && $viewingList)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeMembersModal"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[80vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $viewingList->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $viewingList->member_count }} members</p>
                        </div>
                        <button wire:click="closeMembersModal" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    @if($viewingList->is_dynamic)
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-purple-700">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            This is a dynamic list. Members are automatically determined by filter criteria.
                        </p>
                    </div>
                    @else
                    <!-- Add Members Section -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Add Members</h4>
                        <div class="flex gap-2">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="memberSearch"
                                placeholder="Search by name..."
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            >
                            <button
                                wire:click="addSelectedMembers"
                                @if(empty($selectedMembers)) disabled @endif
                                class="px-4 py-2 bg-pulse-orange-500 text-white rounded-lg text-sm hover:bg-pulse-orange-600 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Add Selected
                            </button>
                        </div>

                        @if($availableMembers->isNotEmpty())
                        <div class="mt-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg">
                            @foreach($availableMembers as $member)
                            <label class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model="selectedMembers"
                                    value="{{ $member['key'] }}"
                                    class="rounded text-pulse-orange-500 focus:ring-pulse-orange-500"
                                >
                                <span class="text-sm text-gray-900">{{ $member['name'] }}</span>
                                <span class="text-xs text-gray-500">{{ $member['meta'] }}</span>
                            </label>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Current Members -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Current Members</h4>
                        @php
                            $members = $viewingList->getAllMembers();
                        @endphp

                        @if($members->isEmpty())
                        <p class="text-sm text-gray-500 text-center py-4">No members in this list</p>
                        @else
                        <div class="space-y-2">
                            @foreach($members as $member)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600">
                                        {{ substr($member instanceof \App\Models\Learner ? $member->full_name : $member->first_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $member instanceof \App\Models\Learner ? $member->full_name : $member->full_name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            @if($member instanceof \App\Models\Learner)
                                            Learner - Grade {{ $member->grade_level }}
                                            @else
                                            {{ ucfirst($member->role) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if(!$viewingList->is_dynamic)
                                <button
                                    wire:click="removeMember('{{ $member instanceof \App\Models\Learner ? 'learner' : 'user' }}', {{ $member->id }})"
                                    class="p-1 text-gray-400 hover:text-red-500"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    <button
                        wire:click="closeMembersModal"
                        class="w-full px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
