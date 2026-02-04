<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Resource Hub' }}</title>

    {{-- SEO Meta --}}
    @if(isset($metaDescription))
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if(isset($metaKeywords))
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $title ?? 'Resource Hub' }}">
    @if(isset($metaDescription))
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    <meta property="og:type" content="website">

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 antialiased">
    {{-- Public Header --}}
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo/Org Name --}}
                <div class="flex items-center gap-3">
                    @if(isset($orgLogo) && $orgLogo)
                        <img src="{{ $orgLogo }}" alt="{{ $orgName ?? 'Organization' }}" class="h-8 w-auto">
                    @endif
                    <div>
                        @if(isset($orgName) && $orgName)
                            <span class="text-lg font-semibold text-gray-900">{{ $orgName }}</span>
                        @endif
                        <span class="text-sm text-gray-500 ml-2">Resource Hub</span>
                    </div>
                </div>

                {{-- Powered By --}}
                <div class="flex items-center gap-4">
                    <span class="text-xs text-gray-400">Powered by</span>
                    <a href="https://pulse.edu" target="_blank" class="text-pulse-orange-600 font-semibold text-sm hover:text-pulse-orange-700">
                        Pulse
                    </a>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} {{ $orgName ?? 'Organization' }}. All rights reserved.
                </p>
                <div class="flex items-center gap-6 text-sm text-gray-500">
                    <a href="#" class="hover:text-gray-700">Privacy Policy</a>
                    <a href="#" class="hover:text-gray-700">Terms of Service</a>
                    <a href="https://pulse.edu" target="_blank" class="text-pulse-orange-600 hover:text-pulse-orange-700">
                        Get Pulse for Your Organization
                    </a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Toast Notifications --}}
    <x-toast-notifications />

    @livewireScripts
</body>
</html>
