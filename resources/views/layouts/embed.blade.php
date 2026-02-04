<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Pulse' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <style>
        body {
            background: transparent;
            overflow: hidden;
        }
        /* Allow scrolling within the embed container */
        .embed-container {
            height: 100vh;
            overflow-y: auto;
        }
    </style>
</head>
<body class="antialiased">
    <div class="embed-container">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
