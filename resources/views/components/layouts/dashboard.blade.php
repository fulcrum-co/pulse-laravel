@use('App\Services\RolePermissions')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - Pulse</title>

    <!-- Microsoft Clarity -->
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "v99lylydfx");
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pulse: {
                            orange: {
                                50: '#FFF7ED',
                                100: '#FFEDD5',
                                200: '#FED7AA',
                                300: '#FDBA74',
                                400: '#FB923C',
                                500: '#F97316',
                                600: '#EA580C',
                                700: '#C2410C',
                            },
                            purple: {
                                50: '#FAF5FF',
                                100: '#F3E8FF',
                                500: '#8B5CF6',
                                600: '#7C3AED',
                                700: '#6D28D9',
                            }
                        }
                    },
                    fontFamily: {
                        sans: ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        /* Hide elements with x-cloak until Alpine initializes */
        [x-cloak] { display: none !important; }

        /* Smooth sidebar transition */
        .sidebar-transition {
            transition: width 0.2s ease-in-out;
        }
        .sidebar-content-transition {
            transition: opacity 0.15s ease-in-out;
        }
    </style>
    {{-- Livewire styles auto-injected via config inject_assets=true --}}
    @stack('styles')

    {{-- Vite assets for Laravel Echo / real-time notifications --}}
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-50 {{ session('demo_role_override') && session('demo_role_override') !== 'actual' ? 'pt-10' : '' }}">
    @php
        // Force sidebar collapsed on canvas-focused pages (Resources, Marketplace, Moderation)
        $forceCollapsed = request()->is('resources') || request()->is('marketplace') || request()->is('admin/moderation*');
    @endphp
    <div x-data="{
            sidebarCollapsed: {{ $forceCollapsed ? 'true' : "localStorage.getItem('sidebarCollapsed') === 'true'" }},
            hoveredItem: null,
            forceCollapsed: {{ $forceCollapsed ? 'true' : 'false' }}
         }"
         x-init="$watch('sidebarCollapsed', val => { if (!forceCollapsed) localStorage.setItem('sidebarCollapsed', val) })"
         class="flex h-screen">

        <!-- Sidebar -->
        @php $inDemoMode = session('demo_role_override') && session('demo_role_override') !== 'actual'; @endphp
        <aside :class="sidebarCollapsed ? 'w-16 sidebar-collapsed overflow-visible' : 'w-64'"
               class="sidebar-transition flex flex-col {{ $inDemoMode ? 'bg-purple-50 border-r-4 border-purple-400' : 'bg-white border-r border-gray-200' }}">

            <!-- Logo & Toggle -->
            <div class="px-3 py-4 {{ $inDemoMode ? 'border-b border-purple-200' : 'border-b border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <a href="/dashboard" class="flex items-center">
                        <span class="text-2xl font-bold {{ $inDemoMode ? 'text-purple-600' : 'text-pulse-orange-500' }}">
                            <span x-show="!sidebarCollapsed">Pulse</span>
                            <span x-show="sidebarCollapsed" class="text-xl">P</span>
                        </span>
                    </a>
                    <button @click="sidebarCollapsed = !sidebarCollapsed"
                            class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg x-show="!sidebarCollapsed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                        <svg x-show="sidebarCollapsed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
                @if($inDemoMode)
                <div x-show="!sidebarCollapsed" class="mt-2 px-2 py-1 bg-purple-100 rounded text-xs text-purple-700 font-medium text-center">
                    Viewing as: {{ ucfirst(str_replace('_', ' ', session('demo_role_override'))) }}
                </div>
                @endif
            </div>

            <!-- User Profile & Organization Switcher -->
            <div x-show="!sidebarCollapsed" class="px-3 py-3 sidebar-content-transition">
                <livewire:organization-switcher />
            </div>
            <!-- Collapsed User Avatar -->
            <div x-show="sidebarCollapsed" class="px-3 py-3 flex justify-center">
                <div @mouseenter="hoveredItem = 'user'" @mouseleave="hoveredItem = null" class="relative">
                    <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-pulse-orange-200 cursor-pointer">
                        @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-full h-full object-cover">
                        @else
                        <div class="w-full h-full bg-pulse-orange-100 flex items-center justify-center">
                            <span class="text-pulse-orange-600 font-medium text-sm">
                                {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}{{ substr(auth()->user()->last_name ?? '', 0, 1) }}
                            </span>
                        </div>
                        @endif
                    </div>
                    <!-- Tooltip -->
                    <div x-show="hoveredItem === 'user'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">
                        {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                    </div>
                </div>
            </div>

            <!-- Quick Access -->
            <div class="py-3 border-b border-gray-200">
                <!-- Expanded: 2x2 Grid -->
                <div x-show="!sidebarCollapsed" class="px-3">
                    <div class="grid grid-cols-2 gap-2">
                        @if(RolePermissions::currentUserCanAccess('home'))
                        <!-- Home -->
                        <a href="/dashboard"
                           class="flex flex-col items-center justify-center p-3 rounded-lg border transition-colors {{ request()->is('dashboard') ? 'bg-pulse-orange-50 border-pulse-orange-200 text-pulse-orange-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300' }}">
                            <x-icon name="home" class="w-5 h-5 mb-1 {{ request()->is('dashboard') ? 'text-pulse-orange-500' : '' }}" />
                            <span class="text-xs font-medium">Home</span>
                        </a>
                        @endif
                        @if(RolePermissions::currentUserCanAccess('contacts'))
                        <!-- Contacts -->
                        <a href="/contacts"
                           class="flex flex-col items-center justify-center p-3 rounded-lg border transition-colors {{ request()->is('contacts*') ? 'bg-pulse-orange-50 border-pulse-orange-200 text-pulse-orange-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300' }}">
                            <x-icon name="users" class="w-5 h-5 mb-1 {{ request()->is('contacts*') ? 'text-pulse-orange-500' : '' }}" />
                            <span class="text-xs font-medium">Contacts</span>
                        </a>
                        @endif
                        @if(RolePermissions::currentUserCanAccess('surveys'))
                        <!-- Surveys -->
                        <a href="/surveys"
                           class="flex flex-col items-center justify-center p-3 rounded-lg border transition-colors {{ request()->is('surveys*') ? 'bg-pulse-orange-50 border-pulse-orange-200 text-pulse-orange-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300' }}">
                            <x-icon name="clipboard-list" class="w-5 h-5 mb-1 {{ request()->is('surveys*') ? 'text-pulse-orange-500' : '' }}" />
                            <span class="text-xs font-medium">Surveys</span>
                        </a>
                        @endif
                        @if(RolePermissions::currentUserCanAccess('dashboards'))
                        <!-- Dashboards -->
                        <a href="/dashboards"
                           class="flex flex-col items-center justify-center p-3 rounded-lg border transition-colors {{ request()->is('dashboards*') ? 'bg-pulse-orange-50 border-pulse-orange-200 text-pulse-orange-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300' }}">
                            <x-icon name="chart-pie" class="w-5 h-5 mb-1 {{ request()->is('dashboards*') ? 'text-pulse-orange-500' : '' }}" />
                            <span class="text-xs font-medium">Dashboards</span>
                        </a>
                        @endif
                    </div>
                </div>
                <!-- Collapsed: Vertical List -->
                <div x-show="sidebarCollapsed" class="px-2 space-y-1">
                    @if(RolePermissions::currentUserCanAccess('home'))
                    <!-- Home -->
                    <a href="/dashboard" @mouseenter="hoveredItem = 'home'" @mouseleave="hoveredItem = null"
                       class="relative flex items-center justify-center px-3 py-2 rounded-lg transition-colors {{ request()->is('dashboard') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="home" class="w-5 h-5 flex-shrink-0 {{ request()->is('dashboard') ? 'text-pulse-orange-500' : '' }}" />
                        <div x-show="hoveredItem === 'home'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Home</div>
                    </a>
                    @endif
                    @if(RolePermissions::currentUserCanAccess('contacts'))
                    <!-- Contacts -->
                    <a href="/contacts" @mouseenter="hoveredItem = 'contacts'" @mouseleave="hoveredItem = null"
                       class="relative flex items-center justify-center px-3 py-2 rounded-lg transition-colors {{ request()->is('contacts*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="users" class="w-5 h-5 flex-shrink-0 {{ request()->is('contacts*') ? 'text-pulse-orange-500' : '' }}" />
                        <div x-show="hoveredItem === 'contacts'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Contacts</div>
                    </a>
                    @endif
                    @if(RolePermissions::currentUserCanAccess('surveys'))
                    <!-- Surveys -->
                    <a href="/surveys" @mouseenter="hoveredItem = 'surveys'" @mouseleave="hoveredItem = null"
                       class="relative flex items-center justify-center px-3 py-2 rounded-lg transition-colors {{ request()->is('surveys*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="clipboard-list" class="w-5 h-5 flex-shrink-0 {{ request()->is('surveys*') ? 'text-pulse-orange-500' : '' }}" />
                        <div x-show="hoveredItem === 'surveys'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Surveys</div>
                    </a>
                    @endif
                    @if(RolePermissions::currentUserCanAccess('dashboards'))
                    <!-- Dashboards -->
                    <a href="/dashboards" @mouseenter="hoveredItem = 'dashboards'" @mouseleave="hoveredItem = null"
                       class="relative flex items-center justify-center px-3 py-2 rounded-lg transition-colors {{ request()->is('dashboards*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="chart-pie" class="w-5 h-5 flex-shrink-0 {{ request()->is('dashboards*') ? 'text-pulse-orange-500' : '' }}" />
                        <div x-show="hoveredItem === 'dashboards'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Dashboards</div>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Workspace Navigation -->
            <nav class="flex-1 py-3" :class="sidebarCollapsed ? 'px-2 overflow-visible' : 'px-3 overflow-y-auto'">
                <p x-show="!sidebarCollapsed" class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-content-transition">Workspace</p>

                @if(RolePermissions::currentUserCanAccess('strategy'))
                <!-- Plan -->
                <div @mouseenter="hoveredItem = 'plan'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/plans"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('plans*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="clipboard-document-list" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Plan</span>
                    </a>
                    <div x-show="sidebarCollapsed && hoveredItem === 'plan'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Plan</div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('reports'))
                <!-- Reports -->
                <div @mouseenter="hoveredItem = 'reports'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/reports"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('reports*') && !request()->is('dashboard') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="chart-bar" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Reports</span>
                    </a>
                    <div x-show="sidebarCollapsed && hoveredItem === 'reports'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Reports</div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('collect'))
                <!-- Collect -->
                <div @mouseenter="hoveredItem = 'collect'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/collect"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('collect*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="collection" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Collect</span>
                    </a>
                    <div x-show="sidebarCollapsed && hoveredItem === 'collect'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Collect</div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('distribute'))
                <!-- Distribute -->
                <div @mouseenter="hoveredItem = 'distribute'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/distribute"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('distribute*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="share" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Distribute</span>
                    </a>
                    <div x-show="sidebarCollapsed && hoveredItem === 'distribute'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Distribute</div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('resources'))
                <!-- Resource -->
                <div @mouseenter="hoveredItem = 'resource'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/resources"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('resources*') && !request()->is('*/moderation*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="book-open" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Resource</span>
                    </a>
                    <div x-show="sidebarCollapsed && hoveredItem === 'resource'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Resource</div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('moderation'))
                <!-- Moderation -->
                @php
                    $moderationCount = \App\Models\ContentModerationResult::where('org_id', auth()->user()->org_id)->needsReview()->count();
                @endphp
                <div @mouseenter="hoveredItem = 'moderation'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="{{ route('admin.moderation') }}"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('admin/moderation*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="shield-check" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Moderation</span>
                        @if($moderationCount > 0)
                        <span x-show="!sidebarCollapsed" class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-pulse-orange-500 rounded-full">{{ $moderationCount > 99 ? '99+' : $moderationCount }}</span>
                        @endif
                    </a>
                    <div x-show="sidebarCollapsed && hoveredItem === 'moderation'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50 flex items-center gap-2">
                        Moderation
                        @if($moderationCount > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-gray-900 bg-pulse-orange-400 rounded-full">{{ $moderationCount > 99 ? '99+' : $moderationCount }}</span>
                        @endif
                    </div>
                </div>
                @endif

                @if(RolePermissions::currentUserCanAccess('marketplace'))
                <!-- Marketplace -->
                <div @mouseenter="hoveredItem = 'marketplace'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/marketplace"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('marketplace*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="shopping-bag" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition whitespace-nowrap">Marketplace</span>
                    </a>
                    <div x-show="sidebarCollapsed && hoveredItem === 'marketplace'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Marketplace</div>
                </div>
                @endif

                @php
                    $user = auth()->user();
                    $childOrgs = $user->getManagedChildOrganizations();
                @endphp

                @if(RolePermissions::currentUserCanAccess('sub_organizations') && $childOrgs->count() > 0)
                <!-- Sub-Organizations (for Consultants/Superintendents) -->
                <div x-data="{ subOrgsOpen: false }" class="mt-4 pt-4 border-t border-gray-100" x-show="!sidebarCollapsed">
                    <!-- Accordion Header -->
                    <button
                        @click="subOrgsOpen = !subOrgsOpen"
                        class="w-full flex items-center justify-between px-3 py-2 hover:bg-gray-50 rounded-lg transition-colors group"
                    >
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider group-hover:text-gray-600">
                            Sub-Organizations ({{ $childOrgs->count() }})
                        </span>
                        <svg
                            :class="{ 'rotate-180': subOrgsOpen }"
                            class="w-4 h-4 text-gray-400 transition-transform duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Accordion Content -->
                    <div
                        x-show="subOrgsOpen"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="space-y-1 max-h-48 overflow-y-auto mt-1"
                    >
                        @foreach($childOrgs as $childOrg)
                        <a
                            href="/organizations/{{ $childOrg->id }}"
                            class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg transition-colors
                                {{ request()->is('organizations/' . $childOrg->id . '*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                        >
                            <div class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-500 flex-shrink-0">
                                {{ substr($childOrg->org_name ?? 'O', 0, 1) }}
                            </div>
                            <span class="truncate">{{ $childOrg->org_name }}</span>
                        </a>
                        @endforeach

                        @if($childOrgs->count() > 5)
                        <a href="/organizations" class="block px-3 py-2 text-xs text-center text-pulse-orange-600 hover:text-pulse-orange-700">
                            View All Organizations
                        </a>
                        @endif
                    </div>
                </div>
                <!-- Collapsed Sub-Orgs Icon -->
                <div x-show="sidebarCollapsed" class="mt-4 pt-4 border-t border-gray-100">
                    <div @mouseenter="hoveredItem = 'suborgs'" @mouseleave="hoveredItem = null" class="relative">
                        <a href="/organizations"
                           class="flex items-center justify-center px-3 py-2 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <x-icon name="office-building" class="w-5 h-5 flex-shrink-0" />
                        </a>
                        <div x-show="hoveredItem === 'suborgs'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Sub-Organizations ({{ $childOrgs->count() }})</div>
                    </div>
                </div>
                @endif
            </nav>

            @if(RolePermissions::currentUserCanAccess('settings'))
            <!-- Settings -->
            <div class="p-3 border-t border-gray-200" :class="sidebarCollapsed ? 'px-2' : ''">
                <div @mouseenter="hoveredItem = 'settings'" @mouseleave="hoveredItem = null" class="relative">
                    <a href="/settings"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('settings*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="cog" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Settings</span>
                    </a>
                    <div x-show="sidebarCollapsed && hoveredItem === 'settings'" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Settings</div>
                </div>
            </div>
            @endif
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            @if(!($hideHeader ?? false))
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-gray-200 px-8 flex items-center justify-between flex-shrink-0">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $title ?? 'Dashboard' }}</h1>

                <div class="flex items-center gap-3">
                    <!-- Header Notification Icons -->
                    <livewire:layouts.header-notifications />

                    <!-- Contextual Help Button -->
                    <x-contextual-help-button />

                    <!-- Divider -->
                    <div class="h-6 w-px bg-gray-200"></div>
                    @if(isset($actions) && $actions->isNotEmpty())
                        {{ $actions }}
                    @elseif(request()->is('dashboard'))
                        <!-- Create Dropdown for Dashboard -->
                        <div x-data="{ open: false }" class="relative">
                            <button
                                @click="open = !open"
                                class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors"
                            >
                                <x-icon name="plus" class="w-4 h-4 mr-2" />
                                Create
                            </button>

                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-transition
                                class="absolute right-0 z-50 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200"
                            >
                                <div class="py-1">
                                    @if(RolePermissions::currentUserCanAccess('create_survey'))
                                    <a href="/surveys/create" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <x-icon name="clipboard-list" class="w-4 h-4 text-gray-400" />
                                        Survey
                                    </a>
                                    @endif
                                    @if(RolePermissions::currentUserCanAccess('create_collection'))
                                    <a href="/collect/create" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <x-icon name="collection" class="w-4 h-4 text-gray-400" />
                                        Collection
                                    </a>
                                    @endif
                                    @if(RolePermissions::currentUserCanAccess('create_report'))
                                    <a href="/reports/create" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <x-icon name="chart-bar" class="w-4 h-4 text-gray-400" />
                                        Report
                                    </a>
                                    @endif
                                    @if(RolePermissions::currentUserCanAccess('create_strategy'))
                                    <a href="/plans/create" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <x-icon name="clipboard-document-list" class="w-4 h-4 text-gray-400" />
                                        Plan
                                    </a>
                                    @endif
                                    @if(RolePermissions::currentUserCanAccess('create_alert'))
                                    <a href="/alerts/create" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <x-icon name="bell" class="w-4 h-4 text-gray-400" />
                                        Alert
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @elseif(request()->is('dashboards'))
                        <!-- New Dashboard Button -->
                        <a href="/dashboard" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                            <x-icon name="plus" class="w-4 h-4 mr-2" />
                            New Dashboard
                        </a>
                    @elseif(request()->is('resources'))
                        <!-- Add Resource Button -->
                        <button onclick="Livewire.dispatch('openAddResourceModal')" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                            <x-icon name="plus" class="w-4 h-4 mr-2" />
                            Add Resource
                        </button>
                    @elseif(request()->is('collect'))
                        <!-- Add Collection Button -->
                        <a href="{{ route('collect.create') }}" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                            <x-icon name="plus" class="w-4 h-4 mr-2" />
                            Add Collection
                        </a>
                    @elseif(request()->is('distribute'))
                        <!-- Create Distribution Button -->
                        <a href="{{ route('distribute.create') }}" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                            <x-icon name="plus" class="w-4 h-4 mr-2" />
                            Create Distribution
                        </a>
                    @elseif(request()->is('plans') || request()->is('plans/'))
                        <!-- New Plan Button -->
                        <a href="{{ route('plans.create') }}" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                            <x-icon name="plus" class="w-4 h-4 mr-2" />
                            New Plan
                        </a>
                    @elseif(request()->is('marketplace'))
                        <!-- Seller Dashboard Button -->
                        @php
                            $hasSellerProfile = \Illuminate\Support\Facades\Schema::hasTable('seller_profiles')
                                ? \App\Models\SellerProfile::where('user_id', auth()->id())->exists()
                                : false;
                        @endphp
                        @if($hasSellerProfile)
                            <a href="{{ route('marketplace.seller.dashboard') }}" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                                <x-icon name="squares-2x2" class="w-4 h-4 mr-2" />
                                Seller Dashboard
                            </a>
                        @else
                            <a href="{{ route('marketplace.seller.create') }}" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                                <x-icon name="sparkles" class="w-4 h-4 mr-2" />
                                Become a Seller
                            </a>
                        @endif
                    @elseif(request()->is('alerts*'))
                        <!-- Create Alert Button -->
                        <a href="{{ route('alerts.create') }}" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                            <x-icon name="plus" class="w-4 h-4 mr-2" />
                            Create Alert
                        </a>
                    @elseif(request()->is('messages*'))
                        <!-- New Message Button -->
                        <button onclick="Livewire.dispatch('openNewConversation')" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                            <x-icon name="plus" class="w-4 h-4 mr-2" />
                            New Message
                        </button>
                    @elseif(request()->is('resources/courses/*') && !request()->is('resources/courses/create') && !request()->is('*/edit'))
                        <!-- Learn More Button - Links to Resource Hub filtered by course category -->
                        @php
                            $course = request()->route('course');
                            $courseCategory = $course?->category ?? '';
                        @endphp
                        <a href="{{ route('resources.index', ['category' => $courseCategory]) }}" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                            <x-icon name="light-bulb" class="w-4 h-4 mr-2" />
                            Learn More
                        </a>
                    @elseif(request()->is('admin/moderation*'))
                        <!-- Add Content Button for Moderation -->
                        <a href="{{ route('resources.courses.index') }}" class="inline-flex items-center px-4 py-2 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                            <x-icon name="plus" class="w-4 h-4 mr-2" />
                            Add Content
                        </a>
                    @elseif(request()->is('help*'))
                        <!-- Contact Us Button for Help Center -->
                        <button
                            @click="$dispatch('open-support-modal', { context: 'help-center' })"
                            class="inline-flex items-center px-4 py-2 text-sm bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            Contact Us
                        </button>
                    @endif
                </div>
            </header>
            @endif

            <!-- Page Content -->
            <main class="flex-1 overflow-auto p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Demo Role Switcher (for admins only) -->
    @livewire('demo-role-switcher')

    <!-- Toast Notifications (real-time popups) -->
    <x-toast-notifications />

    <!-- Task Flow Bar (guided notification workflow) -->
    <x-task-flow-bar />

    <!-- Support Ticket Modal -->
    <x-support-ticket-modal />

    <!-- Help Widget (bottom-right FAB) -->
    <x-help-widget />

    <!-- Page Help Overlay (contextual guided walkthrough) -->
    <x-page-help-overlay />

    <!-- Auto Help Beacons (pulsating dots at key page elements) -->
    <x-auto-help-beacons />

    {{-- Livewire scripts auto-injected via config inject_assets=true --}}
    @stack('scripts')
</body>
</html>
