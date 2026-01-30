<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Back Link -->
    <a href="{{ route('resources.index') }}?activeTab=programs" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Programs
    </a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 px-6 py-8">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 bg-white/20 text-white text-sm rounded-full">
                            {{ ucfirst(str_replace('_', ' ', $program->program_type)) }}
                        </span>
                        @if($program->cost_structure === 'free')
                        <span class="px-3 py-1 bg-green-400/30 text-white text-sm rounded-full">Free</span>
                        @endif
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">{{ $program->name }}</h1>
                    @if($program->provider_org_name)
                    <p class="text-green-100">by {{ $program->provider_org_name }}</p>
                    @endif
                </div>
                <div class="text-right text-white">
                    @if($program->duration_weeks)
                    <div class="text-2xl font-bold">{{ $program->duration_weeks }}</div>
                    <div class="text-green-200 text-sm">weeks</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- Description -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">About This Program</h2>
                <p class="text-gray-600">{{ $program->description }}</p>
            </div>

            <!-- Key Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Location Type -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-sm mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Location
                    </div>
                    <div class="font-medium text-gray-900">{{ ucfirst(str_replace('_', '-', $program->location_type)) }}</div>
                </div>

                <!-- Cost -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-sm mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Cost
                    </div>
                    <div class="font-medium text-gray-900">
                        @switch($program->cost_structure)
                            @case('free')
                                <span class="text-green-600">Free</span>
                                @break
                            @case('sliding_scale')
                                Sliding Scale
                                @break
                            @case('insurance')
                                Insurance Accepted
                                @break
                            @default
                                Paid Program
                        @endswitch
                    </div>
                </div>

                <!-- Duration -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-sm mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Duration
                    </div>
                    <div class="font-medium text-gray-900">
                        @if($program->duration_weeks)
                            {{ $program->duration_weeks }} weeks
                        @else
                            Ongoing
                        @endif
                    </div>
                </div>
            </div>

            <!-- Target Needs -->
            @if($program->target_needs && count($program->target_needs) > 0)
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Areas of Focus</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($program->target_needs as $need)
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">{{ $need }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Eligibility -->
            @if($program->eligibility_criteria && count($program->eligibility_criteria) > 0)
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Eligibility Requirements</h2>
                <ul class="list-disc list-inside text-gray-600 space-y-1">
                    @foreach($program->eligibility_criteria as $criterion)
                    <li>{{ $criterion }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Enrollment -->
            @if($program->enrollment_url)
            <div class="border-t border-gray-200 pt-6">
                <a href="{{ $program->enrollment_url }}" target="_blank" class="inline-flex items-center px-6 py-3 bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Learn More & Enroll
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
