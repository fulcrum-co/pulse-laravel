<div>
    <div class="flex items-center gap-4 pb-4 mb-4 border-b border-gray-200">
        <!-- Avatar - Compact -->
        <div class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0 ring-2 ring-gray-100">
            <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="w-full h-full object-cover">
        </div>

        <!-- Info - Compact -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-xl font-semibold text-gray-900 truncate">{{ $displayName }}</h1>
                @if($riskLevel)
                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $riskColor }} whitespace-nowrap">
                    {{ ucfirst($riskLevel) }} Risk
                </span>
                @endif
            </div>

            <div class="flex items-center gap-4 text-sm text-gray-600">
                <span class="font-medium">{{ $roleDisplay }}</span>
                @foreach($contactInfo as $info)
                <span class="text-gray-400">|</span>
                <span>{{ $info['value'] }}</span>
                @endforeach
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex items-center gap-1">
            <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </button>
            <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Print">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
            </button>
            <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="More actions">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                </svg>
            </button>
        </div>
    </div>
</div>
