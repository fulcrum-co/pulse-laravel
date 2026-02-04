<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4">
    @if($notFound)
        <div class="flex items-center justify-center min-h-[400px]">
            <div class="text-center">
                <x-icon name="user" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Provider Not Found</h2>
                <p class="text-gray-500">This provider is no longer available or has been made private.</p>
            </div>
        </div>
    @else
        <div class="max-w-2xl mx-auto">
            {{-- Provider Card --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-6 text-white">
                    <div class="flex items-center gap-4">
                        {{-- Avatar --}}
                        <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
                            {{ substr($provider->name, 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h1 class="text-2xl font-bold truncate">{{ $provider->name }}</h1>
                                @if($provider->verified_at)
                                <svg class="w-5 h-5 text-white flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" title="Verified">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                @endif
                            </div>
                            <p class="text-purple-100">{{ ucfirst($provider->provider_type) }}</p>
                            @if($provider->credentials)
                            <p class="text-purple-200 text-sm">{{ $provider->credentials }}</p>
                            @endif
                        </div>
                        @if($provider->ratings_average)
                        <div class="text-center flex-shrink-0">
                            <div class="text-2xl font-bold">{{ number_format($provider->ratings_average, 1) }}</div>
                            <div class="flex items-center justify-center text-yellow-300">
                                @for($i = 1; $i <= 5; $i++)
                                <svg class="w-3 h-3 {{ $i <= round($provider->ratings_average) ? 'fill-current' : 'fill-purple-400' }}" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                                @endfor
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-6 space-y-4">
                    {{-- Bio --}}
                    @if($provider->bio)
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 mb-1">About</h2>
                        <p class="text-gray-700 leading-relaxed">{{ $provider->bio }}</p>
                    </div>
                    @endif

                    {{-- Specialties --}}
                    @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 mb-2">Specialties</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($provider->specialty_areas as $specialty)
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">{{ $specialty }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Availability --}}
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="space-y-2 text-sm">
                            <h3 class="font-semibold text-gray-900">Availability</h3>
                            @if($provider->serves_remote)
                            <span class="flex items-center text-green-600">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Remote
                            </span>
                            @endif
                            @if($provider->serves_in_person)
                            <span class="flex items-center text-green-600">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                In-Person
                            </span>
                            @endif
                        </div>
                        <div class="space-y-2 text-sm">
                            <h3 class="font-semibold text-gray-900">Pricing</h3>
                            @if($provider->hourly_rate)
                            <p class="text-gray-600"><span class="font-medium">${{ number_format($provider->hourly_rate) }}</span>/hr</p>
                            @endif
                            @if($provider->accepts_insurance)
                            <span class="flex items-center text-green-600">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Accepts Insurance
                            </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">
                            Powered by Pulse
                        </span>
                        @if($provider->organization)
                            <span class="text-xs text-gray-500">
                                {{ $provider->organization->org_name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
