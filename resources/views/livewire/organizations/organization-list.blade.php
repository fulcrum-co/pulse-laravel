<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Organizations</h1>
            <p class="mt-1 text-sm text-gray-500">Manage your sub-organizations</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="relative w-full sm:w-64">
        <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search organizations..."
            class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
        />
    </div>

    {{-- Grid --}}
    @if($organizations->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($organizations as $org)
                <a href="/organizations/{{ $org->id }}" class="block bg-white rounded-xl border border-gray-200 p-5 hover:shadow-md hover:border-pulse-orange-200 transition-all group">
                    <div class="flex items-start gap-4">
                        {{-- Avatar --}}
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-semibold flex-shrink-0"
                             style="background-color: {{ $org->primary_color ?? '#f3f4f6' }}; color: {{ $org->primary_color ? '#fff' : '#6b7280' }}">
                            @if($org->logo_url)
                                <img src="{{ $org->logo_url }}" alt="" class="w-10 h-10 rounded-lg object-cover" />
                            @else
                                {{ strtoupper(substr($org->org_name, 0, 1)) }}
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 truncate group-hover:text-pulse-orange-600 transition-colors">
                                {{ $org->org_name }}
                            </h3>
                            <p class="text-xs text-gray-500 capitalize mt-0.5">{{ str_replace('_', ' ', $org->org_type ?? 'Organization') }}</p>

                            <div class="flex items-center gap-3 mt-3 text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <x-icon name="users" class="w-3.5 h-3.5" />
                                    {{ $org->users_count }} {{ Str::plural('user', $org->users_count) }}
                                </span>
                                @if($org->primary_contact_email)
                                    <span class="flex items-center gap-1 truncate">
                                        <x-icon name="envelope" class="w-3.5 h-3.5 flex-shrink-0" />
                                        <span class="truncate">{{ $org->primary_contact_email }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <x-icon name="office-building" class="w-12 h-12 text-gray-300 mx-auto" />
            <h3 class="mt-3 text-sm font-medium text-gray-900">No organizations found</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if($search)
                    No organizations match "{{ $search }}".
                @else
                    No sub-organizations are configured yet.
                @endif
            </p>
        </div>
    @endif
</div>
