<div class="bg-white rounded-lg border border-gray-200 p-6">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            {{-- Title --}}
            <div class="mb-4">
                @if($editingTitle)
                    <div class="flex items-center gap-2">
                        <input type="text" wire:model="newTitle" wire:keydown.enter="saveTitle" wire:keydown.escape="cancelEditTitle"
                            class="text-xl font-semibold px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            autofocus>
                        <button wire:click="saveTitle" class="text-pulse-orange-500 hover:text-pulse-orange-600 text-sm font-medium">Save</button>
                        <button wire:click="cancelEditTitle" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                    </div>
                @else
                    <h2 wire:click="startEditTitle" class="text-xl font-semibold text-gray-900 cursor-pointer hover:text-pulse-orange-600">
                        {{ $strategy->title }}
                    </h2>
                @endif
            </div>

            {{-- Owners and Collaborators --}}
            <div class="flex items-center gap-4 mb-4">
                <span class="text-sm text-gray-600">Owners and Collaborators</span>
                <div class="flex items-center -space-x-2">
                    @foreach($collaborators->take(5) as $collab)
                        <div class="w-8 h-8 rounded-full bg-pulse-orange-100 border-2 border-white flex items-center justify-center text-sm font-medium text-pulse-orange-600"
                             title="{{ $collab->user->first_name }} {{ $collab->user->last_name }} ({{ ucfirst($collab->role) }})">
                            {{ substr($collab->user->first_name ?? 'U', 0, 1) }}{{ substr($collab->user->last_name ?? '', 0, 1) }}
                        </div>
                    @endforeach
                    @if($collaborators->count() > 5)
                        <div class="w-8 h-8 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-sm font-medium text-gray-600">
                            +{{ $collaborators->count() - 5 }}
                        </div>
                    @endif
                </div>
                <button wire:click="openCollaboratorModal" class="w-8 h-8 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center text-gray-400 hover:border-pulse-orange-500 hover:text-pulse-orange-500">
                    <x-icon name="plus" class="w-4 h-4" />
                </button>
            </div>

            {{-- Assign To --}}
            <div class="flex items-center gap-3">
                <button wire:click="openAssignmentModal" class="inline-flex items-center px-3 py-1.5 bg-pulse-orange-500 text-white rounded text-sm font-medium hover:bg-pulse-orange-600">
                    Assign to +
                </button>

                @foreach($assignments as $assignment)
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 rounded text-sm">
                        <x-icon name="x" class="w-3 h-3 cursor-pointer hover:text-red-500" wire:click="removeAssignment({{ $assignment->id }})" />
                        {{ $assignment->display_name }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Create Plan +</span>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="px-3 py-2 text-sm text-gray-700 hover:text-gray-900 flex items-center gap-1">
                    Actions
                    <x-icon name="chevron-down" class="w-4 h-4" />
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                    <a href="{{ route('strategies.edit', $strategy) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Edit</a>
                    <form action="{{ route('strategies.duplicate', $strategy) }}" method="POST">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Duplicate</button>
                    </form>
                    <button @click="$dispatch('openPushStrategy'); open = false" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Push to Org</button>
                    <hr class="my-1">
                    <form action="{{ route('strategies.destroy', $strategy) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
                    </form>
                </div>
            </div>

            <button class="px-4 py-2 bg-pulse-orange-500 text-white rounded-lg text-sm font-medium hover:bg-pulse-orange-600 flex items-center gap-1">
                Share
                <x-icon name="chevron-down" class="w-4 h-4" />
            </button>
        </div>
    </div>

    {{-- Collaborator Modal --}}
    @if($showCollaboratorModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold mb-4">Add Collaborator</h3>

                <input type="text" wire:model.live.debounce.300ms="searchCollaborator"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4"
                    placeholder="Search users...">

                <div class="mb-4">
                    <label class="block text-sm text-gray-600 mb-1">Role</label>
                    <select wire:model="selectedCollaboratorRole" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="collaborator">Collaborator</option>
                        <option value="owner">Owner</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>

                @if($searchedUsers->count() > 0)
                    <div class="border border-gray-200 rounded-lg divide-y max-h-48 overflow-y-auto mb-4">
                        @foreach($searchedUsers as $user)
                            <button wire:click="addCollaborator({{ $user->id }})"
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-medium">
                                    {{ substr($user->first_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium">{{ $user->first_name }} {{ $user->last_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif

                {{-- Current Collaborators --}}
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Current Collaborators</h4>
                    <div class="space-y-2">
                        @foreach($collaborators as $collab)
                            <div class="flex items-center justify-between py-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-pulse-orange-100 flex items-center justify-center text-sm font-medium text-pulse-orange-600">
                                        {{ substr($collab->user->first_name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="text-sm">{{ $collab->user->first_name }} {{ $collab->user->last_name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <select wire:change="updateCollaboratorRole({{ $collab->id }}, $event.target.value)" class="text-xs border-gray-200 rounded">
                                        <option value="owner" {{ $collab->role === 'owner' ? 'selected' : '' }}>Owner</option>
                                        <option value="collaborator" {{ $collab->role === 'collaborator' ? 'selected' : '' }}>Collaborator</option>
                                        <option value="viewer" {{ $collab->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                    </select>
                                    <button wire:click="removeCollaborator({{ $collab->id }})" class="text-gray-400 hover:text-red-500">
                                        <x-icon name="x" class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    <button wire:click="closeCollaboratorModal" class="px-4 py-2 text-gray-700 hover:text-gray-900">Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
