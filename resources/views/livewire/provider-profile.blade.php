<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <!-- Back Link -->
    <a href="{{ route('resources.index') }}?activeTab=providers" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Providers
    </a>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 px-6 py-8">
            <div class="flex items-start gap-6">
                <!-- Avatar -->
                <div class="w-24 h-24 rounded-full bg-white/20 flex items-center justify-center text-white text-3xl font-bold">
                    {{ substr($provider->name, 0, 1) }}
                </div>
                <div class="flex-1 text-white">
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl font-bold">{{ $provider->name }}</h1>
                        @if($provider->verified_at)
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20" title="Verified">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        @endif
                    </div>
                    <p class="text-purple-100 mb-2">{{ ucfirst($provider->provider_type) }}</p>
                    @if($provider->credentials)
                    <p class="text-purple-200 text-sm">{{ $provider->credentials }}</p>
                    @endif
                </div>
                @if($provider->ratings_average)
                <div class="text-center">
                    <div class="text-3xl font-bold text-white">{{ number_format($provider->ratings_average, 1) }}</div>
                    <div class="flex items-center justify-center text-yellow-300">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= round($provider->ratings_average) ? 'fill-current' : 'fill-purple-400' }}" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        @endfor
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- Bio -->
            @if($provider->bio)
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">About</h2>
                <p class="text-gray-600">{{ $provider->bio }}</p>
            </div>
            @endif

            <!-- Specialties -->
            @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Specialties</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($provider->specialty_areas as $specialty)
                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">{{ $specialty }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Availability -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Availability</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            @if($provider->serves_remote)
                            <span class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Remote Sessions Available
                            </span>
                            @else
                            <span class="flex items-center text-gray-400">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                No Remote Sessions
                            </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($provider->serves_in_person)
                            <span class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                In-Person Sessions Available
                            </span>
                            @else
                            <span class="flex items-center text-gray-400">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                No In-Person Sessions
                            </span>
                            @endif
                        </div>
                        @if($provider->availability_notes)
                        <p class="text-gray-600 mt-2">{{ $provider->availability_notes }}</p>
                        @endif
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Pricing & Insurance</h2>
                    <div class="space-y-2 text-sm">
                        @if($provider->hourly_rate)
                        <p class="text-gray-600"><span class="font-medium">${{ number_format($provider->hourly_rate) }}</span> per hour</p>
                        @endif
                        <div class="flex items-center gap-2">
                            @if($provider->accepts_insurance)
                            <span class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Accepts Insurance
                            </span>
                            @else
                            <span class="text-gray-500">Does not accept insurance</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Connect -->
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Connect with {{ $provider->name }}</h2>
                <div class="flex flex-wrap gap-4">
                    <!-- Primary CTA: Message Provider -->
                    <button
                        wire:click="messageProvider"
                        class="inline-flex items-center px-6 py-3 bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors font-medium shadow-sm"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Message Provider
                    </button>

                    @if($provider->contact_email)
                    <a href="mailto:{{ $provider->contact_email }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Send Email
                    </a>
                    @endif
                    @if($provider->contact_phone)
                    <a href="tel:{{ $provider->contact_phone }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        {{ $provider->contact_phone }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
