<div class="relative">
    @php($terminology = app(\App\Services\TerminologyService::class))
    <!-- Current Organization Display -->
    <button
        wire:click="toggleSwitcher"
        class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors text-left"
    >
        <div class="w-8 h-8 bg-pulse-orange-100 rounded-full flex items-center justify-center">
            <span class="text-pulse-orange-600 font-medium text-sm">
                {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}{{ substr(auth()->user()->last_name ?? '', 0, 1) }}
            </span>
        </div>
        <div class="flex-1 min-w-0">
            <span class="block text-sm font-medium text-gray-900 truncate">
                {{ auth()->user()->first_name ?? $terminology->get('user_label') }} {{ auth()->user()->last_name ?? '' }}
            </span>
            <span class="block text-xs text-gray-500 truncate flex items-center gap-1">
                @if($isViewingChildOrg)
                <svg class="w-3 h-3 text-pulse-orange-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                </svg>
                @endif
                {{ $currentOrg->org_name ?? $terminology->get('no_organization_label') }}
            </span>
        </div>
        <svg class="w-4 h-4 text-gray-400 transition-transform {{ $showSwitcher ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown -->
    @if($showSwitcher)
    <div class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-80 overflow-y-auto">
        <!-- User Actions -->
        <div class="border-b border-gray-100">
            <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    @term('settings_label')
                </div>
            </a>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        @term('logout_label')
                    </div>
                </button>
            </form>
        </div>

        <!-- Organization Switcher -->
        @if($accessibleOrgs->count() > 1)
        <div class="py-2">
            <div class="px-3 py-1">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">@term('switch_organization_label')</span>
            </div>

            @if($isViewingChildOrg)
            <button
                wire:click="resetToHome"
                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-pulse-orange-600 hover:bg-pulse-orange-50"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                @term('return_home_organization_label')
            </button>
            @endif

            @foreach($accessibleOrgs as $org)
            <button
                wire:click="switchOrganization({{ $org->id }})"
                class="w-full flex items-center gap-3 px-4 py-2 text-sm text-left hover:bg-gray-50 {{ $currentOrg && $currentOrg->id === $org->id ? 'bg-gray-50' : '' }}"
            >
                <div class="w-6 h-6 rounded flex items-center justify-center text-xs font-medium
                    {{ $currentOrg && $currentOrg->id === $org->id ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-gray-100 text-gray-500' }}">
                    {{ substr($org->org_name ?? 'O', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <span class="block text-gray-900 truncate">{{ $org->org_name }}</span>
                    @if($org->org_type)
                    <span class="text-xs text-gray-400 capitalize">@term('org_type_' . $org->org_type . '_label')</span>
                    @endif
                </div>
                @if($currentOrg && $currentOrg->id === $org->id)
                <svg class="w-4 h-4 text-pulse-orange-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                @elseif($org->id === auth()->user()->org_id)
                <span class="text-xs text-gray-400">@term('home_label')</span>
                @endif
            </button>
            @endforeach
        </div>
        @endif
    </div>
    @endif
</div>
