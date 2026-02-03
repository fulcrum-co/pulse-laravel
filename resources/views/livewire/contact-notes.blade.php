<div>
    <!-- New Note Form -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
        <form wire:submit.prevent="saveNote">
            <!-- Note Type Selector -->
            <div class="flex flex-wrap gap-2 mb-3">
                @foreach([
                    'general' => 'note_type_general_label',
                    'follow_up' => 'note_type_follow_up_label',
                    'concern' => 'note_type_concern_label',
                    'milestone' => 'note_type_milestone_label',
                ] as $type => $labelKey)
                <button
                    type="button"
                    wire:click="$set('newNoteType', '{{ $type }}')"
                    class="px-3 py-1 text-sm rounded-full transition-colors {{ $newNoteType === $type ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:border-gray-400' }}"
                >
                    {{ app(\App\Services\TerminologyService::class)->get($labelKey) }}
                </button>
                @endforeach
            </div>

            <!-- Content Input -->
            <textarea
                wire:model="newNoteContent"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                placeholder="@term('add_note_placeholder')"
            ></textarea>

            <!-- Voice Memo Upload -->
            <div class="mt-3">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-gray-900">
                    <input type="file" wire:model="voiceMemo" accept="audio/*" class="hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                    </svg>
                    <span>{{ $voiceMemo ? $voiceMemo->getClientOriginalName() : app(\App\Services\TerminologyService::class)->get('upload_voice_memo_label') }}</span>
                </label>
                @if($voiceMemo)
                <div wire:loading wire:target="voiceMemo" class="mt-2 text-sm text-blue-600">
                    @term('uploading_label')
                </div>
                @endif
            </div>

            <!-- Options Row -->
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center gap-4">
                    <!-- Visibility -->
                    <select wire:model="visibility" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500">
                        <option value="organization">@term('visible_to_organization_label')</option>
                        <option value="team">@term('visible_to_team_label')</option>
                        <option value="private">@term('private_label')</option>
                    </select>

                    <!-- Private Toggle -->
                    <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" wire:model="isPrivate" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        @term('private_note_label')
                    </label>
                </div>

                <x-button type="submit" variant="primary" size="small">
                    @term('save_note_label')
                </x-button>
            </div>

            @error('newNoteContent') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </form>
    </div>

    <!-- Filter Tabs -->
    <div class="flex gap-2 mb-4 overflow-x-auto pb-2">
        @foreach([
            'all' => 'all_label',
            'general' => 'note_type_general_label',
            'follow_up' => 'note_type_follow_up_label',
            'concern' => 'concerns_label',
            'milestone' => 'milestones_label',
            'voice_memo' => 'voice_memos_label',
        ] as $type => $labelKey)
        <button
            wire:click="setFilterType('{{ $type }}')"
            class="px-3 py-1 text-sm font-medium rounded-lg whitespace-nowrap transition-colors {{ $filterType === $type ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
        >
            {{ app(\App\Services\TerminologyService::class)->get($labelKey) }}
        </button>
        @endforeach
    </div>

    <!-- Notes List -->
    <div class="space-y-4">
        @forelse($notes as $note)
        <div
            @can('update', $note)
                @if($editingNoteId !== $note->id)
                    wire:click="startEdit({{ $note->id }})"
                @endif
            @endcan
            class="p-4 border border-gray-200 rounded-lg {{ $note->is_private ? 'bg-yellow-50 border-yellow-200' : '' }} @can('update', $note) {{ $editingNoteId !== $note->id ? 'cursor-pointer hover:border-gray-300 hover:shadow-sm transition-all' : '' }} @endcan"
        >
            <!-- Header -->
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600">
                        {{ substr($note->author->name ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $note->author->name ?? app(\App\Services\TerminologyService::class)->get('unknown_label') }}</div>
                        <div class="text-xs text-gray-500">{{ $note->created_at->diffForHumans() }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @php
                        $typeColor = match($note->note_type) {
                            'concern' => 'red',
                            'follow_up' => 'yellow',
                            'milestone' => 'green',
                            'voice_memo' => 'purple',
                            default => 'gray',
                        };
                        $typeLabels = [
                            'general' => app(\App\Services\TerminologyService::class)->get('note_type_general_label'),
                            'follow_up' => app(\App\Services\TerminologyService::class)->get('note_type_follow_up_label'),
                            'concern' => app(\App\Services\TerminologyService::class)->get('note_type_concern_label'),
                            'milestone' => app(\App\Services\TerminologyService::class)->get('note_type_milestone_label'),
                            'voice_memo' => app(\App\Services\TerminologyService::class)->get('voice_memo_label'),
                        ];
                    @endphp
                    <x-badge :color="$typeColor">{{ $typeLabels[$note->note_type] ?? app(\App\Services\TerminologyService::class)->get('unknown_label') }}</x-badge>

                    @if($note->is_private)
                    <x-badge color="yellow">@term('private_label')</x-badge>
                    @endif

                    <!-- Actions -->
                    @can('update', $note)
                    <div class="relative" x-data="{ open: false }" @click.stop>
                        <button @click="open = !open" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                            <button wire:click="startEdit({{ $note->id }})" class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">@term('edit_action')</button>
                            <button wire:click="deleteNote({{ $note->id }})" wire:confirm="@term('delete_note_confirm_label')" class="w-full px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50">@term('delete_action')</button>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>

            <!-- Content -->
            @if($editingNoteId === $note->id)
            <div class="mt-3" @click.stop>
                <textarea wire:model="editContent" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                <div class="flex gap-2 mt-2">
                    <x-button wire:click="updateNote" variant="primary" size="small">@term('save_action')</x-button>
                    <x-button wire:click="cancelEdit" variant="secondary" size="small">@term('cancel_action')</x-button>
                </div>
            </div>
            @else
            <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $note->content }}</div>
            @endif

            <!-- Voice Memo Player -->
            @if($note->is_voice_memo && $note->audio_file_path)
            <div class="mt-3 p-3 bg-gray-100 rounded-lg" @click.stop>
                <div class="flex items-center gap-3">
                    <button class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </button>
                    <div class="flex-1">
                        <div class="text-xs text-gray-500">@term('voice_memo_label')</div>
                        <audio controls class="w-full h-8">
                            <source src="{{ route('api.notes.audio', $note) }}" type="audio/mpeg">
                        </audio>
                    </div>
                </div>

                @if($note->transcription_status === 'completed' && $note->transcription)
                <div class="mt-2 pt-2 border-t border-gray-200">
                    <div class="text-xs text-gray-500 mb-1">@term('transcription_label')</div>
                    <div class="text-sm text-gray-700">{{ $note->transcription }}</div>
                </div>
                @elseif($note->transcription_status === 'processing')
                <div class="mt-2 text-xs text-blue-600">@term('transcription_in_progress_label')</div>
                @elseif($note->transcription_status === 'failed')
                <div class="mt-2 text-xs text-red-600">@term('transcription_failed_label')</div>
                @endif
            </div>
            @endif

            <!-- Structured Data -->
            @if($note->structured_data)
            <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                <div class="text-xs text-blue-600 font-medium mb-1">@term('extracted_information_label')</div>
                <div class="text-sm text-gray-700">
                    @foreach($note->structured_data as $key => $value)
                    <div><span class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span> {{ is_array($value) ? implode(', ', $value) : $value }}</div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p>@term('no_notes_yet_label')</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notes->hasPages())
    <div class="mt-4">
        {{ $notes->links() }}
    </div>
    @endif
</div>
