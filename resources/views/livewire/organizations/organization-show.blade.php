<div class="space-y-6">
    {{-- Back Link & Header --}}
    <div>
        <a href="/organizations" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-3">
            <x-icon name="arrow-left" class="w-4 h-4" />
            All Organizations
        </a>

        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-lg flex items-center justify-center text-lg font-semibold flex-shrink-0"
                 style="background-color: {{ $organization->primary_color ?? '#f3f4f6' }}; color: {{ $organization->primary_color ? '#fff' : '#6b7280' }}">
                @if($organization->logo_url)
                    <img src="{{ $organization->logo_url }}" alt="" class="w-12 h-12 rounded-lg object-cover" />
                @else
                    {{ strtoupper(substr($organization->org_name, 0, 1)) }}
                @endif
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $organization->org_name }}</h1>
                <p class="text-sm text-gray-500 capitalize">{{ str_replace('_', ' ', $organization->org_type ?? 'Organization') }}</p>
            </div>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Contact Info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Contact Information</h3>
            <dl class="space-y-2 text-sm">
                @if($organization->primary_contact_name)
                    <div>
                        <dt class="text-gray-500">Primary Contact</dt>
                        <dd class="text-gray-900 font-medium">{{ $organization->primary_contact_name }}</dd>
                    </div>
                @endif
                @if($organization->primary_contact_email)
                    <div>
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-900">{{ $organization->primary_contact_email }}</dd>
                    </div>
                @endif
                @if($organization->primary_contact_phone)
                    <div>
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="text-gray-900">{{ $organization->primary_contact_phone }}</dd>
                    </div>
                @endif
                @if(!$organization->primary_contact_name && !$organization->primary_contact_email && !$organization->primary_contact_phone)
                    <p class="text-gray-400 italic">No contact information</p>
                @endif
            </dl>
        </div>

        {{-- Details --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Details</h3>
            <dl class="space-y-2 text-sm">
                @if($organization->timezone)
                    <div>
                        <dt class="text-gray-500">Timezone</dt>
                        <dd class="text-gray-900">{{ $organization->timezone }}</dd>
                    </div>
                @endif
                @if($organization->address)
                    <div>
                        <dt class="text-gray-500">Address</dt>
                        <dd class="text-gray-900">
                            @if(is_array($organization->address))
                                {{ implode(', ', array_filter($organization->address)) }}
                            @else
                                {{ $organization->address }}
                            @endif
                        </dd>
                    </div>
                @endif
                @if($organization->subscription_plan)
                    <div>
                        <dt class="text-gray-500">Plan</dt>
                        <dd class="text-gray-900 capitalize">{{ $organization->subscription_plan }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Stats --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Overview</h3>
            <dl class="space-y-2 text-sm">
                <div>
                    <dt class="text-gray-500">Users</dt>
                    <dd class="text-gray-900 font-medium">{{ $organization->users_count }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Sub-Organizations</dt>
                    <dd class="text-gray-900 font-medium">{{ $organization->children_count }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Status</dt>
                    <dd>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $organization->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $organization->active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Users ({{ $organization->users_count }})</h3>
        </div>
        @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-gray-900 font-medium">{{ $user->name }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $user->primary_role ?? 'user') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-8 text-center text-sm text-gray-500">No users in this organization.</div>
        @endif
    </div>

    {{-- Sub-Organizations --}}
    @if($childOrgs->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Sub-Organizations ({{ $childOrgs->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($childOrgs as $child)
                    <a href="/organizations/{{ $child->id }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold flex-shrink-0"
                             style="background-color: {{ $child->primary_color ?? '#f3f4f6' }}; color: {{ $child->primary_color ? '#fff' : '#6b7280' }}">
                            {{ strtoupper(substr($child->org_name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $child->org_name }}</p>
                            <p class="text-xs text-gray-500">{{ $child->users_count }} {{ Str::plural('user', $child->users_count) }}</p>
                        </div>
                        <x-icon name="chevron-right" class="w-4 h-4 text-gray-400" />
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
