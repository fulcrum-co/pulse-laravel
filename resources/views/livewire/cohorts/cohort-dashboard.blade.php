<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">@term('my_learning_label')</h1>
        <p class="text-gray-600 mt-1">@term('track_progress_body_label')</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
            <p class="text-3xl font-bold">{{ $stats['in_progress'] }}</p>
            <p class="text-purple-100 text-sm">@term('in_progress_label')</p>
        </div>
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <p class="text-3xl font-bold text-gray-900">{{ $stats['completed'] }}</p>
            <p class="text-gray-500 text-sm">@term('completed_label')</p>
        </div>
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <p class="text-3xl font-bold text-gray-900">{{ $stats['total_enrolled'] }}</p>
            <p class="text-gray-500 text-sm">@term('total_enrolled_label')</p>
        </div>
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <p class="text-3xl font-bold text-gray-900">{{ floor($stats['total_time_spent'] / 3600) }}h</p>
            <p class="text-gray-500 text-sm">@term('time_invested_label')</p>
        </div>
    </div>

    <!-- Active Cohorts -->
    @if($activeCohorts->count() > 0)
    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('continue_learning_label')</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($activeCohorts as $membership)
            <a href="{{ route('learn.cohort', $membership->cohort) }}" class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="font-medium text-gray-900">{{ $membership->cohort->course?->title }}</h3>
                        <p class="text-sm text-gray-500">{{ $membership->cohort->name }}</p>
                    </div>
                    @if($membership->cohort->course?->badge_enabled)
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    @endif
                </div>

                <div class="mb-3">
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600">@term('progress_label')</span>
                        <span class="font-medium text-gray-900">{{ $membership->progress_percent }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full transition-all" style="width: {{ $membership->progress_percent }}%"></div>
                    </div>
                </div>

                @if($membership->currentStep)
                <p class="text-xs text-gray-500">
                    @term('current_label'): {{ $membership->currentStep->title }}
                </p>
                @endif

                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $membership->cohort->start_date->format('M d') }} - {{ $membership->cohort->end_date->format('M d, Y') }}</span>
                    <span class="text-purple-600 font-medium">@term('continue_arrow_label')</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Upcoming Cohorts -->
    @if($upcomingCohorts->count() > 0)
    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('upcoming_label')</h2>
        <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-200">
            @foreach($upcomingCohorts as $membership)
            <div class="p-4 flex items-center justify-between">
                <div>
                    <h3 class="font-medium text-gray-900">{{ $membership->cohort->course?->title }}</h3>
                    <p class="text-sm text-gray-500">{{ $membership->cohort->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">@term('starts_label') {{ $membership->cohort->start_date->format('M d, Y') }}</p>
                    <p class="text-xs text-gray-500">{{ $membership->cohort->start_date->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Completed Cohorts -->
    @if($completedCohorts->count() > 0)
    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('completed_label')</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($completedCohorts as $membership)
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-medium text-gray-900">{{ $membership->cohort->course?->title }}</h3>
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-500 mb-3">{{ $membership->cohort->name }}</p>
                <p class="text-xs text-gray-400">@term('completed_label') {{ $membership->completed_at?->format('M d, Y') }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Available to Join -->
    @if($availableCohorts->count() > 0)
    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('available_label') @term('cohort_plural')</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($availableCohorts as $cohort)
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <h3 class="font-medium text-gray-900 mb-1">{{ $cohort->course?->title }}</h3>
                <p class="text-sm text-gray-500 mb-3">{{ $cohort->name }}</p>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">@term('starts_label') {{ $cohort->start_date->format('M d') }}</span>
                    <a href="{{ route('learn.cohort', $cohort) }}" class="px-3 py-1 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100">
                        @term('enroll_action')
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Empty State -->
    @if($activeCohorts->count() === 0 && $completedCohorts->count() === 0 && $upcomingCohorts->count() === 0)
    <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">@term('no_label') @term('cohort_plural') @term('yet_label')</h3>
        <p class="mt-1 text-gray-500">@term('no_cohort_enrollments_yet_label')</p>
        @if($availableCohorts->count() > 0)
        <p class="mt-4 text-sm text-gray-600">@term('available_cohorts_empty_help_label')</p>
        @endif
    </div>
    @endif
</div>
