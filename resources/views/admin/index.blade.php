<x-app-layout>
    <section class="cc-stack-6">
        <header class="cc-stack-2">
            <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Admin Hub</p>
            <h1 class="cc-title-display">Curator Dashboard</h1>
            <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                Manage catalog entries, episodic structures, demo clips, and TMDB imports from a single workspace.
            </p>
        </header>

        <div class="grid gap-4 md:grid-cols-3">
            <article class="cc-surface cc-stack-2 p-4 sm:p-5">
                <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Films</p>
                <p class="font-serif text-4xl text-cc-text-primary">{{ $movieCount }}</p>
                <x-ui.button :href="route('movies.table')" variant="ghost" size="sm">Manage films</x-ui.button>
            </article>

            <article class="cc-surface cc-stack-2 p-4 sm:p-5">
                <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Series</p>
                <p class="font-serif text-4xl text-cc-text-primary">{{ $seriesCount }}</p>
                <x-ui.button :href="route('series.table')" variant="ghost" size="sm">Manage series</x-ui.button>
            </article>

            <article id="video-assets" class="cc-surface cc-stack-2 p-4 sm:p-5">
                <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Video assets</p>
                <p class="font-serif text-4xl text-cc-text-primary">{{ $videoAssetCount }}</p>
                <x-ui.button :href="route('admin.health.video-pipeline')" variant="ghost" size="sm">Pipeline health</x-ui.button>
            </article>
        </div>

        <section class="cc-surface cc-stack-4 p-4 sm:p-5">
            <h2 class="cc-title-section">Quick actions</h2>
            <div class="flex flex-wrap gap-3">
                <x-ui.button :href="route('content.add')" variant="primary" size="sm">Add content</x-ui.button>
                <x-ui.button :href="route('admin.tmdb.search')" variant="secondary" size="sm">Import from TMDB</x-ui.button>
                <x-ui.button :href="route('series.table')" variant="ghost" size="sm">Manage seasons</x-ui.button>
            </div>
        </section>
    </section>
</x-app-layout>
