<x-app-layout>
    <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <article class="cc-stack-6 max-w-3xl">
            <header class="cc-stack-2">
                <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">About</p>
                <h1 class="cc-title-display">Cineclub Streaming Archive</h1>
                <p class="text-sm leading-editorial text-cc-text-secondary">
                    A curated streaming portfolio focused on clean backend architecture, legal trailer playback, and a real FFmpeg HLS pipeline for short demo clips.
                </p>
            </header>

            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                <h2 class="cc-title-section">Project Focus</h2>
                <p class="text-sm leading-7 text-cc-text-secondary">
                    This platform is intentionally editorial and minimal. The public catalog prioritizes discovery and metadata quality, while the admin area demonstrates practical engineering decisions: role-based access, asynchronous jobs, queue processing, and deployable infrastructure.
                </p>
                <p class="text-sm leading-7 text-cc-text-secondary">
                    Playback in the catalog is trailer-first. The HLS pipeline is reserved for short demo media to keep the project legal, lightweight, and production-oriented.
                </p>
            </section>
        </article>
    </section>
</x-app-layout>
