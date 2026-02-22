<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-cc-bg-primary text-cc-text-primary font-sans antialiased">
        @php
            $isAdminRoute = request()->is('admin') || request()->is('admin/*');
        @endphp

        @if ($isAdminRoute)
            <x-ui.admin-shell :header="$header ?? null">
                {{ $slot }}
            </x-ui.admin-shell>
        @else
            <x-ui.editorial-shell :header="$header ?? null">
                {{ $slot }}
            </x-ui.editorial-shell>
        @endif
    </body>
</html>
