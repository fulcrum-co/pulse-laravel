<div class="bg-white rounded-lg border border-gray-200 p-4">
    @php($terminology = app(\App\Services\TerminologyService::class))
    <div class="flex items-start justify-between">
        <div class="flex-1">
            {{-- Title --}}
            <div class="mb-3">
                @if($editingTitle)
                    <div class="flex items-center gap-2">
                        <input type="text" wire:model="newTitle" wire:keydown.enter="saveTitle" wire:keydown.escape="cancelEditTitle"
                            class="text-lg font-semibold px-2 py-0.5 border border-gray-300 rounded focus:ring-1 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            autofocus>
                        <button wire:click="saveTitle" class="text-pulse-orange-500 hover:text-pulse-orange-600 text-xs font-medium">@term('save_label')</button>
                        <button wire:click="cancelEditTitle" class="text-gray-400 hover:text-gray-600 text-xs">@term('cancel_label')</button>
                    </div>
                @else
                    <h2 wire:click="startEditTitle" class="text-lg font-semibold text-gray-900 cursor-pointer hover:text-pulse-orange-600">
                        {{ $plan->title }}
                    </h2>
                @endif
            </div>

            {{-- Owners and Collaborators --}}
            <div class="flex items-center gap-3 mb-3">
                <span class="text-xs text-gray-500">@term('owners_collaborators_label')</span>
                <div class="flex items-center -space-x-1.5">
                    @foreach($collaborators->take(5) as $collab)
                        <div class="w-7 h-7 rounded-full bg-pulse-orange-100 border-2 border-white flex items-center justify-center text-xs font-medium text-pulse-orange-600"
                             title="{{ $collab->user->first_name }} {{ $collab->user->last_name }} ({{ ucfirst($collab->role) }})">
                            {{ substr($collab->user->first_name ?? 'U', 0, 1) }}{{ substr($collab->user->last_name ?? '', 0, 1) }}
                        </div>
                    @endforeach
                    @if($collaborators->count() > 5)
                        <div class="w-7 h-7 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-xs font-medium text-gray-500">
                            +{{ $collaborators->count() - 5 }}
                        </div>
                    @endif
                </div>
                <button wire:click="openCollaboratorModal" class="w-7 h-7 rounded-full border border-dashed border-gray-300 flex items-center justify-center text-gray-400 hover:border-pulse-orange-400 hover:text-pulse-orange-500">
                    <x-icon name="plus" class="w-3.5 h-3.5" />
                </button>
            </div>

            {{-- Assign To --}}
            <div class="flex items-center gap-2">
                <button wire:click="openAssignmentModal" class="inline-flex items-center px-2.5 py-1 bg-pulse-orange-500 text-white rounded text-xs font-medium hover:bg-pulse-orange-600">
                    @term('assign_to_label') +
                </button>

                @foreach($assignments as $assignment)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 rounded text-xs">
                        <x-icon name="x-mark" class="w-3 h-3 cursor-pointer hover:text-red-500" wire:click="removeAssignment({{ $assignment->id }})" />
                        {{ $assignment->display_name }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-1">
            <a href="{{ route('plans.create') }}" class="text-xs text-gray-400 hover:text-gray-600 px-2">@term('create_plan_label') +</a>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="px-2 py-1.5 text-xs text-gray-600 hover:text-gray-900 flex items-center gap-1">
                    @term('actions_label')
                    <x-icon name="chevron-down" class="w-3.5 h-3.5" />
                </button>
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-36 bg-white border border-gray-200 rounded-lg shadow-lg z-10 py-1">
                    <a href="{{ route('plans.edit', $plan) }}" class="block px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50">@term('edit_label')</a>
                    <form action="{{ route('plans.duplicate', $plan) }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50">@term('duplicate_label')</button>
                    </form>
                    <button @click="$dispatch('openPushPlan'); open = false" class="block w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-50">@term('push_to_organizations_label')</button>
                    <hr class="my-1">
                    <form action="{{ route('plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('{{ $terminology->get('delete_plan_confirm_label') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="block w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-red-50">@term('delete_label')</button>
                    </form>
                </div>
            </div>

            <button class="px-3 py-1.5 bg-pulse-orange-500 text-white rounded text-xs font-medium hover:bg-pulse-orange-600 flex items-center gap-1">
                @term('share_label')
                <x-icon name="chevron-down" class="w-3.5 h-3.5" />
            </button>
        </div>
    </div>

    {{-- Collaborator Modal --}}
    @if($showCollaboratorModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm p-4">
                <h3 class="text-base font-semibold mb-3">@term('add_collaborator_label')</h3>

                <input type="text" wire:model.live.debounce.300ms="searchCollaborator"
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded mb-3"
                    placeholder="@term('search_users_placeholder')">

                <div class="mb-3">
                    <label class="block text-xs text-gray-500 mb-1">@term('role_label')</label>
                    <select wire:model="selectedCollaboratorRole" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded">
                        <option value="collaborator">@term('collaborator_role_label')</option>
                        <option value="owner">@term('owner_role_label')</option>
                        <option value="viewer">@term('viewer_role_label')</option>
                    </select>
                </div>

                @if($searchedUsers->count() > 0)
                    <div class="border border-gray-200 rounded divide-y max-h-40 overflow-y-auto mb-3">
                        @foreach($searchedUsers as $user)
                            <button wire:click="addCollaborator({{ $user->id }})"
                                class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium">
                                    {{ substr($user->first_name, 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium truncate">{{ $user->first_name }} {{ $user->last_name }}</p>
                                    <p class="text-[10px] text-gray-400 truncate">{{ $user->email }}</p>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Current Collaborators --}}
                @if($collaborators->count() > 0)
                <div class="mb-3">
                    <h4 class="text-xs font-medium text-gray-500 mb-2">@term('current_label')</h4>
                    <div class="space-y-1">
                        @foreach($collaborators as $collab)
                            <div class="flex items-center justify-between py-1.5 px-2 bg-gray-50 rounded">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-full bg-pulse-orange-100 flex items-center justify-center text-xs font-medium text-pulse-orange-600">
                                        {{ substr($collab->user->first_name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="text-xs truncate">{{ $collab->user->first_name }} {{ $collab->user->last_name }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <select wire:change="updateCollaboratorRole({{ $collab->id }}, $event.target.value)" class="text-[10px] border-gray-200 rounded py-0.5 px-1">
                                        <option value="owner" {{ $collab->role === 'owner' ? 'selected' : '' }}>@term('owner_role_label')</option>
                                        <option value="collaborator" {{ $collab->role === 'collaborator' ? 'selected' : '' }}>@term('collaborator_role_label')</option>
                                        <option value="viewer" {{ $collab->role === 'viewer' ? 'selected' : '' }}>@term('viewer_role_label')</option>
                                    </select>
                                    <button wire:click="removeCollaborator({{ $collab->id }})" class="text-gray-400 hover:text-red-500 p-0.5">
                                        <x-icon name="x-mark" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex justify-end">
                    <button wire:click="closeCollaboratorModal" class="px-3 py-1.5 text-xs text-gray-600 hover:text-gray-900">@term('close_label')</button>
                </div>
            </div>
        </div>
    @endif
</div>
