<div class="space-y-4">
    @php($terminology = app(\App\Services\TerminologyService::class))
    <!-- Search & Filters -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4" data-help="contact-filters">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="relative w-full sm:w-64" data-help="search-contacts">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="@term('search_contacts_placeholder')"
                    class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                />
            </div>

            <div class="flex items-center gap-3">
                <select
                    wire:model.live="riskFilter"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                >
                    <option value="">@term('all_risk_levels_label')</option>
                    <option value="good">@term('risk_good_standing_label')</option>
                    <option value="low">@term('risk_low_label')</option>
                    <option value="high">@term('risk_high_label')</option>
                </select>

                <select
                    wire:model.live="gradeFilter"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                >
                    <option value="">@term('all_levels_label')</option>
                    <option value="9">{{ $terminology->get('level_label') }} 9</option>
                    <option value="10">{{ $terminology->get('level_label') }} 10</option>
                    <option value="11">{{ $terminology->get('level_label') }} 11</option>
                    <option value="12">{{ $terminology->get('level_label') }} 12</option>
                </select>

                @if($search || $riskFilter || $gradeFilter)
                <button
                    wire:click="clearFilters"
                    class="text-sm text-gray-500 hover:text-gray-700"
                >
                    @term('clear_action')
                </button>
                @endif
            </div>
        </div>

        <a href="{{ route('contacts.lists') }}" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors">
            <x-icon name="user-group" class="w-4 h-4" />
            @term('manage_lists_label')
        </a>
    </div>

    <!-- Contacts Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden" data-help="contact-list">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 w-8">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500" />
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('name_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('risk_level_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('level_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('email_label')</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">@term('actions_label')</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($contacts as $contact)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <input
                                type="checkbox"
                                wire:model="selectedIds"
                                value="{{ $contact->id }}"
                                class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <a href="{{ route('contacts.show', $contact) }}" class="flex items-center gap-3 hover:text-pulse-orange-600">
                                <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0">
                                    @if($contact->user->avatar_url)
                                        <img src="{{ $contact->user->avatar_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-pulse-orange-100 flex items-center justify-center">
                                            <span class="text-pulse-orange-600 font-medium text-xs">
                                                {{ substr($contact->user->first_name ?? 'U', 0, 1) }}{{ substr($contact->user->last_name ?? '', 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 text-sm">{{ $contact->user->first_name }} {{ $contact->user->last_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $contact->participant_number }}</div>
                                </div>
                            </a>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            @php
                                $riskColor = match($contact->risk_level) {
                                    'good' => 'green',
                                    'low' => 'yellow',
                                    'high' => 'red',
                                    default => 'gray',
                                };
                                $riskLabel = match($contact->risk_level) {
                                    'good' => $terminology->get('risk_good_standing_label'),
                                    'low' => $terminology->get('risk_low_label'),
                                    'high' => $terminology->get('risk_high_label'),
                                    default => $terminology->get('unknown_label'),
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $riskColor }}-100 text-{{ $riskColor }}-800">
                                {{ $riskLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                            {{ $terminology->get('level_label') }} {{ $contact->level }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                            {{ $contact->user->email }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-right">
                            <a href="{{ route('contacts.show', $contact) }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                @term('view_action')
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <x-icon name="users" class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                            <p class="text-sm text-gray-500">@term('no_contacts_found_label')</p>
                            @if($search || $riskFilter || $gradeFilter)
                            <p class="text-xs text-gray-400 mt-1">@term('adjust_filters_label')</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($contacts->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $contacts->links() }}
        </div>
        @endif
    </div>
</div>
