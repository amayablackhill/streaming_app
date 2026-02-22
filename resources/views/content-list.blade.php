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

        $featuredMovie = $movies->firstWhere('is_featured', true)
            ?? $movies->sortByDesc(fn ($movie) => (float) ($movie->rating ?? 0))->first()
            ?? $movies->first();

        $featuredPoster = null;
        $featuredYear = 'N/A';
        $featuredDuration = null;
        if ($featuredMovie) {
            $featuredPoster = $featuredMovie->picture
                ? asset('storage/movies/' . $featuredMovie->picture)
                : asset('storage/logo/netflick_logo_definitive.png');
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
        <section class="relative flex min-h-[88vh] w-full items-end overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="{{ $featuredPoster }}" alt="{{ $featuredMovie->title }} feature artwork" class="h-full w-full object-cover">
                <div class="absolute inset-0 cc-hero-gradient"></div>
            </div>

            <div class="relative z-10 mx-auto w-full max-w-7xl px-6 pb-20 md:px-12 md:pb-28 lg:px-16">
                <div class="max-w-2xl">
                    <span class="mb-6 block text-[10px] font-bold uppercase tracking-[0.3em] text-cc-accent md:text-xs">Feature Presentation</span>
                    <h1 class="-ml-1 mb-8 font-serif text-6xl leading-none text-white sm:text-7xl md:text-8xl lg:text-9xl">
                        {{ $featuredMovie->title }}
                    </h1>

                    <div class="mb-8 flex flex-wrap items-center gap-4 text-xs uppercase tracking-widest text-cc-text-secondary md:text-sm">
                        <span>{{ $featuredMovie->director ?: 'Unknown Director' }}</span>
                        <span class="h-1 w-1 rounded-full bg-cc-accent"></span>
                        <span>{{ $featuredYear }}</span>
                        @if ($featuredDuration)
                            <span class="h-1 w-1 rounded-full bg-cc-accent"></span>
                            <span>{{ $featuredDuration }}</span>
                        @endif
                    </div>

                    <p class="mb-10 max-w-xl font-serif text-xl italic leading-relaxed text-cc-text-primary md:text-2xl">
                        {{ $featuredMovie->description ?: 'A curated spotlight from the Cineclub archive.' }}
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <x-ui.button :href="url('/movies/' . $featuredMovie->id)" variant="secondary" size="lg" class="border-white bg-white text-cc-bg-primary hover:bg-cc-accent hover:text-white">
                            <span class="material-symbols-outlined text-base">play_arrow</span>
                            Watch Film
                        </x-ui.button>
                        <x-ui.button href="#catalog-sections" variant="ghost" size="lg" class="border border-white/30 text-white hover:border-white hover:bg-white/10">
                            <span class="material-symbols-outlined text-base">bookmark</span>
                            Explore Catalog
                        </x-ui.button>
                        @if ($adminActionHref)
                            <x-ui.button :href="$adminActionHref" variant="ghost" size="lg" class="border border-white/30 text-white hover:border-white hover:bg-white/10">
                                <span class="material-symbols-outlined text-base">dashboard</span>
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
                    <x-ui.badge tone="neutral">{{ $contents->count() }} total</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $movies->count() }} films</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $series->count() }} series</x-ui.badge>
                    @if ($adminActionHref)
                        <x-ui.button :href="$adminActionHref" variant="ghost" size="sm">Admin</x-ui.button>
                        <x-ui.button :href="route('content.add')" variant="secondary" size="sm">Add Content</x-ui.button>
                    @endif
                </div>
            </header>

            <div class="cc-divider mt-6"></div>
        @endunless

        <div class="mt-6" x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 180)">
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
                                </x-ui.rail>
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
                        @endif
                    </section>
                @endforeach
            </div>
        </div>
    </section>

    @if ($isHomeView)
        <footer class="mx-6 mb-8 border border-cc-border bg-cc-bg-surface py-16 md:mx-12 lg:mx-16">
            <div class="mx-auto max-w-2xl px-6 text-center">
                <h3 class="mb-6 font-serif text-4xl text-white md:text-5xl">Join the club.</h3>
                <p class="mb-10 font-serif text-lg italic text-cc-text-secondary">
                    Weekly curation of the finest cinema delivered to your inbox. No algorithms, just taste.
                </p>
                <div class="mb-10 flex flex-col gap-0 sm:flex-row">
                    <input type="email" placeholder="EMAIL ADDRESS" class="cc-input flex-1 border-white/10 bg-cc-bg-primary/50 px-6 py-4 text-sm uppercase tracking-[0.16em] text-white placeholder:text-white/30 focus-visible:border-cc-accent">
                    <button type="button" class="bg-white px-8 py-4 text-xs font-bold uppercase tracking-[0.2em] text-cc-bg-primary transition-colors hover:bg-cc-accent hover:text-white">
                        Subscribe
                    </button>
                </div>
                <div class="mb-8 flex flex-wrap justify-center gap-8">
                    <a href="#" class="text-[10px] font-bold uppercase tracking-[0.2em] text-cc-text-muted transition-colors hover:text-white">About</a>
                    <a href="#" class="text-[10px] font-bold uppercase tracking-[0.2em] text-cc-text-muted transition-colors hover:text-white">Credits</a>
                    <a href="https://github.com" target="_blank" rel="noopener" class="text-[10px] font-bold uppercase tracking-[0.2em] text-cc-text-muted transition-colors hover:text-white">GitHub</a>
                </div>
                <p class="text-[10px] uppercase tracking-[0.3em] text-white/20">Cineclub Curated Archive</p>
            </div>
        </FOOTER>
    @endif
</x-app-layout>
