<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => 'Resources', 'url' => route('resources.index')],
        ['label' => 'Providers', 'url' => route('resources.index') . '?activeTab=providers'],
        ['label' => $provider->name],
    ]" />

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 px-6 py-8">
            <div class="flex items-start gap-6">
                <!-- Avatar -->
                <div class="w-24 h-24 rounded-full bg-white/20 flex items-center justify-center text-white text-3xl font-bold">
                    {{ substr($provider->name, 0, 1) }}
                </div>
                <div class="flex-1 text-white">
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl font-bold">{{ $provider->name }}</h1>
                        @if($provider->verified_at)
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20" title="Verified">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        @endif
                    </div>
                    <p class="text-purple-100 mb-2">{{ ucfirst($provider->provider_type) }}</p>
                    @if($provider->credentials)
                    <p class="text-purple-200 text-sm">{{ $provider->credentials }}</p>
                    @endif
                </div>
                @if($provider->ratings_average)
                <div class="text-center">
                    <div class="text-3xl font-bold text-white">{{ number_format($provider->ratings_average, 1) }}</div>
                    <div class="flex items-center justify-center text-yellow-300">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= round($provider->ratings_average) ? 'fill-current' : 'fill-purple-400' }}" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        @endfor
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- Bio -->
            @if($provider->bio)
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">About</h2>
                <p class="text-gray-600">{{ $provider->bio }}</p>
            </div>
            @endif

            <!-- Specialties -->
            @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Specialties</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($provider->specialty_areas as $specialty)
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">{{ $specialty }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Availability -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Availability</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            @if($provider->serves_remote)
                            <span class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Remote Sessions Available
                            </span>
                            @else
                            <span class="flex items-center text-gray-400">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                No Remote Sessions
                            </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($provider->serves_in_person)
                            <span class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                In-Person Sessions Available
                            </span>
                            @else
                            <span class="flex items-center text-gray-400">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                No In-Person Sessions
                            </span>
                            @endif
                        </div>
                        @if($provider->availability_notes)
                        <p class="text-gray-600 mt-2">{{ $provider->availability_notes }}</p>
                        @endif
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Pricing & Insurance</h2>
                    <div class="space-y-2 text-sm">
                        @if($provider->hourly_rate)
                        <p class="text-gray-600"><span class="font-medium">${{ number_format($provider->hourly_rate) }}</span> per hour</p>
                        @endif
                        <div class="flex items-center gap-2">
                            @if($provider->accepts_insurance)
                            <span class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Accepts Insurance
                            </span>
                            @else
                            <span class="text-gray-500">Does not accept insurance</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                <div class="flex flex-wrap gap-4">
                    <!-- Push to Organizations -->
                    @if($canPush)
                    <button
                        wire:click="openPushModal"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        title="Push to Organizations"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Push to Organizations
                    </button>
                    @endif

                    <!-- Assign to Learner -->
                    <button
                        wire:click="openAssignModal"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        title="Assign to Learner"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Assign to Learner
                        @if($assignmentCount > 0)
                        <span class="ml-2 px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full">{{ $assignmentCount }}</span>
                        @endif
                    </button>

                    <!-- Primary CTA: Message Provider -->
                    {{-- All provider communication must go through in-app messaging for security --}}
                    <button
                        wire:click="messageProvider"
                        class="inline-flex items-center px-6 py-3 bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors font-medium shadow-sm"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Message Provider
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Modal -->
    @if($showAssignModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAssignModal"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Assign Provider
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">Assign {{ $provider->name }} to a learner or list.</p>

                            <div class="mt-4 space-y-4">
                                <!-- Assignment Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Assign to</label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center">
                                            <input type="radio" wire:model.live="assignType" value="learner" class="mr-2">
                                            Individual Learner
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" wire:model.live="assignType" value="list" class="mr-2">
                                            Contact List
                                        </label>
                                    </div>
                                </div>

                                <!-- Learner Select -->
                                @if($assignType === 'learner')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Learner</label>
                                    <select wire:model="selectedLearnerId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                        <option value="">Choose a learner...</option>
                                        @foreach($learners as $learner)
                                        <option value="{{ $learner->id }}">{{ $learner->user?->name ?? 'Learner #' . $learner->id }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- List Select -->
                                @if($assignType === 'list')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Contact List</label>
                                    <select wire:model="selectedListId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                                        <option value="">Choose a list...</option>
                                        @foreach($contactLists as $list)
                                        <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->learners_count ?? $list->learners->count() }} learners)</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- Note -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Note (optional)</label>
                                    <textarea wire:model="assignNote" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500" placeholder="Add a note about this assignment..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button
                        wire:click="assignProvider"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Assign Provider
                    </button>
                    <button
                        wire:click="closeAssignModal"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
