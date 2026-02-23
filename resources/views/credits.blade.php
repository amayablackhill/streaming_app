<x-app-layout>
    <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <article class="cc-stack-6 max-w-3xl">
            <header class="cc-stack-2">
                <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Credits</p>
                <h1 class="cc-title-display">Data & Tooling Credits</h1>
                <p class="text-sm leading-editorial text-cc-text-secondary">
                    This page lists the data and tooling sources used in the project demo.
                </p>
            </header>

            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                <h2 class="cc-title-section">TMDB Attribution</h2>
                <p class="text-sm leading-7 text-cc-text-secondary">
                    Metadata and image references are provided by TMDB where available. This product uses the TMDB API but is not endorsed or certified by TMDB.
                </p>
                <a
                    href="https://www.themoviedb.org/"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex w-fit items-center gap-2 text-sm text-cc-text-secondary transition-colors cc-motion-base hover:text-cc-text-primary"
                >
                    Visit TMDB
                    <x-ui.icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            </section>

            <section class="cc-surface cc-stack-3 p-4 sm:p-5">
                <h2 class="cc-title-section">Open Source Stack</h2>
                <p class="text-sm leading-7 text-cc-text-secondary">
                    Built with Laravel, Blade, Tailwind CSS, Alpine.js, FFmpeg, MySQL/PostgreSQL-compatible infrastructure, and Docker-based local and production workflows.
                </p>
            </section>
        </article>
    </section>
</x-app-layout>
