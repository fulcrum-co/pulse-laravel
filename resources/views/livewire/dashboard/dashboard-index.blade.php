@php
    $terminology = app(\App\Services\TerminologyService::class);
@endphp

<div>
    <!-- Dashboard Header -->
    <div class="mb-6" data-help="dashboard-header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Dashboard Selector -->
            <div class="flex items-center gap-3" data-help="dashboard-selector">
                <div class="relative" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <span class="font-medium text-gray-900">{{ $currentDashboard?->name ?? $terminology->get('select_dashboard_label') }}</span>
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition
                        class="absolute left-0 z-50 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200"
                    >
                        <!-- My Dashboards -->
                        <div class="p-2 border-b border-gray-100">
                            <div class="px-3 py-1 text-xs font-semibold text-gray-500 uppercase">@term('my_dashboards_label')</div>
                            @foreach($this->myDashboards as $dashboard)
                                <button
                                    wire:click="selectDashboard({{ $dashboard->id }})"
                                    @click="open = false"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-left rounded-md hover:bg-gray-100 {{ $selectedDashboardId === $dashboard->id ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700' }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                    </svg>
                                    <span>{{ $dashboard->name }}</span>
                                    @if($dashboard->is_default)
                                        <span class="ml-auto text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded">@term('default_label')</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>

                        <!-- Shared Dashboards -->
                        @if($this->sharedDashboards->count() > 0)
                            <div class="p-2 border-b border-gray-100">
                                <div class="px-3 py-1 text-xs font-semibold text-gray-500 uppercase">@term('shared_with_me_label')</div>
                                @foreach($this->sharedDashboards as $dashboard)
                                    <button
                                        wire:click="selectDashboard({{ $dashboard->id }})"
                                        @click="open = false"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left rounded-md hover:bg-gray-100 {{ $selectedDashboardId === $dashboard->id ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700' }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span>{{ $dashboard->name }}</span>
                                        <span class="ml-auto text-xs text-gray-500">@term('by_label') {{ $dashboard->user->first_name ?? $terminology->get('unknown_label') }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <!-- Create New -->
                        <div class="p-2">
                            <button
                                wire:click="openCreateModal"
                                @click="open = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-left text-indigo-600 rounded-md hover:bg-indigo-50"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <span class="font-medium">@term('create_dashboard_label')</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Bar -->
            <div class="flex items-center gap-2" data-help="dashboard-actions">
                <!-- Date Range Picker -->
                <div class="flex items-center gap-1 bg-white border border-gray-300 rounded-lg p-1" data-help="date-range">
                    <button
                        wire:click="setDateRange('week')"
                        class="px-3 py-1.5 text-sm rounded-md {{ $dateRange === 'week' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}"
                    >
                        @term('week_label')
                    </button>
                    <button
                        wire:click="setDateRange('month')"
                        class="px-3 py-1.5 text-sm rounded-md {{ $dateRange === 'month' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}"
                    >
                        @term('month_label')
                    </button>
                    <button
                        wire:click="setDateRange('quarter')"
                        class="px-3 py-1.5 text-sm rounded-md {{ $dateRange === 'quarter' ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600 hover:bg-gray-100' }}"
                    >
                        @term('quarter_label')
                    </button>
                </div>

                <!-- Add Widget Button -->
                <button
                    wire:click="openWidgetPanel"
                    class="flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>@term('add_widget_label')</span>
                </button>

                <!-- Actions Menu -->
                <div class="relative" x-data="{ open: false }">
                    <button
                        @click="open = !open"
                        class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        @click.outside="open = false"
                        x-transition
                        class="absolute right-0 z-50 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200"
                    >
                        <div class="py-1">
                            <button
                                wire:click="setAsDefault"
                                @click="open = false"
                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                                @term('set_as_default_label')
                            </button>
                            <button
                                wire:click="toggleShare"
                                @click="open = false"
                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                </svg>
                                {{ $currentDashboard?->is_shared ? $terminology->get('make_private_label') : $terminology->get('share_with_org_label') }}
                            </button>
                            <hr class="my-1">
                            <button
                                wire:click="deleteDashboard"
                                wire:confirm="{{ $terminology->get('delete_dashboard_confirm_label') }}"
                                @click="open = false"
                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                @term('delete_dashboard_label')
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Widgets Grid -->
    <div class="grid grid-cols-12 gap-6" data-help="widgets-grid">
        @forelse($widgets as $widget)
            @php
                $position = $widget->position ?? ['w' => 4, 'h' => 2];
                $colSpan = min($position['w'] ?? 4, 12);
            @endphp
            <div class="col-span-12 md:col-span-{{ $colSpan }} bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                <!-- Widget Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h3 class="font-medium text-gray-900">{{ $widget->title }}</h3>
                    <div class="flex items-center gap-1">
                        <button
                            wire:click="editWidget({{ $widget->id }})"
                            class="p-1 text-gray-400 hover:text-gray-600 rounded"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button
                            wire:click="deleteWidget({{ $widget->id }})"
                            wire:confirm="{{ $terminology->get('remove_widget_confirm_label') }}"
                            class="p-1 text-gray-400 hover:text-red-600 rounded"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Widget Content -->
                <div class="p-4">
                    @php $data = $widget->getData($orgId); @endphp

                    @switch($widget->widget_type)
                        @case('metric_card')
                            <div class="text-center">
                                <div class="text-4xl font-bold text-gray-900">{{ $data['formatted_value'] ?? '0' }}</div>
                                @if(isset($data['change']))
                                    <div class="mt-2 flex items-center justify-center gap-1 text-sm {{ $data['change'] > 0 ? 'text-green-600' : ($data['change'] < 0 ? 'text-red-600' : 'text-gray-500') }}">
                                        @if($data['change'] > 0)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                                        @elseif($data['change'] < 0)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                        @endif
                                        {{ abs($data['change']) }}% @term('from_last_period_label')
                                    </div>
                                @endif
                            </div>
                            @break

                        @case('bar_chart')
                            <div class="h-48" x-data="barChart(@js($data['data'] ?? []))" x-init="init()">
                                <canvas x-ref="canvas"></canvas>
                            </div>
                            @break

                        @case('line_chart')
                            <div class="h-48" x-data="lineChart(@js($data['data'] ?? []))" x-init="init()">
                                <canvas x-ref="canvas"></canvas>
                            </div>
                            @break

                        @case('learner_list')
                            <div class="space-y-3">
                                @forelse($data['participants'] ?? [] as $participant)
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                                            @if($participant['avatar_url'])
                                                <img src="{{ $participant['avatar_url'] }}" alt="" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-500 text-sm font-medium">
                                                    {{ substr($participant['name'], 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-gray-900 truncate">{{ $participant['name'] }}</div>
                                            <div class="text-sm text-gray-500">@term('level_label') {{ $participant['level'] }}</div>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full {{ $participant['risk_level'] === 'high' ? 'bg-red-100 text-red-700' : ($participant['risk_level'] === 'low' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                            {{ $terminology->get('risk_' . $participant['risk_level'] . '_label') }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-center text-gray-500 py-4">@term('no_participants_found_label')</div>
                                @endforelse
                            </div>
                            @break

                        @case('survey_summary')
                            <div class="space-y-3">
                                @forelse($data['surveys'] ?? [] as $survey)
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-gray-900 truncate">{{ $survey['title'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $survey['completed_count'] }} / {{ $survey['attempts_count'] }} @term('completed_label')</div>
                                        </div>
                                        <div class="text-sm font-medium {{ $survey['completion_rate'] >= 80 ? 'text-green-600' : ($survey['completion_rate'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $survey['completion_rate'] }}%
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-gray-500 py-4">@term('no_surveys_found_label')</div>
                                @endforelse
                            </div>
                            @break

                        @case('alert_feed')
                            <div class="space-y-3">
                                @forelse($data['executions'] ?? [] as $execution)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $execution['status'] === 'completed' ? 'bg-green-100' : ($execution['status'] === 'failed' ? 'bg-red-100' : 'bg-yellow-100') }}">
                                            <svg class="w-4 h-4 {{ $execution['status'] === 'completed' ? 'text-green-600' : ($execution['status'] === 'failed' ? 'text-red-600' : 'text-yellow-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-gray-900 truncate">{{ $execution['workflow_name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $execution['started_at'] }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-gray-500 py-4">
                                        {{ $data['message'] ?? $terminology->get('no_recent_alerts_label') }}
                                    </div>
                                @endforelse
                            </div>
                            @break

                        @case('notification_feed')
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @forelse($data['notifications'] ?? [] as $notification)
                                    <a href="{{ $notification['url'] }}"
                                       class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                            {{ $notification['status'] === 'warning' ? 'bg-yellow-100 text-yellow-600' :
                                               ($notification['status'] === 'completed' ? 'bg-green-100 text-green-600' :
                                               ($notification['status'] === 'failed' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600')) }}">
                                            @if($notification['icon'] === 'bell')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                                </svg>
                                            @elseif($notification['icon'] === 'user')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $notification['title'] }}</div>
                                            <div class="text-xs text-gray-500">{{ $notification['subtitle'] }}</div>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                @empty
                                    <div class="text-center text-gray-500 py-4">
                                        <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        @term('no_notifications_label')
                                    </div>
                                @endforelse
                            </div>
                            @break

                        @default
                            <div class="text-center text-gray-500 py-4">@term('widget_type_not_supported_label')</div>
                    @endswitch
                </div>
            </div>
        @empty
            <div class="col-span-12 text-center py-12 bg-white rounded-lg border-2 border-dashed border-gray-300">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">@term('no_widgets_label')</h3>
                <p class="mt-1 text-sm text-gray-500">@term('add_widget_empty_help_label')</p>
                <div class="mt-6">
                    <button
                        wire:click="openWidgetPanel"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                    >
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        @term('add_widget_label')
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Create Dashboard Modal -->
    <div
        x-data="{ show: @entangle('showCreateModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
    >
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="show" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div
                x-show="show"
                x-transition
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6"
            >
                <h3 class="text-lg font-medium text-gray-900 mb-4">@term('create_dashboard_label')</h3>

                <form wire:submit="createDashboard">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('dashboard_name_label')</label>
                            <input
                                type="text"
                                wire:model="newDashboardName"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="{{ $terminology->get('dashboard_name_placeholder') }}"
                            >
                            @error('newDashboardName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('description_optional_label')</label>
                            <textarea
                                wire:model="newDashboardDescription"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="{{ $terminology->get('dashboard_description_placeholder') }}"
                            ></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                wire:model="createFromTemplate"
                                id="createFromTemplate"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                            <label for="createFromTemplate" class="text-sm text-gray-700">
                                @term('start_with_default_widgets_label')
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="$set('showCreateModal', false)"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg"
                        >
                            @term('cancel_label')
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                        >
                            @term('create_dashboard_label')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Widget Panel -->
    <div
        x-data="{ show: @entangle('showWidgetPanel') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-hidden"
    >
        <div class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="show = false"></div>

        <div class="absolute inset-y-0 right-0 max-w-md w-full">
            <div
                x-show="show"
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="h-full bg-white shadow-xl flex flex-col"
            >
                <!-- Panel Header -->
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">@term('add_widget_label')</h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Widget Types -->
                <div class="flex-1 overflow-y-auto p-6">
                    @if(!$newWidgetType)
                        <p class="text-sm text-gray-500 mb-4">@term('choose_widget_type_help_label')</p>

                        <div class="space-y-3">
                            @foreach($this->widgetTypes as $type => $info)
                                <button
                                    wire:click="selectWidgetType('{{ $type }}')"
                                    class="w-full flex items-start gap-4 p-4 border border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors text-left"
                                >
                                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $info['label'] }}</div>
                                        <div class="text-sm text-gray-500">{{ $info['description'] }}</div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <!-- Widget Configuration -->
                        <div class="space-y-4">
                            <button
                                wire:click="$set('newWidgetType', '')"
                                class="flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                @term('back_to_widget_types_label')
                            </button>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@term('widget_title_label')</label>
                                <input
                                    type="text"
                                    wire:model="newWidgetTitle"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>

                            @if($newWidgetType === 'metric_card')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">@term('data_source_label')</label>
                                    <select
                                        wire:model="newWidgetConfig.data_source"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="learners_total">@term('total_participants_label')</option>
                                        <option value="learners_at_risk">@term('at_risk_participants_label')</option>
                                        <option value="learners_good">@term('participants_good_standing_label')</option>
                                        <option value="surveys_active">@term('active_surveys_label')</option>
                                        <option value="responses_today">@term('responses_today_label')</option>
                                        <option value="responses_week">@term('responses_this_week_label')</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">@term('color_label')</label>
                                    <select
                                        wire:model="newWidgetConfig.color"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="blue">@term('color_blue_label')</option>
                                        <option value="green">@term('color_green_label')</option>
                                        <option value="yellow">@term('color_yellow_label')</option>
                                        <option value="red">@term('color_red_label')</option>
                                        <option value="indigo">@term('color_indigo_label')</option>
                                    </select>
                                </div>
                            @endif

                            @if($newWidgetType === 'learner_list')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">@term('filter_label')</label>
                                    <select
                                        wire:model="newWidgetConfig.filter"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="high_risk">@term('high_risk_participants_label')</option>
                                        <option value="low_risk">@term('low_risk_participants_label')</option>
                                        <option value="good">@term('participants_good_standing_label')</option>
                                        <option value="all">@term('all_participants_label')</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">@term('limit_label')</label>
                                    <input
                                        type="number"
                                        wire:model="newWidgetConfig.limit"
                                        min="1"
                                        max="20"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                </div>
                            @endif

                            @if($newWidgetType === 'bar_chart')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">@term('data_source_label')</label>
                                    <select
                                        wire:model="newWidgetConfig.data_source"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option value="survey_responses_weekly">@term('survey_responses_weekly_label')</option>
                                        <option value="risk_distribution">@term('risk_level_distribution_label')</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        wire:model="newWidgetConfig.compare"
                                        id="compareData"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    <label for="compareData" class="text-sm text-gray-700">
                                        @term('compare_previous_period_label')
                                    </label>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Panel Footer -->
                @if($newWidgetType)
                    <div class="px-6 py-4 border-t border-gray-200">
                        <button
                            wire:click="addWidget"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                        >
                            @term('add_widget_label')
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Edit Widget Modal -->
    <div
        x-data="{ show: @entangle('showEditWidgetModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
    >
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="show" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div
                x-show="show"
                x-transition
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6"
            >
                <h3 class="text-lg font-medium text-gray-900 mb-4">@term('edit_widget_label')</h3>

                <form wire:submit="updateWidget">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('widget_title_label')</label>
                            <input
                                type="text"
                                wire:model="editWidgetTitle"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            >
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="$set('showEditWidgetModal', false)"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg"
                        >
                            @term('cancel_label')
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                        >
                            @term('save_changes_label')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function barChart(data) {
        return {
            chart: null,
            init() {
                // Destroy existing chart if present
                const existingChart = Chart.getChart(this.$refs.canvas);
                if (existingChart) {
                    existingChart.destroy();
                }
                const ctx = this.$refs.canvas.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [
                            {
                                label: @js($terminology->get('this_week_label')),
                                data: data.map(d => d.value),
                                backgroundColor: 'rgba(79, 70, 229, 0.8)',
                            },
                            ...(data[0]?.compare_value !== undefined ? [{
                                label: @js($terminology->get('last_week_label')),
                                data: data.map(d => d.compare_value),
                                backgroundColor: 'rgba(79, 70, 229, 0.3)',
                            }] : [])
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: data[0]?.compare_value !== undefined,
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    }

    function lineChart(data) {
        return {
            chart: null,
            init() {
                // Destroy existing chart if present
                const existingChart = Chart.getChart(this.$refs.canvas);
                if (existingChart) {
                    existingChart.destroy();
                }
                const ctx = this.$refs.canvas.getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(d => d.label),
                        datasets: [{
                            label: @js($terminology->get('responses_label')),
                            data: data.map(d => d.value),
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
    }
</script>
@endpush
