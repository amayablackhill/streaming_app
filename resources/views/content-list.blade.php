<x-app-layout>
    @php
        $movies = $contents->where('type', 'film')->values();
        $series = $contents->where('type', 'serie')->values();
        $isMoviesView = request()->routeIs('content.movies.list');
        $isSeriesView = request()->routeIs('content.series.list');

        $pageTitle = $isMoviesView
            ? 'Film Catalog'
            : ($isSeriesView ? 'Series Catalog' : 'Curated Catalog');

        $pageDescription = $isMoviesView
            ? 'A curated selection of feature films with editorial metadata.'
            : ($isSeriesView
                ? 'Serialized stories selected for visual language and storytelling.'
                : 'Discover films and series in an editorial, dark-first experience.');

        $sections = [];

        if ($isMoviesView) {
            $sections[] = ['title' => 'Films', 'subtitle' => 'Collection', 'items' => $movies];
        } elseif ($isSeriesView) {
            $sections[] = ['title' => 'Series', 'subtitle' => 'Collection', 'items' => $series];
        } else {
            $sections[] = ['title' => 'Films', 'subtitle' => 'Featured', 'items' => $movies];
            $sections[] = ['title' => 'Series', 'subtitle' => 'Featured', 'items' => $series];
        }
    @endphp

    <section class="cc-stack-6">
        <header class="cc-stack-2">
            <p class="text-cc-caption uppercase text-cc-text-muted tracking-label">Cineclub Catalog</p>
            <h1 class="cc-title-display">{{ $pageTitle }}</h1>
            <p class="max-w-3xl text-sm text-cc-text-secondary leading-editorial">{{ $pageDescription }}</p>

            <div class="flex flex-wrap items-center gap-2 pt-1">
                <x-ui.badge tone="neutral">{{ $contents->count() }} total</x-ui.badge>
                <x-ui.badge tone="neutral">{{ $movies->count() }} films</x-ui.badge>
                <x-ui.badge tone="neutral">{{ $series->count() }} series</x-ui.badge>
            </div>
        </header>

        <div class="cc-divider"></div>

        <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 180)">
            <div x-show="loading" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" role="status" aria-live="polite" aria-label="Loading catalog">
                @foreach (range(1, 8) as $item)
                    <article class="cc-surface overflow-hidden animate-pulse">
                        <div class="aspect-[2/3] bg-cc-bg-elevated"></div>
                        <div class="space-y-2 p-3">
                            <div class="h-2.5 w-24 bg-cc-bg-elevated"></div>
                            <div class="h-5 w-4/5 bg-cc-bg-elevated"></div>
                            <div class="h-2.5 w-2/5 bg-cc-bg-elevated"></div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div x-show="!loading" x-cloak class="cc-stack-8">
                @foreach ($sections as $section)
                    <section class="cc-stack-4">
                        <header class="cc-stack-2">
                            <p class="text-cc-caption uppercase text-cc-text-muted tracking-label">{{ $section['subtitle'] }}</p>
                            <h2 class="cc-title-section">{{ $section['title'] }}</h2>
                        </header>

                        @if ($section['items']->isEmpty())
                            <x-ui.empty-state
                                title="No titles found"
                                description="This section is still empty. Add content from the admin panel or sync metadata imports."
                                :action-label="auth()->check() ? 'Go to dashboard' : null"
                                :action-href="auth()->check() ? route('dashboard') : null"
                            />
                        @else
                            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
                                @foreach ($section['items'] as $content)
                                    @php
                                        $detailUrl = url('/' . ($content->type === 'serie' ? 'series' : 'movies') . '/' . $content->id);
                                        $posterDir = $content->type === 'film' ? 'movies' : 'series';
                                        $posterUrl = $content->picture
                                            ? asset('storage/' . $posterDir . '/' . $content->picture)
                                            : asset('storage/logo/netflick_logo_definitive.png');
                                        $releaseYear = $content->release_date ? substr((string) $content->release_date, 0, 4) : 'N/A';
                                        $meta = $content->rating ? 'Rating ' . number_format((float) $content->rating, 1) : null;
                                    @endphp

                                    <x-ui.card-film
                                        :title="$content->title"
                                        :href="$detailUrl"
                                        :image="$posterUrl"
                                        :year="$releaseYear"
                                        :eyebrow="$content->type === 'serie' ? 'Series' : 'Film'"
                                        :meta="$meta"
                                    />
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>
        </div>
    </section>
</x-app-layout>
