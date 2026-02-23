<x-app-layout>
    @php
        $movies = $contents->where('type', 'film')->values();
        $series = $contents->where('type', 'serie')->values();

        $isMoviesView = request()->routeIs('content.movies.list');
        $isSeriesView = request()->routeIs('content.series.list');
        $isHomeView = ! $isMoviesView && ! $isSeriesView;

        $pageTitle = $isMoviesView
            ? 'Film Catalog'
            : ($isSeriesView ? 'Series Catalog' : 'Curated Catalog');

        $pageDescription = $isMoviesView
            ? 'A curated selection of feature films with editorial metadata.'
            : ($isSeriesView
                ? 'Serialized stories selected for visual language and storytelling.'
                : 'Discover films and series in an editorial, dark-first experience.');

        $heroCandidates = $movies
            ->filter(fn ($movie) => filled($movie->backdrop_url) || filled($movie->poster_url))
            ->values();

        if ($isHomeView) {
            $featuredHeroCandidates = $heroCandidates
                ->where('is_featured', true)
                ->values();

            $selectionPool = $featuredHeroCandidates->isNotEmpty() ? $featuredHeroCandidates : $heroCandidates;
            $featuredMovie = $selectionPool->shuffle()->first()
                ?? $movies->shuffle()->first()
                ?? $movies->first();
        } else {
            $featuredMovie = $movies->firstWhere('is_featured', true)
                ?? $movies->sortByDesc(fn ($movie) => (float) ($movie->rating ?? 0))->first()
                ?? $movies->first();
        }

        $featuredBackdrop = null;
        $featuredYear = 'N/A';
        $featuredDuration = null;
        if ($featuredMovie) {
            $featuredBackdrop = $featuredMovie->backdrop_url ?: $featuredMovie->poster_url;
            $featuredYear = $featuredMovie->release_date ? substr((string) $featuredMovie->release_date, 0, 4) : 'N/A';
            $featuredDuration = $featuredMovie->duration ? $featuredMovie->duration . ' min' : null;
        }

        $sections = [];

        if ($isMoviesView) {
            $sections[] = ['title' => 'Films', 'subtitle' => 'Collection', 'items' => $movies];
        } elseif ($isSeriesView) {
            $sections[] = ['title' => 'Series', 'subtitle' => 'Collection', 'items' => $series];
        } else {
            $sections[] = ['title' => 'Now Showing', 'subtitle' => 'Featured', 'items' => $movies];
            $sections[] = ['title' => 'Serialized Stories', 'subtitle' => 'Curated Selection', 'items' => $series];
        }

        $adminActionHref = auth()->check() && auth()->user()->canAccessAdminPanel()
            ? route('admin.home')
            : null;
    @endphp

    @if ($isHomeView && $featuredMovie)
        <section class="relative flex min-h-[70vh] w-full items-end overflow-hidden sm:min-h-[78vh] lg:min-h-[88vh]">
            <div class="absolute inset-0 z-0">
                @if ($featuredBackdrop)
                    <img
                        src="{{ $featuredBackdrop }}"
                        alt="{{ $featuredMovie->title }} feature artwork"
                        width="1920"
                        height="1080"
                        loading="eager"
                        fetchpriority="high"
                        draggable="false"
                        class="h-full w-full select-none object-cover object-[center_24%] sm:object-center"
                    >
                @else
                    <div class="h-full w-full bg-cc-bg-elevated"></div>
                @endif
                <div class="absolute inset-0 cc-hero-gradient"></div>
            </div>

            <div class="relative z-10 mx-auto w-full max-w-7xl px-4 pb-12 sm:px-6 sm:pb-20 md:px-12 md:pb-28 lg:px-16">
                <div class="max-w-2xl">
                    <h1 class="-ml-0.5 mb-6 font-serif text-5xl leading-none text-white sm:-ml-1 sm:mb-8 sm:text-7xl md:text-8xl lg:text-9xl">
                        {{ $featuredMovie->title }}
                    </h1>

                    <div class="mb-6 flex flex-wrap items-center gap-3 text-xs uppercase tracking-widest text-cc-text-secondary sm:mb-8 sm:gap-4 md:text-sm">
                        <span>{{ $featuredMovie->director ?: 'Unknown Director' }}</span>
                        <span class="h-1 w-1 rounded-full bg-cc-accent"></span>
                        <span>{{ $featuredYear }}</span>
                        @if ($featuredDuration)
                            <span class="h-1 w-1 rounded-full bg-cc-accent"></span>
                            <span>{{ $featuredDuration }}</span>
                        @endif
                    </div>



                    <div class="flex flex-wrap gap-3 sm:gap-4">
                        <x-ui.button :href="url('/movies/' . $featuredMovie->id)" variant="secondary" size="lg" class="border-white bg-white text-cc-bg-primary hover:bg-cc-accent hover:text-white">
                            <x-ui.icon name="play" class="h-4 w-4" />
                            Watch Film
                        </x-ui.button>
                        <x-ui.button href="#catalog-sections" variant="ghost" size="lg" class="border border-white/30 text-white hover:border-white hover:bg-white/10">
                            <x-ui.icon name="bookmark" class="h-4 w-4" />
                            Explore Catalog
                        </x-ui.button>
                        @if ($adminActionHref)
                            <x-ui.button :href="$adminActionHref" variant="ghost" size="lg" class="border border-white/30 text-white hover:border-white hover:bg-white/10">
                                <x-ui.icon name="dashboard" class="h-4 w-4" />
                                Curate Catalog
                            </x-ui.button>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section id="catalog-sections" class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @unless ($isHomeView)
            <header class="cc-stack-2">
                <p class="text-cc-caption uppercase text-cc-text-muted tracking-label">Cineclub Catalog</p>
                <h1 class="cc-title-display">{{ $pageTitle }}</h1>
                <p class="max-w-3xl text-sm text-cc-text-secondary leading-editorial">{{ $pageDescription }}</p>

                <div class="flex flex-wrap items-center gap-2 pt-1">
                    @if ($isMoviesView)
                        <x-ui.badge tone="neutral">Total {{ $movies->count() }} films</x-ui.badge>
                    @elseif ($isSeriesView)
                        <x-ui.badge tone="neutral">Total {{ $series->count() }} series</x-ui.badge>
                    @else
                        <x-ui.badge tone="neutral">{{ $contents->count() }} total</x-ui.badge>
                        <x-ui.badge tone="neutral">{{ $movies->count() }} films</x-ui.badge>
                        <x-ui.badge tone="neutral">{{ $series->count() }} series</x-ui.badge>
                    @endif
                    @if ($adminActionHref)
                        <x-ui.button :href="$adminActionHref" variant="ghost" size="sm">Admin</x-ui.button>
                        <x-ui.button :href="route('content.add')" variant="secondary" size="sm">Add Content</x-ui.button>
                    @endif
                </div>
            </header>

            <div class="cc-divider mt-6"></div>
        @endunless

        <div class="mt-6" x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 180)">
            <div x-show="loading" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4" role="status" aria-live="polite" aria-label="Loading catalog">
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
                        <header class="cc-stack-2 flex items-end justify-between gap-4">
                            <div class="cc-stack-2">
                                <p class="text-cc-caption uppercase text-cc-text-muted tracking-label">{{ $section['subtitle'] }}</p>
                                <h2 class="cc-title-section">{{ $section['title'] }}</h2>
                            </div>
                            @if ($isHomeView)
                                <a href="{{ $section['title'] === 'Now Showing' ? route('content.movies.list') : route('content.series.list') }}" class="border-b border-cc-border pb-1 text-[10px] font-bold uppercase tracking-[0.2em] text-cc-text-muted transition-colors hover:text-cc-accent">
                                    View full catalog
                                </a>
                            @endif
                        </header>

                        @if ($section['items']->isEmpty())
                            <x-ui.empty-state
                                title="No titles found"
                                description="This section is still empty. Add content from the admin panel or sync metadata imports."
                                :action-label="$adminActionHref ? 'Go to admin' : null"
                                :action-href="$adminActionHref"
                            />
                        @else
                            @if ($isHomeView)
                                <x-ui.rail :aria-label="$section['title'] . ' rail'" track-class="pb-3">
                                    @foreach ($section['items'] as $content)
                                        @php
                                            $detailUrl = url('/' . ($content->type === 'serie' ? 'series' : 'movies') . '/' . $content->id);
                                            $releaseYear = $content->release_date ? substr((string) $content->release_date, 0, 4) : 'N/A';
                                            $meta = $content->rating ? 'Rating ' . number_format((float) $content->rating, 1) : null;
                                        @endphp

                                        <x-ui.card-film
                                            :title="$content->title"
                                            :href="$detailUrl"
                                            :image="$content->poster_url"
                                            :year="$releaseYear"
                                            :eyebrow="$content->type === 'serie' ? 'Series' : 'Film'"
                                            :meta="$meta"
                                        />
                                    @endforeach
                                </x-ui.rail>
                            @else
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                                    @foreach ($section['items'] as $content)
                                        @php
                                            $detailUrl = url('/' . ($content->type === 'serie' ? 'series' : 'movies') . '/' . $content->id);
                                            $releaseYear = $content->release_date ? substr((string) $content->release_date, 0, 4) : 'N/A';
                                            $meta = $content->rating ? 'Rating ' . number_format((float) $content->rating, 1) : null;
                                        @endphp

                                        <x-ui.card-film
                                            :title="$content->title"
                                            :href="$detailUrl"
                                            :image="$content->poster_url"
                                            :year="$releaseYear"
                                            :eyebrow="$content->type === 'serie' ? 'Series' : 'Film'"
                                            :meta="$meta"
                                            full-width
                                        />
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </section>
                @endforeach
            </div>
        </div>
    </section>
</x-app-layout>
