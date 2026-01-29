<div class="space-y-6">
    <!-- Search & Filters -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="flex-1 w-full sm:w-auto">
            <div class="relative">
                <x-icon name="search" class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search contacts..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <select
                wire:model.live="riskFilter"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">All Risk Levels</option>
                <option value="good">Good Standing</option>
                <option value="low">Low Risk</option>
                <option value="high">High Risk</option>
            </select>

            <select
                wire:model.live="gradeFilter"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">All Grades</option>
                <option value="9">Grade 9</option>
                <option value="10">Grade 10</option>
                <option value="11">Grade 11</option>
                <option value="12">Grade 12</option>
            </select>

            @if($search || $riskFilter || $gradeFilter)
            <button
                wire:click="clearFilters"
                class="text-sm text-gray-500 hover:text-gray-700"
            >
                Clear filters
            </button>
            @endif
        </div>
    </div>

    <!-- Contacts Table -->
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" class="rounded border-gray-300" />
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Risk Level</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($contacts as $contact)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <input
                                type="checkbox"
                                wire:model="selectedIds"
                                value="{{ $contact->id }}"
                                class="rounded border-gray-300"
                            />
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('contacts.show', $contact) }}" class="flex items-center gap-3 hover:text-pulse-orange-600">
                                <div class="w-10 h-10 bg-pulse-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-pulse-orange-600 font-medium text-sm">
                                        {{ substr($contact->user->first_name ?? 'U', 0, 1) }}{{ substr($contact->user->last_name ?? '', 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $contact->user->first_name }} {{ $contact->user->last_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $contact->student_number }}</div>
                                </div>
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $riskColor = match($contact->risk_level) {
                                    'good' => 'green',
                                    'low' => 'yellow',
                                    'high' => 'red',
                                    default => 'gray',
                                };
                                $riskLabel = match($contact->risk_level) {
                                    'good' => 'Good Standing',
                                    'low' => 'Low Risk',
                                    'high' => 'High Risk',
                                    default => 'Unknown',
                                };
                            @endphp
                            <x-badge :color="$riskColor">{{ $riskLabel }}</x-badge>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            Grade {{ $contact->grade_level }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $contact->user->email }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('contacts.show', $contact) }}" class="p-1.5 hover:bg-gray-100 rounded transition-colors" title="View">
                                    <x-icon name="edit" class="w-4 h-4 text-gray-600" />
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <x-icon name="users" class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                            <p class="text-gray-500">No contacts found.</p>
                            @if($search || $riskFilter || $gradeFilter)
                            <p class="text-gray-400 text-sm mt-1">Try adjusting your filters.</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contacts->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $contacts->links() }}
        </div>
        @endif
    </x-card>
</div>
