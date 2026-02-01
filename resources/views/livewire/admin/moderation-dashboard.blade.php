<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Moderation Dashboard</h1>
                    <p class="text-gray-500 mt-1">Monitor team performance and queue health</p>
                </div>

                <div class="flex items-center space-x-4">
                    {{-- Time Range Selector --}}
                    <div class="flex bg-gray-100 rounded-lg p-1">
                        <button wire:click="setTimeRange('24h')"
                                class="px-3 py-1 text-sm font-medium rounded-md transition-colors
                                    {{ $timeRange === '24h' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                                title="Last 24 hours">
                            24h
                        </button>
                        <button wire:click="setTimeRange('7d')"
                                class="px-3 py-1 text-sm font-medium rounded-md transition-colors
                                    {{ $timeRange === '7d' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                                title="Last 7 days">
                            7d
                        </button>
                        <button wire:click="setTimeRange('30d')"
                                class="px-3 py-1 text-sm font-medium rounded-md transition-colors
                                    {{ $timeRange === '30d' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}"
                                title="Last 30 days">
                            30d
                        </button>
                    </div>

                    {{-- Navigation --}}
                    <a href="{{ route('admin.moderation') }}"
                       class="px-4 py-2 text-gray-700 hover:text-gray-900 flex items-center text-sm"
                       title="View moderation queue">
                        <x-icon name="queue-list" class="w-5 h-5 mr-2" />
                        Queue
                    </a>
                    <a href="{{ route('admin.moderation.task-flow') }}"
                       class="px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors flex items-center text-sm"
                       title="Start reviewing items in task flow mode">
                        <x-icon name="play" class="w-5 h-5 mr-2" />
                        Start Reviewing
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Key Metrics --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Queue Depth --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 text-sm font-medium">Queue Depth</span>
                    <x-icon name="inbox-stack" class="w-5 h-5 text-gray-400" title="Total items in queue" />
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ $this->queueStats['total'] }}</div>
                <div class="mt-2 flex items-center text-sm">
                    <span class="text-red-600 mr-2">{{ $this->queueStats['overdue'] }} overdue</span>
                    <span class="text-gray-400">·</span>
                    <span class="text-yellow-600 ml-2">{{ $this->queueStats['by_priority']['urgent'] ?? 0 }} urgent</span>
                </div>
            </div>

            {{-- SLA Compliance --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 text-sm font-medium">SLA Compliance</span>
                    <x-icon name="clock" class="w-5 h-5 text-gray-400" title="Percentage of items completed within SLA" />
                </div>
                <div class="text-3xl font-bold {{ ($this->queueStats['sla_compliance'] ?? 0) >= 90 ? 'text-green-600' : (($this->queueStats['sla_compliance'] ?? 0) >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $this->queueStats['sla_compliance'] ?? 0 }}%
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    Last 30 days
                </div>
            </div>

            {{-- Average Review Time --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 text-sm font-medium">Avg Review Time</span>
                    <x-icon name="arrow-trending-down" class="w-5 h-5 text-gray-400" title="Average time spent per review" />
                </div>
                @php
                    $avgSeconds = $this->queueStats['avg_review_time'] ?? 0;
                    $avgMinutes = floor($avgSeconds / 60);
                    $avgRemainingSeconds = $avgSeconds % 60;
                @endphp
                <div class="text-3xl font-bold text-gray-900">
                    {{ $avgMinutes }}:{{ str_pad($avgRemainingSeconds, 2, '0', STR_PAD_LEFT) }}
                </div>
                <div class="mt-2 text-sm text-gray-500">
                    Minutes per review
                </div>
            </div>

            {{-- Today's Reviews --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-500 text-sm font-medium">Today's Reviews</span>
                    <x-icon name="check-circle" class="w-5 h-5 text-gray-400" title="Reviews completed today" />
                </div>
                <div class="text-3xl font-bold text-gray-900">{{ $this->queueStats['completed_today'] ?? 0 }}</div>
                <div class="mt-2 text-sm text-gray-500">
                    {{ $this->queueStats['in_progress'] ?? 0 }} in progress
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Priority Distribution --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Queue by Priority</h2>
                    <div class="space-y-4">
                        @foreach(['urgent' => 'red', 'high' => 'orange', 'normal' => 'blue', 'low' => 'gray'] as $priority => $color)
                            @php
                                $count = $this->queueStats['by_priority'][$priority] ?? 0;
                                $total = $this->queueStats['total'] ?: 1;
                                $percentage = ($count / $total) * 100;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 capitalize">{{ $priority }}</span>
                                    <span class="text-sm text-gray-500">{{ $count }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-{{ $color }}-500 h-2 rounded-full transition-all duration-300"
                                         style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Team Performance --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Team Performance</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Team Member
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Today
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        This Week
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Avg Time
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Current Load
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Approval Rate
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($this->teamPerformance as $member)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-pulse-blue-100 flex items-center justify-center text-pulse-blue-600 font-medium text-sm">
                                                    {{ substr($member['name'], 0, 1) }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $member['name'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                            {{ $member['completed_today'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                            {{ $member['completed_week'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                            {{ $member['avg_time'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                                                {{ $member['current_load'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm {{ $member['approval_rate'] >= 70 ? 'text-green-600' : 'text-yellow-600' }}">
                                                {{ $member['approval_rate'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            No team members configured
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-8">
                {{-- SLA Warnings --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">SLA Warnings</h2>
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">
                            {{ $this->slaWarnings->count() }}
                        </span>
                    </div>
                    <div class="divide-y divide-gray-200 max-h-80 overflow-y-auto">
                        @forelse($this->slaWarnings as $warning)
                            <div class="px-6 py-3 hover:bg-gray-50">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $warning['content_title'] }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $warning['assigned_to'] }}
                                        </p>
                                    </div>
                                    <span class="ml-2 px-2 py-1 text-xs font-medium rounded-full
                                        {{ $warning['sla_status'] === 'breached' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $warning['due_at'] }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-gray-500">
                                <x-icon name="check-circle" class="w-8 h-8 mx-auto mb-2 text-green-500" title="All items on track" />
                                <p class="text-sm">No SLA warnings</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Recent Decisions</h2>
                    </div>
                    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                        @forelse($this->recentDecisions as $decision)
                            <div class="px-6 py-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900">{{ $decision['user_name'] }}</span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                        {{ match($decision['color']) {
                                            'green' => 'bg-green-100 text-green-700',
                                            'red' => 'bg-red-100 text-red-700',
                                            'yellow' => 'bg-yellow-100 text-yellow-700',
                                            'orange' => 'bg-orange-100 text-orange-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        } }}">
                                        {{ $decision['decision'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 truncate">{{ $decision['content_title'] }}</p>
                                <div class="flex items-center justify-between mt-1 text-xs text-gray-400">
                                    <span>{{ $decision['time_spent'] }}</span>
                                    <span>{{ $decision['created_at'] }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-gray-500">
                                <p class="text-sm">No recent decisions</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Content Type Breakdown --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Content Types</h2>
                    <div class="space-y-3">
                        @foreach($this->contentTypeBreakdown as $type => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">{{ $type }}</span>
                                <span class="font-semibold text-gray-900">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
