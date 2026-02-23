@props([
    'header' => null,
])

@php
    $routeName = (string) request()->route()?->getName();

    $adminPageLabel = match (true) {
        request()->routeIs('admin.home') => 'Dashboard',
        request()->routeIs('movies.table') => 'Films',
        request()->routeIs('series.table') => 'Series',
        request()->routeIs('content.add') => 'Add Content',
        request()->routeIs('content.edit'), request()->routeIs('content.update') => 'Edit Content',
        request()->routeIs('seasons.manage') => 'Manage Seasons',
        request()->routeIs('seasons.store') => 'Create Season',
        request()->routeIs('episodes.create') => 'Create Episode',
        request()->routeIs('episodes.edit'), request()->routeIs('episodes.update') => 'Edit Episode',
        request()->routeIs('episodes.store') => 'Create Episode',
        request()->routeIs('episodes.destroy') => 'Delete Episode',
        request()->routeIs('video-assets.show'), request()->routeIs('video-assets.status') => 'Video Assets',
        request()->routeIs('admin.tmdb.search'), request()->routeIs('admin.tmdb.import') => 'TMDB Import',
        request()->routeIs('admin.health.video-pipeline') => 'Pipeline Health',
        default => $routeName !== '' ? \Illuminate\Support\Str::headline(str_replace('.', ' ', $routeName)) : 'Workspace',
    };

    $breadcrumbs = [
        ['label' => 'Admin', 'href' => route('admin.home')],
        ['label' => $adminPageLabel],
    ];
@endphp

<div class="min-h-screen bg-cc-bg-primary text-cc-text-primary">
    <x-ui.top-nav context="admin" />

    <header class="border-b border-cc-border bg-cc-bg-elevated/70">
        <div class="mx-auto w-full max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <div class="cc-stack-2">
                <p class="text-[11px] uppercase tracking-[0.12em] text-cc-text-muted">Curator Workspace</p>
                @if ($header)
                    <div class="mt-1">{{ $header }}</div>
                @else
                    <h1 class="font-serif text-2xl text-cc-text-primary">Admin Dashboard</h1>
                @endif
                <x-ui.breadcrumbs :items="$breadcrumbs" />
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>
</div>
