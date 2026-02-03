<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.cohorts.index') }}" class="hover:text-purple-600">@term('cohort_plural')</a>
                <span>/</span>
                <a href="{{ route('admin.cohorts.show', $cohort) }}" class="hover:text-purple-600">{{ $cohort->name }}</a>
                <span>/</span>
                <span>@term('enroll_action')</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">@term('add_action') @term('learner_plural')</h1>
            <p class="text-gray-600 mt-1">@term('enroll_users_in_label') {{ $cohort->name }}</p>
        </div>
        <a href="{{ route('admin.cohorts.show', $cohort) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            @term('back_to_cohort_label')
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Search & Manual Enrollment -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Search Users -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">@term('search_users_label')</h2>
                    <p class="text-sm text-gray-500 mt-1">@term('find_select_users_label')</p>
                </div>
                <div class="p-4">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="flex-1">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="userSearch"
                                placeholder="{{ app(\App\Services\TerminologyService::class)->get('search_by_name_or_email_placeholder') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            >
                        </div>
                        @if(count($selectedUsers) > 0)
                            <button
                                wire:click="clearSelection"
                                class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800"
                            >
                                @term('clear_action') ({{ count($selectedUsers) }})
                            </button>
                        @endif
                    </div>

                    @if(strlen($userSearch) >= 2)
                        @if($searchResults->count() > 0)
                            <div class="border border-gray-200 rounded-lg divide-y divide-gray-200 max-h-80 overflow-y-auto">
                                @foreach($searchResults as $user)
                                    <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleUser({{ $user->id }})"
                                            @checked(in_array($user->id, $selectedUsers))
                                            class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                                        >
                                        <div class="ml-3 flex items-center flex-1">
                                            <img class="w-8 h-8 rounded-full" src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" alt="">
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-2 flex items-center justify-between text-sm text-gray-500">
                                <span>{{ $searchResults->count() }} @term('results_label')</span>
                                <button wire:click="selectAll" class="text-purple-600 hover:text-purple-700">@term('select_all_visible_label')</button>
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <p>@term('no_users_found_label') "{{ $userSearch }}"</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <svg class="mx-auto h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <p class="mt-2">@term('type_to_search_help_label')</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- CSV Import -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">@term('bulk_import_label')</h2>
                    <p class="text-sm text-gray-500 mt-1">@term('bulk_import_body_label')</p>
                </div>
                <div class="p-4">
                    @if(!$showCsvPreview)
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                            <div class="text-center">
                                <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <div class="mt-3">
                                    <label for="csv-upload" class="cursor-pointer">
                                        <span class="text-purple-600 hover:text-purple-700 font-medium">@term('upload_csv_label')</span>
                                        <input
                                            id="csv-upload"
                                            type="file"
                                            wire:model="csvFile"
                                            accept=".csv,.txt"
                                            class="sr-only"
                                        >
                                    </label>
                                    <p class="text-xs text-gray-500 mt-1">@term('csv_required_columns_label')</p>
                                </div>
                            </div>
                        </div>
                        @error('csvFile')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- CSV Template -->
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 mb-2">@term('csv_template_label')</p>
                            <code class="text-xs text-gray-600 block">{{ app(\App\Services\TerminologyService::class)->get('csv_template_header_label') }}</code>
                            <code class="text-xs text-gray-600 block">{{ app(\App\Services\TerminologyService::class)->get('csv_template_row_one_label') }}</code>
                            <code class="text-xs text-gray-600 block">{{ app(\App\Services\TerminologyService::class)->get('csv_template_row_two_label') }}</code>
                        </div>
                    @else
                        <!-- CSV Preview -->
                        <div>
                            @if(count($csvErrors) > 0)
                                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-sm font-medium text-red-800 mb-2">@term('errors_found_label')</p>
                                    <ul class="text-sm text-red-700 list-disc list-inside">
                                        @foreach(array_slice($csvErrors, 0, 5) as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                        @if(count($csvErrors) > 5)
                                            <li>@term('more_errors_label') {{ count($csvErrors) - 5 }}</li>
                                        @endif
                                    </ul>
                                </div>
                            @endif

                            @if(count($csvPreview) > 0)
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">@term('email_label')</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">@term('name_label')</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">@term('role_label')</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">@term('status_label')</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach(array_slice($csvPreview, 0, 10) as $row)
                                                <tr>
                                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $row['email'] }}</td>
                                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $row['name'] ?: '-' }}</td>
                                                    <td class="px-4 py-2 text-sm text-gray-600 capitalize">{{ $row['role'] }}</td>
                                                    <td class="px-4 py-2">
                                                        @if($row['already_enrolled'])
                                                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">@term('already_enrolled_label')</span>
                                                        @elseif($row['user_exists'])
                                                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">@term('ready_label')</span>
                                                        @else
                                                            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">@term('will_invite_label')</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if(count($csvPreview) > 10)
                                    <p class="mt-2 text-sm text-gray-500">@term('showing_label') 10 @term('of_label') {{ count($csvPreview) }} @term('records_label')</p>
                                @endif

                                <div class="mt-4 flex items-center justify-end space-x-3">
                                    <button
                                        wire:click="cancelCsvImport"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                                    >
                                        @term('cancel_action')
                                    </button>
                                    <button
                                        wire:click="importCsv"
                                        class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700"
                                    >
                                        @term('import_action') {{ count($csvPreview) }} @term('learner_plural')
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="space-y-6">
            <!-- Enrollment Options -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">@term('enrollment_options_label')</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('default_role_label')</label>
                        <select wire:model="bulkRole" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            @foreach($roleOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                wire:model="sendWelcomeEmail"
                                class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">@term('send_welcome_email_label')</span>
                        </label>
                    </div>

                    @if(count($selectedUsers) > 0)
                        <button
                            wire:click="enrollSelected"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700"
                        >
                            @term('enroll_action') {{ count($selectedUsers) }} @term('selected_label')
                        </button>
                    @else
                        <button
                            disabled
                            class="w-full px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed"
                        >
                            @term('select_users_to_enroll_label')
                        </button>
                    @endif
                </div>
            </div>

            <!-- Recent Enrollments -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">@term('recently_enrolled_label')</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($currentMembers as $member)
                        <div class="px-4 py-3 flex items-center">
                            <img class="w-8 h-8 rounded-full" src="{{ $member->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($member->user?->name ?? 'U') }}" alt="">
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $member->user?->name ?? app(\App\Services\TerminologyService::class)->get('unknown_label') }}</p>
                                <p class="text-xs text-gray-500">{{ $member->enrolled_at?->diffForHumans() }}</p>
                            </div>
                            <span class="text-xs text-gray-500 capitalize">{{ $member->role }}</span>
                        </div>
                    @empty
                        <div class="px-4 py-6 text-center text-gray-500 text-sm">
                            @term('no_label') @term('learner_plural') @term('enrolled_label') @term('yet_label')
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Cohort Info -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">@term('cohort_info_label')</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">@term('capacity_label')</dt>
                        <dd class="text-gray-900">{{ $cohort->max_capacity ?? app(\App\Services\TerminologyService::class)->get('unlimited_label') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">@term('enrolled_label')</dt>
                        <dd class="text-gray-900">{{ $cohort->members_count ?? $cohort->members()->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">@term('start_date_label')</dt>
                        <dd class="text-gray-900">{{ $cohort->start_date->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
