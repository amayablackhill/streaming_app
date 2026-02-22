@props([
    'header' => null,
])

<div class="min-h-screen bg-cc-bg-primary text-cc-text-primary">
    <x-ui.top-nav context="editorial" />

    @if ($header)
        <header class="border-b border-cc-border bg-cc-bg-surface/60">
            <div class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endif

    <main class="w-full">
        {{ $slot }}
    </main>
</div>
