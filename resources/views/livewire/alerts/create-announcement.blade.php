@php
    $terminology = app(\App\Services\TerminologyService::class);
    $roleLabels = [
        'instructor' => $terminology->get('role_instructor_label'),
        'support_person' => $terminology->get('role_support_person_label'),
        'admin' => $terminology->get('role_admin_label'),
        'direct_supervisor' => $terminology->get('role_direct_supervisor_label'),
    ];
@endphp

<div>
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="close"></div>

                {{-- Spacer for centering --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="send">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex-shrink-0 w-10 h-10 bg-pulse-orange-100 rounded-full flex items-center justify-center">
                                    <x-icon name="megaphone" class="w-5 h-5 text-pulse-orange-600" />
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">@term('create_announcement_label')</h3>
                            </div>

                            {{-- Title --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">@term('title_label')</label>
                                <input
                                    type="text"
                                    wire:model="title"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                                    placeholder="{{ $terminology->get('announcement_title_placeholder') }}"
                                />
                                @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Body --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">@term('message_label')</label>
                                <textarea
                                    wire:model="body"
                                    rows="4"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                                    placeholder="{{ $terminology->get('announcement_message_placeholder') }}"
                                ></textarea>
                                @error('body') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Priority --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">@term('priority_label')</label>
                                <select
                                    wire:model="priority"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                                >
                                    <option value="low">@term('priority_low_label')</option>
                                    <option value="normal">@term('priority_normal_label')</option>
                                    <option value="high">@term('priority_high_label')</option>
                                    <option value="urgent">@term('priority_urgent_label')</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">@term('priority_help_label')</p>
                            </div>

                            {{-- Target Audience --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">@term('send_to_label')</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="targetType" value="all" class="text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                        <span class="ml-2 text-sm text-gray-700">@term('send_to_all_label')</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="targetType" value="role" class="text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                        <span class="ml-2 text-sm text-gray-700">@term('send_to_roles_label')</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" wire:model.live="targetType" value="specific" class="text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                        <span class="ml-2 text-sm text-gray-700">@term('send_to_specific_label')</span>
                                    </label>
                                </div>
                                @error('targetType') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Role Selection --}}
                            @if($targetType === 'role')
                                <div class="mb-4 ml-6">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($this->availableRoles as $role)
                                            <button
                                                type="button"
                                                wire:click="toggleRole('{{ $role }}')"
                                                class="px-3 py-1.5 text-sm border rounded-full transition-colors
                                                    {{ in_array($role, $targetRoles)
                                                        ? 'bg-pulse-orange-50 border-pulse-orange-300 text-pulse-orange-700'
                                                        : 'border-gray-300 text-gray-600 hover:border-gray-400' }}"
                                            >
                                                <span class="capitalize">{{ $roleLabels[$role] ?? $role }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                    @error('targetRoles') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            {{-- User Selection --}}
                            @if($targetType === 'specific')
                                <div class="mb-4 ml-6">
                                    <select
                                        wire:model="targetUserIds"
                                        multiple
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                                        size="5"
                                    >
                                        @foreach($this->userSearchResults as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->name }} ({{ $user->email }}) - {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">@term('multi_select_help_label')</p>
                                    @error('targetUserIds') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            {{-- Expiration --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">@term('expires_at_optional_label')</label>
                                <input
                                    type="datetime-local"
                                    wire:model="expiresAt"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                                />
                                <p class="text-xs text-gray-500 mt-1">@term('expires_at_help_label')</p>
                                @error('expiresAt') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                            <button
                                type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium hover:bg-pulse-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pulse-orange-500 sm:w-auto"
                            >
                                @term('send_announcement_label')
                            </button>
                            <button
                                type="button"
                                wire:click="close"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pulse-orange-500 sm:mt-0 sm:w-auto"
                            >
                                @term('cancel_action')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
