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
        /* Smooth sidebar transition */
        .sidebar-transition {
            transition: width 0.2s ease-in-out;
        }
        .sidebar-content-transition {
            transition: opacity 0.15s ease-in-out;
        }
    </style>
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div x-data="{ sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' }"
         x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))"
         class="flex h-screen">

        <!-- Sidebar -->
        <aside :class="sidebarCollapsed ? 'w-16 sidebar-collapsed' : 'w-64'"
               class="sidebar-transition bg-white border-r border-gray-200 flex flex-col">

            <!-- Logo & Toggle -->
            <div class="px-3 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <a href="/dashboard" class="flex items-center">
                        <span class="text-2xl font-bold text-pulse-orange-500">
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
            </div>

            <!-- User Profile & Organization Switcher -->
            <div x-show="!sidebarCollapsed" class="px-3 py-3 border-b border-gray-200 sidebar-content-transition">
                <livewire:organization-switcher />
            </div>
            <!-- Collapsed User Avatar -->
            <div x-show="sidebarCollapsed" class="px-3 py-3 border-b border-gray-200 flex justify-center">
                <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" class="relative">
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
                    <div x-show="hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">
                        {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                    </div>
                </div>
            </div>

            <!-- Quick Access -->
            <div class="py-3 border-b border-gray-200">
                <!-- Expanded: 2x2 Grid -->
                <div x-show="!sidebarCollapsed" class="px-3">
                    <div class="grid grid-cols-2 gap-2">
                        <!-- Home -->
                        <a href="/dashboard"
                           class="flex flex-col items-center justify-center p-3 rounded-lg border transition-colors {{ request()->is('dashboard') ? 'bg-pulse-orange-50 border-pulse-orange-200 text-pulse-orange-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300' }}">
                            <x-icon name="home" class="w-5 h-5 mb-1 {{ request()->is('dashboard') ? 'text-pulse-orange-500' : '' }}" />
                            <span class="text-xs font-medium">Home</span>
                        </a>
                        <!-- Contacts -->
                        <a href="/contacts"
                           class="flex flex-col items-center justify-center p-3 rounded-lg border transition-colors {{ request()->is('contacts*') ? 'bg-pulse-orange-50 border-pulse-orange-200 text-pulse-orange-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300' }}">
                            <x-icon name="users" class="w-5 h-5 mb-1 {{ request()->is('contacts*') ? 'text-pulse-orange-500' : '' }}" />
                            <span class="text-xs font-medium">Contacts</span>
                        </a>
                        <!-- Surveys -->
                        <a href="/surveys"
                           class="flex flex-col items-center justify-center p-3 rounded-lg border transition-colors {{ request()->is('surveys*') ? 'bg-pulse-orange-50 border-pulse-orange-200 text-pulse-orange-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300' }}">
                            <x-icon name="clipboard-list" class="w-5 h-5 mb-1 {{ request()->is('surveys*') ? 'text-pulse-orange-500' : '' }}" />
                            <span class="text-xs font-medium">Surveys</span>
                        </a>
                        <!-- Dashboards -->
                        <a href="/dashboards"
                           class="flex flex-col items-center justify-center p-3 rounded-lg border transition-colors {{ request()->is('dashboards*') ? 'bg-pulse-orange-50 border-pulse-orange-200 text-pulse-orange-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300' }}">
                            <x-icon name="chart-pie" class="w-5 h-5 mb-1 {{ request()->is('dashboards*') ? 'text-pulse-orange-500' : '' }}" />
                            <span class="text-xs font-medium">Dashboards</span>
                        </a>
                    </div>
                </div>
                <!-- Collapsed: Vertical List -->
                <div x-show="sidebarCollapsed" class="px-2 space-y-1">
                    <!-- Home -->
                    <a href="/dashboard" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false"
                       class="relative flex items-center justify-center px-3 py-2 rounded-lg transition-colors {{ request()->is('dashboard') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="home" class="w-5 h-5 flex-shrink-0 {{ request()->is('dashboard') ? 'text-pulse-orange-500' : '' }}" />
                        <div x-show="hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Home</div>
                    </a>
                    <!-- Contacts -->
                    <a href="/contacts" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false"
                       class="relative flex items-center justify-center px-3 py-2 rounded-lg transition-colors {{ request()->is('contacts*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="users" class="w-5 h-5 flex-shrink-0 {{ request()->is('contacts*') ? 'text-pulse-orange-500' : '' }}" />
                        <div x-show="hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Contacts</div>
                    </a>
                    <!-- Surveys -->
                    <a href="/surveys" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false"
                       class="relative flex items-center justify-center px-3 py-2 rounded-lg transition-colors {{ request()->is('surveys*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="clipboard-list" class="w-5 h-5 flex-shrink-0 {{ request()->is('surveys*') ? 'text-pulse-orange-500' : '' }}" />
                        <div x-show="hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Surveys</div>
                    </a>
                    <!-- Dashboards -->
                    <a href="/dashboards" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false"
                       class="relative flex items-center justify-center px-3 py-2 rounded-lg transition-colors {{ request()->is('dashboards*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="chart-pie" class="w-5 h-5 flex-shrink-0 {{ request()->is('dashboards*') ? 'text-pulse-orange-500' : '' }}" />
                        <div x-show="hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Dashboards</div>
                    </a>
                </div>
            </div>

            <!-- Workspace Navigation -->
            <nav class="flex-1 py-3 overflow-y-auto" :class="sidebarCollapsed ? 'px-2' : 'px-3'">
                <p x-show="!sidebarCollapsed" class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-content-transition">Workspace</p>
                <div x-show="sidebarCollapsed" class="mb-2 border-t border-gray-200"></div>

                <!-- Strategy -->
                <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" class="relative">
                    <a href="/strategies"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('strategies*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="flag" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Strategy</span>
                    </a>
                    <div x-show="sidebarCollapsed && hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Strategy</div>
                </div>

                <!-- Reports -->
                <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" class="relative">
                    <a href="/reports"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('reports*') && !request()->is('dashboard') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="chart-bar" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Reports</span>
                    </a>
                    <div x-show="sidebarCollapsed && hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Reports</div>
                </div>

                <!-- Collect -->
                <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" class="relative">
                    <a href="/collect"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <x-icon name="collection" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Collect</span>
                        <span x-show="!sidebarCollapsed" class="ml-auto bg-pulse-orange-100 text-pulse-orange-600 text-xs font-medium px-2 py-0.5 rounded-full whitespace-nowrap">Soon</span>
                    </a>
                    <div x-show="sidebarCollapsed && hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Collect</div>
                </div>

                <!-- Distribute -->
                <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" class="relative">
                    <a href="/distribute"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <x-icon name="share" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Distribute</span>
                        <span x-show="!sidebarCollapsed" class="ml-auto bg-pulse-orange-100 text-pulse-orange-600 text-xs font-medium px-2 py-0.5 rounded-full whitespace-nowrap">Soon</span>
                    </a>
                    <div x-show="sidebarCollapsed && hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Distribute</div>
                </div>

                <!-- Resource -->
                <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" class="relative">
                    <a href="/resources"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <x-icon name="book-open" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Resource</span>
                    </a>
                    <div x-show="sidebarCollapsed && hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Resource</div>
                </div>

                <!-- Alerts -->
                <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" class="relative">
                    <a href="/alerts"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('alerts*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="bell" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Alerts</span>
                        <span x-show="!sidebarCollapsed" class="ml-auto bg-red-100 text-red-600 text-xs font-medium px-2 py-0.5 rounded-full">4</span>
                    </a>
                    <div x-show="sidebarCollapsed && hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Alerts (4)</div>
                </div>

                <!-- Marketplace -->
                <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" class="relative">
                    <a href="/marketplace"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                        <x-icon name="shopping-bag" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition whitespace-nowrap">Marketplace</span>
                        <span x-show="!sidebarCollapsed" class="ml-auto bg-pulse-orange-100 text-pulse-orange-600 text-xs font-medium px-2 py-0.5 rounded-full whitespace-nowrap">Soon</span>
                    </a>
                    <div x-show="sidebarCollapsed && hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Marketplace</div>
                </div>

                @php
                    $user = auth()->user();
                    $childOrgs = $user->getManagedChildOrganizations();
                @endphp

                @if($user->isAdmin() && $childOrgs->count() > 0)
                <!-- Sub-Organizations (for Consultants/Superintendents) -->
                <div class="mt-4 pt-4 border-t border-gray-100" x-show="!sidebarCollapsed">
                    <p class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sub-Organizations</p>

                    <div class="space-y-1 max-h-48 overflow-y-auto">
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
                    </div>

                    @if($childOrgs->count() > 5)
                    <a href="/organizations" class="block px-3 py-2 text-xs text-center text-pulse-orange-600 hover:text-pulse-orange-700">
                        View All Organizations
                    </a>
                    @endif
                </div>
                <!-- Collapsed Sub-Orgs Icon -->
                <div x-show="sidebarCollapsed" class="mt-4 pt-4 border-t border-gray-100">
                    <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" class="relative">
                        <a href="/organizations"
                           class="flex items-center justify-center px-3 py-2 rounded-lg transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                            <x-icon name="office-building" class="w-5 h-5 flex-shrink-0" />
                        </a>
                        <div x-show="hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Sub-Organizations ({{ $childOrgs->count() }})</div>
                    </div>
                </div>
                @endif
            </nav>

            <!-- Settings -->
            <div class="p-3 border-t border-gray-200" :class="sidebarCollapsed ? 'px-2' : ''">
                <div x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" class="relative">
                    <a href="/settings"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors {{ request()->is('settings*') ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <x-icon name="cog" class="w-5 h-5 flex-shrink-0" />
                        <span x-show="!sidebarCollapsed" class="text-sm font-medium sidebar-content-transition">Settings</span>
                    </a>
                    <div x-show="sidebarCollapsed && hover" x-transition.opacity class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded whitespace-nowrap z-50">Settings</div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-gray-200 px-8 flex items-center justify-between flex-shrink-0">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $title ?? 'Dashboard' }}</h1>

                <div class="flex items-center gap-3">
                    @if(isset($actions) && $actions->isNotEmpty())
                        {{ $actions }}
                    @elseif(request()->is('dashboard'))
                        <!-- Create Dropdown for Dashboard -->
                        <div x-data="{ open: false }" class="relative">
                            <button
                                @click="open = !open"
                                class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors"
                            >
                                <x-icon name="plus" class="w-4 h-4 mr-2" />
                                Create
                                <x-icon name="chevron-down" class="w-4 h-4 ml-2" />
                            </button>

                            <div
                                x-show="open"
                                @click.outside="open = false"
                                x-transition
                                class="absolute right-0 z-50 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200"
                            >
                                <div class="py-1">
                                    <a href="/surveys/create" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <x-icon name="clipboard-list" class="w-4 h-4 text-gray-400" />
                                        Survey
                                    </a>
                                    <a href="/reports/create" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <x-icon name="chart-bar" class="w-4 h-4 text-gray-400" />
                                        Report
                                    </a>
                                    <a href="/strategies/create" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <x-icon name="flag" class="w-4 h-4 text-gray-400" />
                                        Strategy
                                    </a>
                                    <a href="/alerts/create" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <x-icon name="bell" class="w-4 h-4 text-gray-400" />
                                        Alert
                                    </a>
                                </div>
                            </div>
                        </div>
                    @elseif(request()->is('dashboards'))
                        <!-- New Dashboard Button -->
                        <a href="/dashboard" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                            <x-icon name="plus" class="w-4 h-4 mr-2" />
                            New Dashboard
                        </a>
                    @endif
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
