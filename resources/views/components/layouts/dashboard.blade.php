<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - Pulse</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-60 bg-white border-r border-gray-200 flex flex-col">
            <!-- Logo -->
            <div class="px-6 py-5 border-b border-gray-200">
                <a href="/dashboard" class="flex items-center">
                    <span class="text-2xl font-bold text-pulse-orange-500">Pulse</span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
                <x-nav-item href="/dashboard" :active="request()->is('dashboard')">
                    <x-icon name="home" class="w-5 h-5" />
                    Dashboard
                </x-nav-item>

                <x-nav-item href="/contacts" :active="request()->is('contacts*')">
                    <x-icon name="users" class="w-5 h-5" />
                    Contacts
                </x-nav-item>

                <x-nav-item href="/surveys" :active="request()->is('surveys*')">
                    <x-icon name="clipboard-list" class="w-5 h-5" />
                    Surveys
                </x-nav-item>

                <x-nav-item href="/resources" :active="request()->is('resources*')">
                    <x-icon name="book-open" class="w-5 h-5" />
                    Resources
                </x-nav-item>

                <x-nav-item href="/reports" :active="request()->is('reports*')">
                    <x-icon name="chart-bar" class="w-5 h-5" />
                    Reports
                </x-nav-item>

                <div class="pt-4 mt-4 border-t border-gray-200">
                    <x-nav-item href="/settings" :active="request()->is('settings*')">
                        <x-icon name="cog" class="w-5 h-5" />
                        Settings
                    </x-nav-item>
                </div>
            </nav>

            <!-- User Menu -->
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-pulse-orange-100 rounded-full flex items-center justify-center">
                        <span class="text-pulse-orange-600 font-medium text-sm">
                            {{ substr(auth()->user()->first_name ?? 'U', 0, 1) }}{{ substr(auth()->user()->last_name ?? '', 0, 1) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ auth()->user()->first_name ?? 'User' }} {{ auth()->user()->last_name ?? '' }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            {{ auth()->user()->email ?? '' }}
                        </p>
                    </div>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="p-1.5 hover:bg-gray-100 rounded transition-colors" title="Logout">
                            <x-icon name="logout" class="w-5 h-5 text-gray-500" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-gray-200 px-8 flex items-center justify-between flex-shrink-0">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $title ?? 'Dashboard' }}</h1>

                <div class="flex items-center gap-3">
                    {{ $actions ?? '' }}
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
