<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.cohorts.index') }}" class="hover:text-purple-600">@term('cohort_plural')</a>
                <span>/</span>
                <span>{{ $cohort->name }}</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $cohort->name }}</h1>
            <p class="text-gray-600 mt-1">{{ $cohort->course?->title }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.cohorts.enroll', $cohort) }}" class="px-4 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100">
                Add @term('learner_plural')
            </a>
            <a href="{{ route('admin.cohorts.schedule', $cohort) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Drip Schedule
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_members'] }}</p>
                    <p class="text-xs text-gray-500">Total @term('learner_plural')</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_members'] }}</p>
                    <p class="text-xs text-gray-500">Active</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['completed'] }}</p>
                    <p class="text-xs text-gray-500">Completed</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['avg_progress'] }}%</p>
                    <p class="text-xs text-gray-500">Avg @term('progress_label')</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Cohort Info & Status -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('cohort_singular') Details</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">@term('period_singular')</dt>
                    <dd class="font-medium text-gray-900">{{ $cohort->semester?->display_name ?? 'None' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Dates</dt>
                    <dd class="font-medium text-gray-900">{{ $cohort->start_date->format('M d, Y') }} - {{ $cohort->end_date->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Capacity</dt>
                    <dd class="font-medium text-gray-900">{{ $cohort->max_capacity ? $stats['total_members'] . '/' . $cohort->max_capacity : 'Unlimited' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Self-Enrollment</dt>
                    <dd class="font-medium text-gray-900">{{ $cohort->allow_self_enrollment ? 'Enabled' : 'Disabled' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Drip Content</dt>
                    <dd class="font-medium text-gray-900">{{ $cohort->drip_content ? 'Enabled' : 'Disabled' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Created By</dt>
                    <dd class="font-medium text-gray-900">{{ $cohort->creator?->name ?? 'System' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Status</h2>
            <div class="space-y-3">
                @foreach($statusOptions as $value => $label)
                <button
                    wire:click="updateStatus('{{ $value }}')"
                    class="w-full px-4 py-2 text-sm font-medium rounded-lg border transition-colors
                        {{ $cohort->status === $value
                            ? 'bg-purple-600 text-white border-purple-600'
                            : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}"
                >
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">@term('learner_plural')</h2>
            <div class="flex items-center space-x-3">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="memberSearch"
                    placeholder="Search..."
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg"
                >
                <select wire:model.live="memberStatusFilter" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg">
                    <option value="">All Statuses</option>
                    @foreach($memberStatusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@term('learner_singular')</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">@term('progress_label')</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrolled</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($members as $member)
                <tr>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <img class="w-8 h-8 rounded-full" src="{{ $member->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->user?->name ?? 'U') }}" alt="">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $member->user?->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500">{{ $member->user?->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-900">{{ $memberRoleOptions[$member->role] ?? $member->role }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-24 bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $member->progress_percent }}%"></div>
                            </div>
                            <span class="text-sm text-gray-600">{{ $member->progress_percent }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $statusColors = [
                                'enrolled' => 'bg-blue-100 text-blue-800',
                                'active' => 'bg-green-100 text-green-800',
                                'completed' => 'bg-purple-100 text-purple-800',
                                'withdrawn' => 'bg-red-100 text-red-800',
                                'paused' => 'bg-yellow-100 text-yellow-800',
                            ];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$member->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $memberStatusOptions[$member->status] ?? $member->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $member->enrolled_at?->format('M d, Y') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button
                            wire:click="removeMember({{ $member->id }})"
                            wire:confirm="Remove this @term('learner_singular') from the @term('cohort_singular')?"
                            class="text-red-600 hover:text-red-800 text-sm"
                        >
                            Remove
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                        No @term('learner_plural') enrolled yet.
                        <a href="{{ route('admin.cohorts.enroll', $cohort) }}" class="text-purple-600 hover:underline">Add @term('learner_plural')</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($members->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $members->links() }}
        </div>
        @endif
    </div>
</div>
