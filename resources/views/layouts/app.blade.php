<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Cineclub</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $isAdminRoute = request()->is('admin') || request()->is('admin/*');
        $isMovieDetail = request()->segment(1) === 'movies'
            && filled(request()->segment(2))
            && request()->segment(3) === null;
        $isSeriesDetail = request()->segment(1) === 'series'
            && filled(request()->segment(2))
            && request()->segment(3) === null;
        $isSplitDetailLayout = $isMovieDetail || $isSeriesDetail;
    @endphp
    <body @class([
        'bg-cc-bg-primary text-cc-text-primary font-sans antialiased',
        'min-h-screen' => ! $isSplitDetailLayout,
        'min-h-screen lg:h-screen lg:overflow-hidden' => $isSplitDetailLayout,
    ])>

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
