<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.cohorts.index') }}" class="hover:text-purple-600">@term('cohort_plural')</a>
                <span>/</span>
                <a href="{{ route('admin.cohorts.show', $cohort) }}" class="hover:text-purple-600">{{ $cohort->name }}</a>
                <span>/</span>
                <span>@term('drip_schedule_label')</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">@term('content_release_schedule_label')</h1>
            <p class="text-gray-600 mt-1">@term('configure_step_availability_label')</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.cohorts.show', $cohort) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                @term('back_to_cohort_label')
            </a>
            <button
                wire:click="saveSchedule"
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700"
            >
                @term('save_schedule_label')
            </button>
        </div>
    </div>

    <!-- Cohort Info Bar -->
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div>
                    <p class="text-sm text-gray-500">@term('cohort_singular')</p>
                    <p class="font-medium text-gray-900">{{ $cohort->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">@term('duration_label')</p>
                    <p class="font-medium text-gray-900">{{ $cohort->start_date->format('M d') }} - {{ $cohort->end_date->format('M d, Y') }} ({{ $cohortDays }} @term('days_label'))</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">@term('step_plural')</p>
                    <p class="font-medium text-gray-900">{{ count($schedule) }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">@term('drip_content_label')</span>
                <button
                    wire:click="toggleDrip"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 {{ $dripEnabled ? 'bg-purple-600' : 'bg-gray-200' }}"
                >
                    <span class="sr-only">@term('toggle_drip_content_label')</span>
                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $dripEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        </div>
    </div>

    @if($dripEnabled)
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">@term('quick_schedule_templates_label')</span>
                <div class="flex items-center space-x-2">
                    <button
                        wire:click="applyDailySchedule"
                        class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200"
                    >
                        @term('daily_label')
                    </button>
                    <button
                        wire:click="applyWeeklySchedule"
                        class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200"
                    >
                        @term('weekly_label')
                    </button>
                    <button
                        wire:click="applyBiweeklySchedule"
                        class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200"
                    >
                        @term('biweekly_label')
                    </button>
                    <button
                        wire:click="releaseAllNow"
                        class="px-3 py-1.5 text-xs font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100"
                    >
                        @term('release_all_now_label')
                    </button>
                </div>
            </div>
        </div>

        <!-- Visual Timeline -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('release_timeline_label')</h2>

            @if($maxScheduleDay > 0)
                <div class="relative mb-8">
                    <!-- Timeline bar -->
                    <div class="absolute top-4 left-0 right-0 h-1 bg-gray-200 rounded"></div>

                    <!-- Timeline markers -->
                    <div class="relative flex justify-between">
                        @foreach($sortedSchedule as $index => $item)
                            @php
                                $position = $maxScheduleDay > 0 ? ($item['days_after_start'] / $maxScheduleDay) * 100 : 0;
                                $isReleased = $this->isReleased($item['days_after_start']);
                            @endphp
                            <div
                                class="absolute transform -translate-x-1/2"
                                style="left: {{ $position }}%"
                            >
                                <div class="flex flex-col items-center">
                                    <div class="w-4 h-4 rounded-full {{ $isReleased ? 'bg-green-500' : 'bg-purple-500' }} border-2 border-white shadow"></div>
                                    <div class="mt-2 text-xs text-gray-500 whitespace-nowrap">@term('day_label') {{ $item['days_after_start'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Step List -->
            <div class="space-y-3">
                @foreach($schedule as $stepId => $item)
                    @php
                        $isReleased = $this->isReleased($item['days_after_start']);
                        $releaseDate = $this->getReleaseDate($item['days_after_start']);
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-4 {{ $isReleased ? 'bg-green-50 border-green-200' : '' }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <!-- Step number -->
                                <div class="flex-shrink-0 w-8 h-8 rounded-full {{ $isReleased ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700' }} flex items-center justify-center text-sm font-medium">
                                    {{ $item['order'] + 1 }}
                                </div>

                                <!-- Step info -->
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ $item['title'] }}</h3>
                                    <p class="text-sm text-gray-500">
                                        @if($isReleased)
                                            <span class="text-green-600">@term('released_label') {{ $releaseDate }}</span>
                                        @else
                                            @term('releases_label') {{ $releaseDate }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-4">
                                <!-- Days input -->
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600">@term('days_after_start_label')</label>
                                    <input
                                        type="number"
                                        min="0"
                                        value="{{ $item['days_after_start'] }}"
                                        wire:change="updateDays({{ $stepId }}, $event.target.value)"
                                        class="w-20 px-2 py-1 text-sm border border-gray-300 rounded-lg text-center"
                                    >
                                </div>

                                <!-- Options -->
                                <div class="flex items-center space-x-3">
                                    <label class="flex items-center space-x-1 text-sm text-gray-600 cursor-pointer" title="{{ app(\App\Services\TerminologyService::class)->get('require_previous_step_label') }}">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleRequirePrevious({{ $stepId }})"
                                            @checked($item['require_previous'])
                                            class="w-4 h-4 text-purple-600 border-gray-300 rounded"
                                        >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </label>
                                    <label class="flex items-center space-x-1 text-sm text-gray-600 cursor-pointer" title="{{ app(\App\Services\TerminologyService::class)->get('notify_participants_label') }}">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleNotify({{ $stepId }})"
                                            @checked($item['notify_learners'])
                                            class="w-4 h-4 text-purple-600 border-gray-300 rounded"
                                        >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Legend -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-3">@term('legend_label')</h3>
            <div class="flex items-center space-x-6 text-sm text-gray-600">
                <div class="flex items-center space-x-2">
                    <div class="w-4 h-4 bg-green-50 border border-green-200 rounded"></div>
                    <span>@term('released_label')</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>@term('requires_previous_step_label')</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span>@term('notify_on_release_label')</span>
                </div>
            </div>
        </div>
    @else
        <!-- Drip Disabled State -->
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">@term('drip_content_disabled_label')</h3>
            <p class="mt-1 text-gray-500">@term('drip_content_disabled_body')</p>
            <button
                wire:click="toggleDrip"
                class="mt-4 px-4 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100"
            >
                @term('enable_drip_content_label')
            </button>
        </div>
    @endif
</div>
