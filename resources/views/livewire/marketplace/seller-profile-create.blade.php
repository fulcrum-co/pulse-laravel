@php
    $terminology = app(\App\Services\TerminologyService::class);
@endphp

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('marketplace.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="arrow-left" class="w-5 h-5" />
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">@term('become_seller_label')</h1>
                    <p class="mt-1 text-sm text-gray-500">@term('share_resources_with_community_label')</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form wire:submit="create" class="space-y-8">
            <!-- Profile Info Card -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">@term('profile_information_label')</h2>
                    <p class="text-sm text-gray-500">@term('profile_information_help_label')</p>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Avatar Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">@term('profile_photo_label')</label>
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
                                @if($avatar)
                                    <img src="{{ $avatar->temporaryUrl() }}" alt="{{ $terminology->get('preview_label') }}" class="w-full h-full object-cover">
                                @else
                                    <x-icon name="user" class="w-10 h-10 text-gray-400" />
                                @endif
                            </div>
                            <div>
                                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <x-icon name="camera" class="w-4 h-4" />
                                    @term('upload_photo_label')
                                    <input type="file" wire:model="avatar" accept="image/*" class="hidden">
                                </label>
                                <p class="mt-1 text-xs text-gray-500">@term('file_types_limit_label')</p>
                            </div>
                        </div>
                        @error('avatar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Display Name -->
                    <div>
                        <label for="displayName" class="block text-sm font-medium text-gray-700 mb-1">@term('display_name_label') <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="displayName"
                            wire:model="displayName"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="{{ $terminology->get('display_name_placeholder') }}"
                        >
                        @error('displayName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Seller Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">@term('seller_type_label') <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach($sellerTypes as $value => $label)
                                <label class="relative">
                                    <input type="radio" wire:model="sellerType" value="{{ $value }}" class="peer sr-only">
                                    <div class="p-4 rounded-lg border-2 cursor-pointer transition-all
                                        peer-checked:border-pulse-orange-500 peer-checked:bg-pulse-orange-50
                                        border-gray-200 hover:border-gray-300">
                                        <div class="font-medium text-gray-900">{{ $label }}</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            @switch($value)
                                                @case('individual')
                                                    @term('seller_type_help_individual_label')
                                                    @break
                                                @case('organization')
                                                    @term('seller_type_help_organization_label')
                                                    @break
                                                @case('verified_educator')
                                                    @term('seller_type_help_verified_label')
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('sellerType') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Bio -->
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">@term('bio_label')</label>
                        <textarea
                            id="bio"
                            wire:model="bio"
                            rows="4"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="{{ $terminology->get('bio_placeholder') }}"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">{{ strlen($bio) }}/1000 {{ $terminology->get('characters_label') }}</p>
                        @error('bio') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Expertise Card -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">@term('expertise_areas_label')</h2>
                    <p class="text-sm text-gray-500">@term('expertise_areas_help_label')</p>
                </div>

                <div class="p-6">
                    <!-- Add expertise input -->
                    <div class="flex gap-2 mb-4">
                        <input
                            type="text"
                            x-data
                            x-ref="expertiseInput"
                            @keydown.enter.prevent="$wire.addExpertise($refs.expertiseInput.value); $refs.expertiseInput.value = ''"
                            class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="{{ $terminology->get('expertise_placeholder') }}"
                        >
                        <button
                            type="button"
                            x-data
                            @click="$wire.addExpertise($refs.expertiseInput.value); $refs.expertiseInput.value = ''"
                            class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200"
                        >
                            @term('add_label')
                        </button>
                    </div>

                    <!-- Expertise tags -->
                    @if(count($expertiseAreas) > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($expertiseAreas as $index => $area)
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-pulse-orange-100 text-pulse-orange-700 text-sm">
                                    {{ $area }}
                                    <button type="button" wire:click="removeExpertise({{ $index }})" class="hover:text-pulse-orange-900">
                                        <x-icon name="x-mark" class="w-4 h-4" />
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">@term('no_expertise_areas_yet_label')</p>
                    @endif
                </div>
            </div>

            <!-- Credentials Card -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">@term('credentials_certifications_label')</h2>
                    <p class="text-sm text-gray-500">@term('credentials_help_label')</p>
                </div>

                <div class="p-6">
                    <!-- Add credential form -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                        <input
                            type="text"
                            wire:model="newCredentialTitle"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="{{ $terminology->get('credential_title_placeholder') }}"
                        >
                        <input
                            type="text"
                            wire:model="newCredentialIssuer"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="{{ $terminology->get('credential_issuer_placeholder') }}"
                        >
                        <div class="flex gap-2">
                            <input
                                type="text"
                                wire:model="newCredentialYear"
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                                placeholder="{{ $terminology->get('year_label') }}"
                            >
                            <button
                                type="button"
                                wire:click="addCredential"
                                class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200"
                            >
                                @term('add_label')
                            </button>
                        </div>
                    </div>

                    <!-- Credentials list -->
                    @if(count($credentials) > 0)
                        <div class="space-y-2">
                            @foreach($credentials as $index => $credential)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $credential['title'] }}</div>
                                        @if($credential['issuer'] || $credential['year'])
                                            <div class="text-sm text-gray-500">
                                                {{ $credential['issuer'] }}{{ $credential['issuer'] && $credential['year'] ? ' • ' : '' }}{{ $credential['year'] }}
                                            </div>
                                        @endif
                                    </div>
                                    <button type="button" wire:click="removeCredential({{ $index }})" class="text-gray-400 hover:text-red-600">
                                        <x-icon name="trash" class="w-5 h-5" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">@term('no_credentials_yet_label')</p>
                    @endif
                </div>
            </div>

            <!-- Terms & Submit -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <x-icon name="information-circle" class="w-6 h-6 text-blue-600" />
                        </div>
                        <div class="text-sm text-gray-600">
                            <p class="font-medium text-gray-900 mb-1">@term('marketplace_terms_label')</p>
                            <p>@term('marketplace_terms_body_label')</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('marketplace.index') }}" class="text-gray-500 hover:text-gray-700">
                            @term('cancel_label')
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-pulse-orange-500 text-white font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                        >
                            <x-icon name="sparkles" class="w-5 h-5" />
                            @term('create_seller_profile_label')
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
