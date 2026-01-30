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
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <!-- Logo & User Profile -->
            <div class="px-4 py-4 border-b border-gray-200">
                <a href="/dashboard" class="flex items-center mb-4">
                    <span class="text-2xl font-bold text-pulse-orange-500">Pulse</span>
                </a>
                <!-- User Profile Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="w-full flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-8 h-8 bg-pulse-orange-100 rounded-full flex items-center justify-center">
                            <span class="text-pulse-orange-600 font-medium text-sm">
                                {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}{{ substr(auth()->user()->last_name ?? '', 0, 1) }}
                            </span>
                        </div>
                        <span class="flex-1 text-left text-sm font-medium text-gray-900 truncate">
                            {{ auth()->user()->first_name ?? 'User' }} {{ auth()->user()->last_name ?? '' }}
                        </span>
                        <x-icon name="chevron-down" class="w-4 h-4 text-gray-400" />
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20">
                        <a href="/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Settings</a>
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Logout</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quick Access Grid -->
            <div class="px-3 py-3 border-b border-gray-200">
                <div class="grid grid-cols-2 gap-2">
                    <a href="/dashboard" class="flex flex-col items-center p-3 rounded-lg border {{ request()->is('dashboard') ? 'border-pulse-orange-200 bg-pulse-orange-50' : 'border-gray-200 hover:bg-gray-50' }}">
                        <x-icon name="home" class="w-5 h-5 {{ request()->is('dashboard') ? 'text-pulse-orange-500' : 'text-gray-500' }}" />
                        <span class="text-xs mt-1 {{ request()->is('dashboard') ? 'text-pulse-orange-600' : 'text-gray-600' }}">Home</span>
                    </a>
                    <a href="/contacts" class="flex flex-col items-center p-3 rounded-lg border {{ request()->is('contacts*') ? 'border-pulse-orange-200 bg-pulse-orange-50' : 'border-gray-200 hover:bg-gray-50' }}">
                        <x-icon name="users" class="w-5 h-5 {{ request()->is('contacts*') ? 'text-pulse-orange-500' : 'text-gray-500' }}" />
                        <span class="text-xs mt-1 {{ request()->is('contacts*') ? 'text-pulse-orange-600' : 'text-gray-600' }}">Contacts</span>
                    </a>
                    <a href="/surveys" class="flex flex-col items-center p-3 rounded-lg border {{ request()->is('surveys*') ? 'border-pulse-orange-200 bg-pulse-orange-50' : 'border-gray-200 hover:bg-gray-50' }}">
                        <x-icon name="clipboard-list" class="w-5 h-5 {{ request()->is('surveys*') ? 'text-pulse-orange-500' : 'text-gray-500' }}" />
                        <span class="text-xs mt-1 {{ request()->is('surveys*') ? 'text-pulse-orange-600' : 'text-gray-600' }}">Surveys</span>
                    </a>
                    <a href="/dashboards" class="flex flex-col items-center p-3 rounded-lg border {{ request()->is('dashboards*') ? 'border-pulse-orange-200 bg-pulse-orange-50' : 'border-gray-200 hover:bg-gray-50' }}">
                        <x-icon name="chart-pie" class="w-5 h-5 {{ request()->is('dashboards*') ? 'text-pulse-orange-500' : 'text-gray-500' }}" />
                        <span class="text-xs mt-1 {{ request()->is('dashboards*') ? 'text-pulse-orange-600' : 'text-gray-600' }}">Dashboards</span>
                    </a>
                </div>
            </div>

            <!-- Workspace Navigation -->
            <nav class="flex-1 py-3 px-3 overflow-y-auto">
                <p class="px-3 mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Workspace</p>

                <x-nav-item href="/strategies" :active="request()->is('strategies*')">
                    <x-icon name="flag" class="w-5 h-5" />
                    Strategy
                </x-nav-item>

                <x-nav-item href="/reports" :active="request()->is('reports*') && !request()->is('dashboard')">
                    <x-icon name="chart-bar" class="w-5 h-5" />
                    Reports
                </x-nav-item>

                <x-nav-item href="/surveys" :active="false">
                    <x-icon name="collection" class="w-5 h-5" />
                    Collect
                </x-nav-item>

                <x-nav-item href="/resources" :active="request()->is('resources*')">
                    <x-icon name="share" class="w-5 h-5" />
                    Distribute
                </x-nav-item>

                <x-nav-item href="/resources" :active="false">
                    <x-icon name="book-open" class="w-5 h-5" />
                    Resource
                </x-nav-item>

                <x-nav-item href="/alerts" :active="request()->is('alerts*')">
                    <x-icon name="bell" class="w-5 h-5" />
                    Alerts
                    <span class="ml-auto bg-pulse-orange-100 text-pulse-orange-600 text-xs font-medium px-2 py-0.5 rounded-full">4</span>
                </x-nav-item>

                <x-nav-item href="/marketplace" :active="request()->is('marketplace*')">
                    <x-icon name="shopping-bag" class="w-5 h-5" />
                    Marketplace
                    <span class="ml-auto bg-purple-100 text-purple-600 text-xs font-medium px-2 py-0.5 rounded-full">New</span>
                </x-nav-item>
            </nav>

            <!-- Settings -->
            <div class="p-3 border-t border-gray-200">
                <x-nav-item href="/settings" :active="request()->is('settings*')">
                    <x-icon name="cog" class="w-5 h-5" />
                    Settings
                </x-nav-item>
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
