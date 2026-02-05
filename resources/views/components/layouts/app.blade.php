<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Pulse' }} - Student Wellness Platform</title>

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
</head>
@php $isProspect = auth()->user()?->isProspect() ?? false; @endphp
<body class="bg-gray-50 min-h-screen font-sans {{ $isProspect ? 'demo-prospect' : '' }}">
    <script>
        window.PULSE_PROSPECT = {{ $isProspect ? 'true' : 'false' }};
    </script>
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <img src="/Pulse Logo - Black font.svg" alt="Pulse" class="h-7 w-auto" />
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="/dashboard" class="text-gray-600 hover:text-gray-900 font-medium">Dashboard</a>
                        <form method="POST" action="/logout" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 font-medium">Logout</button>
                        </form>
                    @else
                        <a href="/login" class="text-gray-400 hover:text-gray-600 font-medium inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3V7a3 3 0 10-6 0v1c0 1.657 1.343 3 3 3z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 11h12v9a2 2 0 01-2 2H8a2 2 0 01-2-2v-9z"/>
                            </svg>
                            Admin Log In
                        </a>
                        @if (request()->routeIs('demo.landing') || request()->routeIs('home'))
                            <a href="#demo-access" class="bg-pulse-orange-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">Get Started</a>
                        @else
                            <a href="/register" class="bg-pulse-orange-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">Get Started</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} Pulse. Supporting student wellness through meaningful conversations.
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
