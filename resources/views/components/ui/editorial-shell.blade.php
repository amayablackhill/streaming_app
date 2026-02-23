@props([
    'header' => null,
])

@php
    $isEpisodeWatch = request()->routeIs('episodes.watch');
    $showPublicFooter = (
        request()->routeIs('home', 'search', 'content.movies.list', 'content.series.list', 'about', 'credits')
        || request()->is('movies/*')
        || request()->is('series/*')
    ) && ! $isEpisodeWatch;
@endphp

<div class="flex min-h-screen flex-col bg-cc-bg-primary text-cc-text-primary">
    <x-ui.top-nav context="editorial" />

    @if ($header)
        <header class="border-b border-cc-border bg-cc-bg-surface/60">
            <div class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endif

    <main class="w-full flex-1">
        {{ $slot }}
    </main>

    @if ($showPublicFooter)
        <x-ui.public-footer />
    @endif
</div>
