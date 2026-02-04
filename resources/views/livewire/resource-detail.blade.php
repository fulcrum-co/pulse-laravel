<div class="space-y-6">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Resources', 'url' => route('resources.index')],
        ['label' => 'Content', 'url' => route('resources.index') . '?activeTab=content'],
        ['label' => $resource->title],
    ]" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl font-semibold text-gray-900">{{ $resource->title }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $this->typeColor }}-100 text-{{ $this->typeColor }}-800">
                        {{ ucfirst($resource->resource_type) }}
                    </span>
                </div>
                <div class="flex items-center gap-3 mt-1 text-sm text-gray-500">
                    @if($resource->category)
                        <span>{{ ucfirst($resource->category) }}</span>
                        <span class="text-gray-300">·</span>
                    @endif
                    @if($resource->estimated_duration_minutes)
                        <span>{{ $resource->estimated_duration_minutes }} min</span>
                        <span class="text-gray-300">·</span>
                    @endif
                    <span>{{ $assignmentCount }} {{ Str::plural('assignment', $assignmentCount) }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{-- Share Button --}}
            <button
                wire:click="$dispatch('open-share-modal', { type: 'resource', id: {{ $resource->id }}, title: '{{ addslashes($resource->title) }}', isPublic: {{ $resource->is_public ? 'true' : 'false' }} })"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                title="Share & Embed"
            >
                <x-icon name="share" class="w-4 h-4 mr-2" />
                Share
            </button>
            @if($resource->url || $resource->file_path)
                <a
                    href="{{ $resource->url ?? Storage::url($resource->file_path) }}"
                    target="_blank"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    <x-icon name="arrow-top-right-on-square" class="w-4 h-4 mr-2" />
                    Open
                </a>
            @endif
            @if($canPush)
                <button
                    wire:click="openPushModal"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    title="Push to Schools"
                >
                    <x-icon name="arrow-up-on-square" class="w-4 h-4 mr-2" />
                    Push
                </button>
            @endif
            <button
                wire:click="openAssignModal"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
            >
                <x-icon name="user-plus" class="w-4 h-4 mr-2" />
                Assign
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Preview Area -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                @if($this->previewType === 'video_embed')
                    <div class="aspect-video">
                        <iframe
                            src="{{ $this->videoEmbedUrl }}"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>
                @elseif($this->previewType === 'video_file')
                    <div class="aspect-video bg-black">
                        <video
                            src="{{ Storage::url($resource->file_path) }}"
                            class="w-full h-full"
                            controls
                        ></video>
                    </div>
                @elseif($this->previewType === 'pdf')
                    <div class="h-[600px]">
                        <iframe
                            src="{{ Storage::url($resource->file_path) }}"
                            class="w-full h-full"
                            frameborder="0"
                        ></iframe>
                    </div>
                @elseif($this->previewType === 'image')
                    <div class="p-4">
                        <img
                            src="{{ Storage::url($resource->file_path) }}"
                            alt="{{ $resource->title }}"
                            class="max-w-full h-auto mx-auto rounded-lg"
                        />
                    </div>
                @elseif($this->previewType === 'audio')
                    <div class="p-8 flex flex-col items-center justify-center">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <x-icon name="musical-note" class="w-12 h-12 text-gray-400" />
                        </div>
                        <audio
                            src="{{ Storage::url($resource->file_path) }}"
                            controls
                            class="w-full max-w-md"
                        ></audio>
                    </div>
                @elseif($this->previewType === 'link')
                    <a
                        href="{{ $resource->url }}"
                        target="_blank"
                        class="block p-8 text-center hover:bg-gray-50 transition-colors"
                    >
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-icon name="link" class="w-8 h-8 text-gray-400" />
                        </div>
                        <p class="text-sm text-gray-500 mb-2">External Link</p>
                        <p class="text-pulse-orange-600 font-medium truncate max-w-md mx-auto">{{ $resource->url }}</p>
                        <p class="mt-4 text-sm text-gray-400">Click to open in a new tab</p>
                    </a>
                @else
                    <div class="p-16 text-center">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-icon name="{{ $this->typeIcon }}" class="w-10 h-10 text-gray-400" />
                        </div>
                        <p class="text-gray-500">No preview available for this resource</p>
                    </div>
                @endif
            </div>

            <!-- Description -->
            @if($resource->description)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-sm font-medium text-gray-900 mb-3">Description</h2>
                <p class="text-gray-600 whitespace-pre-wrap">{{ $resource->description }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Metadata -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-sm font-medium text-gray-900 mb-4">Details</h2>

                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Type</dt>
                        <dd class="mt-1 flex items-center gap-2">
                            <x-icon name="{{ $this->typeIcon }}" class="w-4 h-4 text-{{ $this->typeColor }}-500" />
                            <span class="text-sm text-gray-900">{{ ucfirst($resource->resource_type) }}</span>
                        </dd>
                    </div>

                    @if($resource->category)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Category</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($resource->category) }}</dd>
                    </div>
                    @endif

                    @if($resource->estimated_duration_minutes)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $resource->estimated_duration_minutes }} minutes</dd>
                    </div>
                    @endif

                    @if($resource->target_grades && count($resource->target_grades) > 0)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Target Grades</dt>
                        <dd class="mt-1 flex flex-wrap gap-1">
                            @foreach($resource->target_grades as $grade)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $grade }}
                                </span>
                            @endforeach
                        </dd>
                    </div>
                    @endif

                    @if($resource->target_risk_levels && count($resource->target_risk_levels) > 0)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Target Risk Levels</dt>
                        <dd class="mt-1 flex flex-wrap gap-1">
                            @foreach($resource->target_risk_levels as $level)
                                @php
                                    $levelColor = match($level) {
                                        'high' => 'red',
                                        'moderate' => 'yellow',
                                        'low' => 'green',
                                        default => 'gray',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $levelColor }}-100 text-{{ $levelColor }}-800">
                                    {{ ucfirst($level) }}
                                </span>
                            @endforeach
                        </dd>
                    </div>
                    @endif

                    @if($resource->tags && count($resource->tags) > 0)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tags</dt>
                        <dd class="mt-1 flex flex-wrap gap-1">
                            @foreach($resource->tags as $tag)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </dd>
                    </div>
                    @endif

                    <div class="pt-4 border-t border-gray-100">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $resource->created_at->format('M d, Y') }}</dd>
                    </div>

                    @if($resource->creator)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Added By</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $resource->creator->name }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Visibility Settings -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-sm font-medium text-gray-900 mb-3">Visibility</h2>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-700">Public Access</p>
                        <p class="text-xs text-gray-500">Allow in public hub & embeds</p>
                    </div>
                    <button
                        wire:click="togglePublic"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2 {{ $resource->is_public ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                        role="switch"
                        aria-checked="{{ $resource->is_public ? 'true' : 'false' }}"
                    >
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $resource->is_public ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
                @if($resource->is_public)
                    <div class="mt-3 p-2 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-xs text-green-700">
                            <x-icon name="globe-alt" class="w-3 h-3 inline-block mr-1" />
                            This resource is publicly visible
                        </p>
                    </div>
                @endif
            </div>

            <!-- Quick Assign -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-sm font-medium text-gray-900 mb-3">Quick Assign</h2>
                <p class="text-xs text-gray-500 mb-4">Assign this resource to students or a contact list.</p>
                <button
                    wire:click="openAssignModal"
                    class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-pulse-orange-600 bg-pulse-orange-50 rounded-lg hover:bg-pulse-orange-100"
                >
                    <x-icon name="user-plus" class="w-4 h-4 mr-2" />
                    Assign to Students
                </button>
            </div>

            <!-- Related Resources -->
            @if($relatedResources->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-sm font-medium text-gray-900 mb-4">Related Resources</h2>
                <div class="space-y-3">
                    @foreach($relatedResources as $related)
                        @php
                            $relatedColor = match($related->resource_type) {
                                'article' => 'blue',
                                'video' => 'red',
                                'worksheet' => 'green',
                                'activity' => 'purple',
                                'link' => 'orange',
                                'document' => 'gray',
                                default => 'gray',
                            };
                            $relatedIcon = match($related->resource_type) {
                                'article' => 'document-text',
                                'video' => 'play-circle',
                                'worksheet' => 'clipboard-document-list',
                                'activity' => 'puzzle-piece',
                                'link' => 'link',
                                'document' => 'document',
                                default => 'document',
                            };
                        @endphp
                        <a
                            href="{{ route('resources.show', $related) }}"
                            class="block p-3 rounded-lg border border-gray-100 hover:border-gray-200 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded bg-{{ $relatedColor }}-100 flex items-center justify-center flex-shrink-0">
                                    <x-icon name="{{ $relatedIcon }}" class="w-4 h-4 text-{{ $relatedColor }}-600" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $related->title }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst($related->resource_type) }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Assign Modal -->
    @if($showAssignModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAssignModal"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Assign Resource</h3>
                    <button wire:click="closeAssignModal" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                        <x-icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                <p class="text-sm text-gray-500 mb-4">
                    Assign "{{ $resource->title }}" to a student or contact list.
                </p>

                <!-- Assignment Type Toggle -->
                <div class="flex gap-2 mb-4">
                    <button
                        type="button"
                        wire:click="$set('assignType', 'student')"
                        class="flex-1 p-3 rounded-lg border-2 text-center transition-all
                            {{ $assignType === 'student' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                    >
                        <x-icon name="user" class="w-5 h-5 mx-auto mb-1 {{ $assignType === 'student' ? 'text-pulse-orange-600' : 'text-gray-400' }}" />
                        <span class="text-sm font-medium {{ $assignType === 'student' ? 'text-pulse-orange-600' : 'text-gray-700' }}">Individual</span>
                    </button>
                    <button
                        type="button"
                        wire:click="$set('assignType', 'list')"
                        class="flex-1 p-3 rounded-lg border-2 text-center transition-all
                            {{ $assignType === 'list' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                    >
                        <x-icon name="user-group" class="w-5 h-5 mx-auto mb-1 {{ $assignType === 'list' ? 'text-pulse-orange-600' : 'text-gray-400' }}" />
                        <span class="text-sm font-medium {{ $assignType === 'list' ? 'text-pulse-orange-600' : 'text-gray-700' }}">Contact List</span>
                    </button>
                </div>

                <div class="space-y-4">
                    @if($assignType === 'student')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Student</label>
                        <select
                            wire:model="selectedStudentId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                            <option value="">Choose a student...</option>
                            @foreach($this->students as $student)
                                <option value="{{ $student->id }}">{{ $student->full_name }} ({{ $student->grade_level }})</option>
                            @endforeach
                        </select>
                        @error('selectedStudentId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Contact List</label>
                        <select
                            wire:model="selectedListId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                            <option value="">Choose a list...</option>
                            @foreach($this->contactLists as $list)
                                <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->member_count }} students)</option>
                            @endforeach
                        </select>
                        @error('selectedListId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
                        <textarea
                            wire:model="assignNote"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="Add a note for the student..."
                        ></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="closeAssignModal"
                        class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="assignResource"
                        class="px-4 py-2 text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        Assign
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Push Content Modal -->
    @livewire('push-content-modal')

    <!-- Share Modal -->
    @livewire('components.share-modal')
</div>
