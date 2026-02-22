@props([
    'header' => null,
])

<div class="min-h-screen bg-cc-bg-primary text-cc-text-primary">
    <x-ui.top-nav context="admin" />

    <header class="border-b border-cc-border bg-cc-bg-elevated/70">
        <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
            <div>
                <p class="text-[11px] uppercase tracking-[0.12em] text-cc-text-muted">Curator Workspace</p>
                @if ($header)
                    <div class="mt-1">{{ $header }}</div>
                @else
                    <h1 class="font-serif text-2xl text-cc-text-primary">Admin Dashboard</h1>
                @endif
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>
</div>
