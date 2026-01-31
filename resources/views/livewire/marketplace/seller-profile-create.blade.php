<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('marketplace.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="arrow-left" class="w-5 h-5" />
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Become a Seller</h1>
                    <p class="mt-1 text-sm text-gray-500">Share your educational resources with the Pulse community</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form wire:submit="create" class="space-y-8">
            <!-- Profile Info Card -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Profile Information</h2>
                    <p class="text-sm text-gray-500">This is how you'll appear to buyers on the marketplace.</p>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Avatar Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                        <div class="flex items-center gap-6">
                            <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
                                @if($avatar)
                                    <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" class="w-full h-full object-cover">
                                @else
                                    <x-icon name="user" class="w-10 h-10 text-gray-400" />
                                @endif
                            </div>
                            <div>
                                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <x-icon name="camera" class="w-4 h-4" />
                                    Upload Photo
                                    <input type="file" wire:model="avatar" accept="image/*" class="hidden">
                                </label>
                                <p class="mt-1 text-xs text-gray-500">JPG, PNG, or GIF up to 2MB</p>
                            </div>
                        </div>
                        @error('avatar') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Display Name -->
                    <div>
                        <label for="displayName" class="block text-sm font-medium text-gray-700 mb-1">Display Name <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="displayName"
                            wire:model="displayName"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="Your name or business name"
                        >
                        @error('displayName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Seller Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Seller Type <span class="text-red-500">*</span></label>
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
                                                    Selling as yourself
                                                    @break
                                                @case('organization')
                                                    Representing a company
                                                    @break
                                                @case('verified_educator')
                                                    K-12 educator
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
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                        <textarea
                            id="bio"
                            wire:model="bio"
                            rows="4"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="Tell buyers about yourself, your experience, and what you offer..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">{{ strlen($bio) }}/1000 characters</p>
                        @error('bio') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Expertise Card -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Expertise Areas</h2>
                    <p class="text-sm text-gray-500">Help buyers find you by specifying your areas of expertise.</p>
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
                            placeholder="e.g., Special Education, Math, SEL, Anxiety..."
                        >
                        <button
                            type="button"
                            x-data
                            @click="$wire.addExpertise($refs.expertiseInput.value); $refs.expertiseInput.value = ''"
                            class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200"
                        >
                            Add
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
                        <p class="text-sm text-gray-500">No expertise areas added yet.</p>
                    @endif
                </div>
            </div>

            <!-- Credentials Card -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Credentials & Certifications</h2>
                    <p class="text-sm text-gray-500">Add your professional credentials to build trust with buyers.</p>
                </div>

                <div class="p-6">
                    <!-- Add credential form -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                        <input
                            type="text"
                            wire:model="newCredentialTitle"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="Credential/Degree"
                        >
                        <input
                            type="text"
                            wire:model="newCredentialIssuer"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="Issuing institution"
                        >
                        <div class="flex gap-2">
                            <input
                                type="text"
                                wire:model="newCredentialYear"
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                                placeholder="Year"
                            >
                            <button
                                type="button"
                                wire:click="addCredential"
                                class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200"
                            >
                                Add
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
                        <p class="text-sm text-gray-500">No credentials added yet.</p>
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
                            <p class="font-medium text-gray-900 mb-1">Marketplace Terms</p>
                            <p>By creating a seller profile, you agree to our marketplace terms. Platform fees apply: 10% for direct purchases, 30% for marketplace discovery.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('marketplace.index') }}" class="text-gray-500 hover:text-gray-700">
                            Cancel
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-pulse-orange-500 text-white font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                        >
                            <x-icon name="sparkles" class="w-5 h-5" />
                            Create Seller Profile
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
