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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10">
            <a href="{{ url('/') }}" class="text-3xl font-bold tracking-wide text-red-500">NETFLICK</a>
            <p class="mt-2 text-sm text-slate-400">Portfolio Streaming Demo</p>
            <div class="w-full sm:max-w-md mt-6 px-6 py-5 border border-slate-800 bg-slate-900 shadow-lg overflow-hidden sm:rounded-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
