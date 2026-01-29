<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Pulse' }} - Student Wellness Platform</title>
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
                                500: '#8B5CF6',
                                600: '#7C3AED',
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
<body class="bg-gray-50 min-h-screen font-sans">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center">
                        <span class="text-2xl font-bold text-pulse-orange-500">Pulse</span>
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
                        <a href="/login" class="text-gray-600 hover:text-gray-900 font-medium">Login</a>
                        <a href="/register" class="bg-pulse-orange-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">Get Started</a>
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
