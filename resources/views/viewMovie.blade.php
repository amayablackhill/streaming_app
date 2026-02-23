<x-app-layout>
    @php
        $posterUrl = $content->poster_url;
        $tmdbPosterPath = null;
        if (is_string($posterUrl) && preg_match('#^https://image\.tmdb\.org/t/p/(?:w\d+|original)(/.*)$#', $posterUrl, $matches) === 1) {
            $tmdbPosterPath = $matches[1];
        }
        $posterMobileSrc = $tmdbPosterPath ? 'https://image.tmdb.org/t/p/w342' . $tmdbPosterPath : null;
        $posterDesktopSrc = $tmdbPosterPath ? 'https://image.tmdb.org/t/p/w500' . $tmdbPosterPath : null;
        $backdropUrl = $content->backdrop_url ?: $posterUrl;
        $overviewText = $content->display_overview ?: 'No synopsis available yet.';
        $runtimeValue = $content->display_runtime;

        $releaseYear = $content->release_date ? substr((string) $content->release_date, 0, 4) : 'N/A';
        $genreName = optional($content->genre)->name ?? 'Uncategorized';
        $durationLabel = $runtimeValue ? $runtimeValue . ' min' : 'N/A';
        $ratingLabel = isset($content->rating) ? number_format((float) $content->rating, 1) : null;

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
                'content/max_' . $videoValue,
                'content/mid_' . $videoValue,
                'content/min_' . $videoValue,
                'content/' . $videoValue,
                'movies/' . $videoValue,
            ];
        }

        $playbackPath = collect($videoCandidates)->first(
            fn (string $path): bool => \Illuminate\Support\Facades\Storage::disk('public')->exists($path)
        );
        $playbackUrl = $playbackPath ? asset('storage/' . $playbackPath) : null;
        $hlsPlaybackUrl = isset($hlsUrl) && is_string($hlsUrl) && $hlsUrl !== '' ? $hlsUrl : null;
        $adminActionEnabled = auth()->check() && auth()->user()->canAccessAdminPanel();
        $tmdbSyncLabel = $content->tmdb_last_synced_at
            ? $content->tmdb_last_synced_at->diffForHumans()
            : 'Never';
        $sourceLabel = ($content->tmdb_id && $content->tmdb_type) ? 'TMDB linked' : 'Local entry';
        $breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Films', 'href' => route('content.movies.list')],
            ['label' => $content->title],
        ];
    @endphp

    <article class="bg-cc-bg-primary lg:h-[calc(100vh-4rem)]">
        <main class="flex flex-col lg:h-full lg:flex-row lg:items-stretch lg:overflow-hidden">
            <section class="relative h-[52vh] w-full overflow-hidden bg-cc-bg-elevated lg:h-full lg:w-[45%] lg:shrink-0">
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
                    <h1 class="font-serif text-4xl text-white">{{ $content->title }}</h1>
                    <p class="mt-1 text-sm text-cc-accent">{{ $releaseYear }}  -  {{ $content->director ?: 'Unknown Director' }}</p>
                </div>
            </section>

            <section class="w-full bg-cc-bg-primary lg:h-full lg:w-[55%] lg:overflow-y-auto lg:overscroll-contain">
                <div class="mx-auto flex w-full max-w-3xl flex-col px-5 py-10 sm:px-8 lg:px-12 lg:py-16">
                    <x-ui.breadcrumbs :items="$breadcrumbs" class="mb-5" />

                    @if (session('status'))
                        <x-ui.alert tone="success" title="Update">{{ session('status') }}</x-ui.alert>
                    @endif

                    @if (session('error'))
                        <x-ui.alert tone="error" title="Update failed">{{ session('error') }}</x-ui.alert>
                    @endif

                    <div class="mb-6 hidden border-b border-cc-border pb-8 lg:grid lg:grid-cols-[minmax(0,1fr)_15rem] lg:items-start lg:gap-8">
                        <div class="min-w-0">
                            <h1 class="font-serif text-6xl leading-[1.05] text-white xl:text-7xl">{{ $content->title }}</h1>
                        </div>

                        <div class="w-full max-w-[15rem] justify-self-end overflow-hidden rounded-sm border border-cc-border bg-cc-bg-elevated">
                            <div class="aspect-[2/3]">
                                @if ($posterUrl)
                                    <img
                                        src="{{ $posterUrl }}"
                                        @if ($posterMobileSrc && $posterDesktopSrc)
                                            srcset="{{ $posterMobileSrc }} 342w, {{ $posterDesktopSrc }} 500w"
                                            sizes="(max-width: 1023px) 40vw, 15rem"
                                        @endif
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

                    <div class="mb-10 flex flex-wrap items-center gap-3">
                        @if ($youtubeId || $hlsPlaybackUrl || $playbackUrl)
                            <a
                                href="#player"
                                class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-cc-bg-primary transition-all cc-motion-base hover:bg-cc-text-primary"
                            >
                                <x-ui.icon name="play" class="h-4 w-4" />
                                Watch Film
                            </a>
                        @endif

                        <a
                            href="{{ route('content.movies.list') }}"
                            class="inline-flex items-center gap-2 rounded-sm px-3 py-2 text-sm text-cc-text-secondary transition-colors cc-motion-base hover:bg-cc-bg-elevated hover:text-cc-text-primary"
                        >
                            <x-ui.icon name="arrow-left" class="h-4 w-4" />
                            Back to catalog
                        </a>

                    </div>

                    <section class="mb-6">
                        <h2 class="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-cc-text-muted">Metadata</h2>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <x-ui.badge tone="neutral">{{ $content->director ?: 'Unknown Director' }}</x-ui.badge>
                            <x-ui.badge tone="neutral">{{ $releaseYear }}</x-ui.badge>
                            <x-ui.badge tone="neutral">{{ $genreName }}</x-ui.badge>
                            <x-ui.badge tone="neutral">{{ $durationLabel }}</x-ui.badge>
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

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <x-ui.button :href="route('admin.home')" variant="ghost" size="sm">Admin panel</x-ui.button>
                                <x-ui.button :href="route('content.edit', $content->id)" variant="ghost" size="sm">Edit film</x-ui.button>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-cc-border pt-4">
                                <x-ui.badge tone="neutral">Type Film</x-ui.badge>
                                <x-ui.badge tone="neutral">{{ $sourceLabel }}</x-ui.badge>
                                <x-ui.badge tone="neutral">Synced {{ $tmdbSyncLabel }}</x-ui.badge>
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
                                <video id="detail-hls-player" controls preload="metadata" poster="{{ $posterUrl ?: '' }}" class="h-full w-full bg-black"></video>
                            @elseif ($playbackUrl)
                                <video controls preload="metadata" poster="{{ $posterUrl ?: '' }}" class="h-full w-full bg-black">
                                    <source src="{{ $playbackUrl }}" type="video/mp4">
                                    Your browser does not support HTML5 video.
                                </video>
                            @else
                                <div class="flex h-full items-center justify-center p-6 text-center text-sm text-cc-text-secondary">
                                    Trailer not available yet for this title.
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="mt-12 border-t border-cc-border pt-8">
                        <h2 class="mb-5 text-xs font-bold uppercase tracking-[0.16em] text-cc-text-muted">Technical Details</h2>
                        <div class="grid grid-cols-1 gap-y-5 gap-x-8 text-sm md:grid-cols-2">
                            <div class="flex flex-col gap-1">
                                <span class="text-cc-text-muted">Title</span>
                                <span class="text-cc-text-primary">{{ $content->title }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-cc-text-muted">Duration</span>
                                <span class="text-cc-text-primary">{{ $durationLabel }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-cc-text-muted">Director</span>
                                <span class="text-cc-text-primary">{{ $content->director ?: 'Unknown' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-cc-text-muted">Genre</span>
                                <span class="text-cc-text-primary">{{ $genreName }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="text-cc-text-muted">Release Date</span>
                                <span class="text-cc-text-primary">{{ $content->release_date ?: 'N/A' }}</span>
                            </div>
                            @if ($ratingLabel)
                                <div class="flex flex-col gap-1">
                                    <span class="text-cc-text-muted">Rating</span>
                                    <span class="text-cc-text-primary">{{ $ratingLabel }}</span>
                                </div>
                            @endif
                        </div>
                    </section>
                </div>
            </section>
        </main>
    </article>

    @if (!$youtubeId && $hlsPlaybackUrl)
        <script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const player = document.getElementById('detail-hls-player');
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
