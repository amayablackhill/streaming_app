@props([
    'header' => null,
])

@php
    $isHeroHomeRoute = request()->is('/') || request()->routeIs('home');
@endphp

<div class="min-h-screen bg-cc-bg-primary text-cc-text-primary">
    @unless ($isHeroHomeRoute)
        <x-ui.top-nav context="editorial" />
    @endunless

    @if ($header)
        <header class="border-b border-cc-border bg-cc-bg-surface/60">
            <div class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endif

    <main class="{{ $isHeroHomeRoute ? 'w-full' : 'mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8' }}">
        {{ $slot }}
    </main>
</div>
