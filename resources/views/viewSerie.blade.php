<x-app-layout>
    @php
        $posterUrl = $content->picture
            ? asset('storage/series/' . $content->picture)
            : asset('storage/logo/netflick_logo_definitive.png');

        $releaseYear = $content->release_date ? substr((string) $content->release_date, 0, 4) : 'N/A';
        $genreName = optional($content->genre)->name ?? 'Uncategorized';
        $ratingLabel = isset($content->rating) ? number_format((float) $content->rating, 1) : null;

        $seasons = $content->seasons->sortBy('season_number')->values();
        $episodesCount = $seasons->sum(fn ($season) => $season->episodes->count());

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
    @endphp

    <article class="cc-stack-6">
        <header class="cc-stack-2">
            <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Series Detail</p>
            <h1 class="cc-title-display">{{ $content->title }}</h1>
            <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                Explore seasons and episodes with direct playback access for each chapter.
            </p>
        </header>

        <x-ui.alert tone="info" title="Playback policy">
            Main catalog experience uses trailers and short legal demo clips.
        </x-ui.alert>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.7fr)_minmax(0,1fr)]">
            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                <div class="cc-elevated aspect-video overflow-hidden">
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
                        <video id="detail-hls-player-series" controls preload="metadata" poster="{{ $posterUrl }}" class="h-full w-full bg-black"></video>
                    @elseif ($playbackUrl)
                        <video controls preload="metadata" poster="{{ $posterUrl }}" class="h-full w-full bg-black">
                            <source src="{{ $playbackUrl }}" type="video/mp4">
                            Your browser does not support HTML5 video.
                        </video>
                    @else
                        <x-ui.empty-state
                            title="Trailer not available"
                            description="Add a YouTube trailer URL or a short demo clip to enable featured playback."
                        />
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge tone="neutral">Series</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $releaseYear }}</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $seasons->count() }} seasons</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $episodesCount }} episodes</x-ui.badge>
                    @if ($ratingLabel)
                        <x-ui.badge tone="premium">Rating {{ $ratingLabel }}</x-ui.badge>
                    @endif
                </div>
            </section>

            <aside class="cc-surface cc-stack-4 p-4 sm:p-5">
                <div class="overflow-hidden rounded-sm border border-cc-border bg-cc-bg-elevated">
                    <img
                        src="{{ $posterUrl }}"
                        alt="{{ $content->title }} poster"
                        loading="lazy"
                        class="h-auto w-full object-cover"
                    >
                </div>

                <dl class="cc-stack-2 text-sm leading-editorial text-cc-text-secondary">
                    <div>
                        <dt class="text-cc-caption uppercase tracking-label text-cc-text-muted">Genre</dt>
                        <dd class="text-cc-text-primary">{{ $genreName }}</dd>
                    </div>
                    <div>
                        <dt class="text-cc-caption uppercase tracking-label text-cc-text-muted">Release date</dt>
                        <dd class="text-cc-text-primary">{{ $content->release_date ?: 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-cc-caption uppercase tracking-label text-cc-text-muted">Creator / Director</dt>
                        <dd class="text-cc-text-primary">{{ $content->director ?: 'Unknown' }}</dd>
                    </div>
                </dl>

                <x-ui.button href="{{ route('content.series.list') }}" variant="secondary" size="sm">
                    Back to series
                </x-ui.button>
            </aside>
        </div>

        <section class="cc-surface cc-stack-4 p-4 sm:p-5">
            <header class="cc-stack-2">
                <h2 class="cc-title-section">Seasons & Episodes</h2>
                <p class="text-sm leading-editorial text-cc-text-secondary">
                    Browse each season and jump directly to episode playback.
                </p>
            </header>

            @if ($seasons->isEmpty())
                <x-ui.empty-state
                    title="No seasons available"
                    description="This series still has no seasons published. Add them from the admin panel."
                    :action-label="auth()->check() ? 'Go to dashboard' : null"
                    :action-href="auth()->check() ? route('dashboard') : null"
                />
            @else
                <div class="cc-stack-4">
                    @foreach ($seasons as $season)
                        @php
                            $episodes = $season->episodes->sortBy('episode_number')->values();
                        @endphp

                        <section class="cc-elevated cc-stack-3 p-4" x-data="{ open: true }">
                            <header class="flex flex-wrap items-center justify-between gap-3">
                                <div class="cc-stack-2">
                                    <h3 class="text-lg font-medium text-cc-text-primary">
                                        Season {{ $season->season_number }}
                                    </h3>
                                    <div class="flex items-center gap-2">
                                        <x-ui.badge tone="neutral">{{ $episodes->count() }} episodes</x-ui.badge>
                                        @if ($season->release_date)
                                            <x-ui.badge tone="neutral">{{ $season->release_date }}</x-ui.badge>
                                        @endif
                                    </div>
                                </div>

                                <x-ui.button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="open = !open"
                                    x-text="open ? 'Collapse' : 'Expand'"
                                >
                                    Collapse
                                </x-ui.button>
                            </header>

                            <div x-show="open" x-transition:enter="transition-opacity cc-motion-base" x-transition:leave="transition-opacity cc-motion-fast cc-motion-exit">
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
                                                        Episode {{ $episode->episode_number }}: {{ $episode->title }}
                                                    </p>
                                                    <p class="text-xs text-cc-text-secondary">
                                                        {{ $episode->duration ? $episode->duration . ' min' : 'Duration N/A' }}
                                                        @if ($episode->release_date)
                                                             -  {{ $episode->release_date }}
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

