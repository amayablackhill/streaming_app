<x-app-layout>
    @php
        $posterUrl = $content->poster_url;
        $backdropUrl = $content->backdrop_url ?: $posterUrl;
        $overviewText = $content->display_overview ?: 'No synopsis available yet.';
        $runtimeValue = $content->display_runtime;

        $releaseYear = $content->release_date ? substr((string) $content->release_date, 0, 4) : 'N/A';
        $genreName = optional($content->genre)->name ?? 'Uncategorized';
        $ratingLabel = isset($content->rating) ? number_format((float) $content->rating, 1) : null;

        $seasons = $content->seasons->sortBy('season_number')->values();
        $seasonEntries = $seasons->map(function (\App\Models\Season $season) {
            return [
                'season' => $season,
                'episodes' => $season->episodes->sortBy('episode_number')->values(),
            ];
        });
        $episodesCount = $seasonEntries->sum(fn (array $entry): int => $entry['episodes']->count());

        $firstSeasonEntry = $seasonEntries->first(fn (array $entry): bool => $entry['episodes']->isNotEmpty());
        $firstEpisode = $firstSeasonEntry['episodes']->first() ?? null;
        $firstEpisodeUrl = ($firstSeasonEntry && $firstEpisode)
            ? route('episodes.watch', [$content->id, $firstSeasonEntry['season']->id, $firstEpisode->id])
            : null;

        $videoValue = trim((string) ($content->video ?? ''));
        $youtubeId = null;

        if ($videoValue !== '') {
            if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/', $videoValue, $matches)) {
                $youtubeId = $matches[1];
            } elseif (preg_match('/^[A-Za-z0-9_-]{11}$/', $videoValue)) {
                $youtubeId = $videoValue;
            }
        }

        $videoCandidates = [];
        if ($videoValue !== '') {
            $videoCandidates = [
                'series/' . $videoValue,
                'content/max_' . $videoValue,
                'content/mid_' . $videoValue,
                'content/min_' . $videoValue,
                'content/' . $videoValue,
            ];
        }

        $playbackPath = collect($videoCandidates)->first(
            fn (string $path): bool => \Illuminate\Support\Facades\Storage::disk('public')->exists($path)
        );
        $playbackUrl = $playbackPath ? asset('storage/' . $playbackPath) : null;
        $hlsPlaybackUrl = isset($hlsUrl) && is_string($hlsUrl) && $hlsUrl !== '' ? $hlsUrl : null;
        $hasFeaturedPlayback = $youtubeId || $hlsPlaybackUrl || $playbackUrl;

        $adminActionEnabled = auth()->check() && auth()->user()->canAccessAdminPanel();
        $tmdbEpisodesImportedCount = $seasons->sum(
            fn (\App\Models\Season $season): int => $season->episodes->whereNotNull('tmdb_id')->count()
        );
        $tmdbSyncLabel = $content->tmdb_last_synced_at
            ? $content->tmdb_last_synced_at->diffForHumans()
            : 'Never';

        $breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Series', 'href' => route('content.series.list')],
            ['label' => $content->title],
        ];
    @endphp

    <article class="min-h-[calc(100vh-4rem)] bg-cc-bg-primary">
        <main class="flex min-h-[calc(100vh-4rem)] flex-col lg:flex-row lg:items-start">
            <section class="relative h-[52vh] w-full overflow-hidden bg-cc-bg-elevated lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:w-[45%] lg:self-start">
                <div class="absolute inset-0 bg-gradient-to-t from-cc-bg-primary via-transparent to-transparent opacity-65 lg:opacity-35"></div>
                @if ($backdropUrl)
                    <img
                        src="{{ $backdropUrl }}"
                        alt="{{ $content->title }} backdrop"
                        loading="lazy"
                        draggable="false"
                        width="1920"
                        height="1080"
                        class="h-full w-full select-none object-cover"
                    >
                @else
                    <div class="flex h-full w-full items-center justify-center bg-cc-bg-elevated text-center text-sm text-cc-text-muted">
                        No artwork available
                    </div>
                @endif

                <div class="absolute bottom-0 left-0 z-10 w-full bg-gradient-to-t from-cc-bg-primary to-transparent p-5 lg:hidden">
                    <h1 class="font-serif text-3xl text-white">{{ $content->title }}</h1>
                    <p class="mt-1 text-sm text-cc-accent">{{ $releaseYear }}  -  {{ $content->director ?: 'Unknown Creator' }}</p>
                </div>
            </section>

            <section class="w-full bg-cc-bg-primary lg:w-[55%]">
                <div class="mx-auto flex h-full w-full max-w-3xl flex-col px-5 py-10 sm:px-8 lg:px-12 lg:py-16">
                    <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-5" />

                    @if (session('status'))
                        <x-ui.alert tone="success" title="Update">{{ session('status') }}</x-ui.alert>
                    @endif

                    @if (session('error'))
                        <x-ui.alert tone="error" title="Update failed">{{ session('error') }}</x-ui.alert>
                    @endif

                    <div class="mb-8 hidden items-start justify-between gap-6 lg:flex">
                        <div>
                            <h1 class="font-serif text-5xl leading-[1.1] text-white xl:text-6xl">{{ $content->title }}</h1>
                        </div>

                        <div class="w-36 shrink-0 overflow-hidden rounded-sm border border-cc-border bg-cc-bg-elevated">
                            <div class="aspect-[2/3]">
                                @if ($posterUrl)
                                    <img
                                        src="{{ $posterUrl }}"
                                        alt="{{ $content->title }} poster"
                                        loading="lazy"
                                        width="500"
                                        height="750"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <div class="flex h-full items-center justify-center p-3 text-center text-xs text-cc-text-muted">
                                        Poster unavailable
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-10 flex flex-wrap items-center gap-3 border-b border-cc-border pb-6">
                        @if ($hasFeaturedPlayback)
                            <a
                                href="#player"
                                class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-cc-bg-primary transition-all cc-motion-base hover:bg-cc-text-primary"
                            >
                                <x-ui.icon name="play" class="h-4 w-4" />
                                Watch Trailer
                            </a>
                        @endif

                        <x-ui.button :href="$firstEpisodeUrl" variant="secondary" size="sm" :disabled="!$firstEpisodeUrl">
                            Watch First Episode
                        </x-ui.button>

                        <a
                            href="{{ route('content.series.list') }}"
                            class="inline-flex items-center gap-2 rounded-sm px-3 py-2 text-sm text-cc-text-secondary transition-colors cc-motion-base hover:bg-cc-bg-elevated hover:text-cc-text-primary"
                        >
                            <x-ui.icon name="arrow-left" class="h-4 w-4" />
                            Back to catalog
                        </a>
                    </div>

                    <section class="mb-6">
                        <h2 class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-cc-text-muted">Metadata</h2>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <x-ui.badge tone="neutral">{{ $content->director ?: 'Unknown Creator' }}</x-ui.badge>
                            <x-ui.badge tone="neutral">{{ $releaseYear }}</x-ui.badge>
                            <x-ui.badge tone="neutral">{{ $genreName }}</x-ui.badge>
                            <x-ui.badge tone="neutral">{{ $seasons->count() }} seasons</x-ui.badge>
                            <x-ui.badge tone="neutral">{{ $episodesCount }} episodes</x-ui.badge>
                            @if ($ratingLabel)
                                <x-ui.badge tone="neutral">Rating {{ $ratingLabel }}</x-ui.badge>
                            @endif
                        </div>
                    </section>

                    <article class="mb-8">
                        <p class="max-w-2xl text-sm leading-7 text-cc-text-secondary">
                            {{ $overviewText }}
                        </p>
                    </article>

                    @if ($adminActionEnabled)
                        <section class="mb-8 rounded-sm border border-cc-border bg-cc-bg-surface p-4">
                            <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-cc-text-muted">Admin Controls</h2>

                            <div class="mt-3 flex flex-wrap items-center justify-end gap-2">
                                <x-ui.button :href="route('seasons.manage', $content->id)" variant="ghost" size="sm">Manage seasons</x-ui.button>
                                <x-ui.button :href="route('content.edit', $content->id)" variant="secondary" size="sm">Edit series</x-ui.button>
                                @if ($content->tmdb_type === 'tv' && $content->tmdb_id)
                                    <form method="POST" action="{{ route('admin.tmdb.series.episodes.import', $content) }}" class="inline-flex">
                                        @csrf
                                        <x-ui.button type="submit" variant="ghost" size="sm">Import episodes</x-ui.button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.tmdb.series.episodes.import', $content) }}" class="inline-flex">
                                        @csrf
                                        <input type="hidden" name="all" value="1">
                                        <x-ui.button type="submit" variant="ghost" size="sm">Import all seasons</x-ui.button>
                                    </form>
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-cc-border pt-3">
                                <x-ui.badge tone="premium">Role admin</x-ui.badge>
                                <x-ui.badge tone="neutral">Type Series</x-ui.badge>
                                @if ($content->tmdb_type === 'tv' && $content->tmdb_id)
                                    <x-ui.badge tone="neutral">{{ $seasons->count() }} TMDB seasons</x-ui.badge>
                                    <x-ui.badge tone="neutral">{{ $tmdbEpisodesImportedCount }} TMDB episodes</x-ui.badge>
                                    <x-ui.badge tone="neutral">Synced {{ $tmdbSyncLabel }}</x-ui.badge>
                                @else
                                    <x-ui.badge tone="neutral">Local entry</x-ui.badge>
                                @endif
                            </div>
                        </section>
                    @endif

                    <section id="player" class="mt-10 overflow-hidden rounded-sm border border-cc-border bg-cc-bg-elevated">
                        <div class="aspect-video bg-black">
                            @if ($youtubeId)
                                <iframe
                                    src="https://www.youtube-nocookie.com/embed/{{ $youtubeId }}"
                                    title="Trailer for {{ $content->title }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen
                                    class="h-full w-full"
                                ></iframe>
                            @elseif ($hlsPlaybackUrl)
                                <video id="detail-hls-player-series" controls preload="metadata" poster="{{ $posterUrl ?: '' }}" class="h-full w-full bg-black"></video>
                            @elseif ($playbackUrl)
                                <video controls preload="metadata" poster="{{ $posterUrl ?: '' }}" class="h-full w-full bg-black">
                                    <source src="{{ $playbackUrl }}" type="video/mp4">
                                    Your browser does not support HTML5 video.
                                </video>
                            @else
                                <div class="flex h-full items-center justify-center p-6 text-center text-sm text-cc-text-secondary">
                                    Trailer not available yet for this series.
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="mt-12 border-t border-cc-border pt-8">
                        <header class="mb-5 flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-[0.16em] text-cc-text-muted">Seasons & Episodes</h2>
                                <p class="mt-2 text-sm text-cc-text-secondary">{{ $seasons->count() }} seasons - {{ $episodesCount }} episodes</p>
                            </div>
                        </header>

                        @if ($seasonEntries->isEmpty())
                            <x-ui.empty-state
                                title="No seasons available"
                                description="This series still has no seasons published. Add them from the admin panel."
                                :action-label="$adminActionEnabled ? 'Go to admin' : null"
                                :action-href="$adminActionEnabled ? route('seasons.manage', $content->id) : null"
                            />
                        @else
                            <div class="cc-stack-4">
                                @foreach ($seasonEntries as $entry)
                                    @php
                                        /** @var \App\Models\Season $season */
                                        $season = $entry['season'];
                                        /** @var \Illuminate\Support\Collection<int, \App\Models\Episode> $episodes */
                                        $episodes = $entry['episodes'];
                                    @endphp

                                    <section class="cc-elevated p-4" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                                        <header class="flex flex-wrap items-center justify-between gap-3">
                                            <div class="cc-stack-2">
                                                <h3 class="text-lg font-medium text-cc-text-primary">Season {{ $season->season_number }}</h3>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <x-ui.badge tone="neutral">{{ $episodes->count() }} episodes</x-ui.badge>
                                                    @if ($season->release_date)
                                                        <x-ui.badge tone="neutral">{{ $season->release_date->toDateString() }}</x-ui.badge>
                                                    @endif
                                                </div>
                                            </div>

                                            <x-ui.button type="button" variant="ghost" size="sm" @click="open = !open" x-text="open ? 'Hide episodes' : 'Show episodes'">
                                                Hide episodes
                                            </x-ui.button>
                                        </header>

                                        <div
                                            class="mt-4"
                                            x-show="open"
                                            x-transition:enter="transition-opacity cc-motion-base"
                                            x-transition:leave="transition-opacity cc-motion-fast cc-motion-exit"
                                        >
                                            @if ($episodes->isEmpty())
                                                <x-ui.alert tone="warning" title="No episodes">
                                                    This season does not have episodes yet.
                                                </x-ui.alert>
                                            @else
                                                <ul class="cc-stack-2">
                                                    @foreach ($episodes as $episode)
                                                        <li class="cc-surface flex flex-col gap-3 p-3 sm:flex-row sm:items-center sm:justify-between">
                                                            <div class="cc-stack-2">
                                                                <p class="text-sm font-medium text-cc-text-primary">
                                                                    E{{ str_pad((string) $episode->episode_number, 2, '0', STR_PAD_LEFT) }} - {{ $episode->title }}
                                                                </p>
                                                                <p class="text-xs text-cc-text-secondary">
                                                                    {{ ($episode->runtime_minutes ?: $episode->duration) ? ($episode->runtime_minutes ?: $episode->duration) . ' min' : 'Duration N/A' }}
                                                                    @if ($episode->release_date)
                                                                        - {{ $episode->release_date->toDateString() }}
                                                                    @endif
                                                                </p>
                                                                @if ($episode->plot)
                                                                    <p class="text-xs leading-editorial text-cc-text-muted">
                                                                        {{ \Illuminate\Support\Str::limit($episode->plot, 140) }}
                                                                    </p>
                                                                @endif
                                                            </div>

                                                            <x-ui.button
                                                                :href="route('episodes.watch', [$content->id, $season->id, $episode->id])"
                                                                variant="secondary"
                                                                size="sm"
                                                            >
                                                                Watch episode
                                                            </x-ui.button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </section>
                                @endforeach
                            </div>
                        @endif
                    </section>
                </div>
            </section>
        </main>
    </article>

    @if (!$youtubeId && $hlsPlaybackUrl)
        <script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const player = document.getElementById('detail-hls-player-series');
                const source = @js($hlsPlaybackUrl);

                if (!player || !source) {
                    return;
                }

                if (player.canPlayType('application/vnd.apple.mpegurl')) {
                    player.src = source;
                    return;
                }

                if (window.Hls && window.Hls.isSupported()) {
                    const hls = new window.Hls();
                    hls.loadSource(source);
                    hls.attachMedia(player);
                    return;
                }

                player.outerHTML = '<div class="flex h-full items-center justify-center p-6 text-center text-sm text-cc-text-secondary">HLS playback is not supported in this browser.</div>';
            });
        </script>
    @endif
</x-app-layout>
