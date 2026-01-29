<div>
    <x-card class="mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <!-- Avatar -->
            <div class="w-24 h-24 rounded-full overflow-hidden flex-shrink-0">
                <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="w-full h-full object-cover">
            </div>

            <!-- Info -->
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-semibold text-gray-900">{{ $displayName }}</h1>
                    @if($riskLevel)
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $riskColor }}">
                        {{ ucfirst($riskLevel) }} Risk
                    </span>
                    @endif
                </div>

                <div class="flex items-center gap-2 mb-3">
                    <span class="text-sm font-medium text-gray-600">{{ $roleDisplay }}</span>
                </div>

                <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600">
                    @foreach($contactInfo as $info)
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500">{{ $info['label'] }}:</span>
                        <span class="text-gray-900">{{ $info['value'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="flex items-center gap-2">
                <button class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
                <button class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors" title="Print">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                </button>
                <button class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors" title="More actions">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </x-card>
</div>
