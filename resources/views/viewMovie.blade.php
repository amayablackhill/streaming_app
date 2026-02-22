<x-app-layout>
    @php
        $posterUrl = $content->picture
            ? asset('storage/movies/' . $content->picture)
            : asset('storage/logo/netflick_logo_definitive.png');

        $releaseYear = $content->release_date ? substr((string) $content->release_date, 0, 4) : 'N/A';
        $genreName = optional($content->genre)->name ?? 'Uncategorized';
        $durationLabel = $content->duration ? $content->duration . ' min' : 'N/A';
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
    @endphp

    <article class="min-h-[calc(100vh-4rem)] bg-cc-bg-primary">
        <main class="flex min-h-[calc(100vh-4rem)] flex-col lg:flex-row">
            <section class="relative h-[52vh] w-full overflow-hidden bg-cc-bg-elevated lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:w-[45%]">
                <div class="absolute inset-0 bg-gradient-to-t from-cc-bg-primary via-transparent to-transparent opacity-65 lg:opacity-35"></div>
                <img
                    src="{{ $posterUrl }}"
                    alt="{{ $content->title }} poster"
                    loading="lazy"
                    class="h-full w-full object-cover transition-transform duration-[2000ms] ease-soft hover:scale-105"
                >

                <div class="absolute bottom-0 left-0 z-10 w-full bg-gradient-to-t from-cc-bg-primary to-transparent p-5 lg:hidden">
                    <h1 class="font-serif text-3xl text-white">{{ $content->title }}</h1>
                    <p class="mt-1 text-sm text-cc-accent">{{ $releaseYear }}  -  {{ $content->director ?: 'Unknown Director' }}</p>
                </div>
            </section>

            <section class="w-full bg-cc-bg-primary lg:w-[55%]">
                <div class="mx-auto flex h-full w-full max-w-3xl flex-col px-5 py-10 sm:px-8 lg:px-12 lg:py-16">
                    <div class="mb-8 flex items-center gap-2 text-xs font-bold uppercase tracking-[0.16em] text-cc-accent">
                        <span>Editorial</span>
                        <span class="text-cc-text-muted">/</span>
                        <span>Film Detail</span>
                    </div>

                    <div class="mb-8 hidden lg:block">
                        <h1 class="font-serif text-5xl leading-[1.1] text-white xl:text-6xl">{{ $content->title }}</h1>
                        <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-cc-text-secondary">
                            <span class="text-cc-text-primary">{{ $content->director ?: 'Unknown Director' }}</span>
                            <span class="h-1 w-1 rounded-full bg-cc-text-muted/70"></span>
                            <span>{{ $releaseYear }}</span>
                            <span class="h-1 w-1 rounded-full bg-cc-text-muted/70"></span>
                            <span>{{ $genreName }}</span>
                            @if ($ratingLabel)
                                <span class="rounded-sm border border-cc-border px-2 py-0.5 text-[11px] uppercase tracking-[0.08em] text-cc-text-primary">
                                    Rating {{ $ratingLabel }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-10 flex flex-wrap items-center gap-3 border-b border-cc-border pb-6">
                        @if ($youtubeId || $hlsPlaybackUrl || $playbackUrl)
                            <a
                                href="#player"
                                class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-cc-bg-primary transition-all cc-motion-base hover:bg-cc-text-primary"
                            >
                                <span class="material-symbols-outlined text-base">play_arrow</span>
                                Watch Film
                            </a>
                        @endif

                        <a
                            href="{{ route('content.movies.list') }}"
                            class="inline-flex items-center gap-2 rounded-sm px-3 py-2 text-sm text-cc-text-secondary transition-colors cc-motion-base hover:bg-cc-bg-elevated hover:text-cc-text-primary"
                        >
                            <span class="material-symbols-outlined text-base">arrow_back</span>
                            Back to catalog
                        </a>

                        <button type="button" class="ml-auto inline-flex items-center gap-2 rounded-sm px-3 py-2 text-sm text-cc-text-muted transition-colors cc-motion-base hover:text-cc-text-primary">
                            <span class="material-symbols-outlined text-base">share</span>
                        </button>
                    </div>

                    <article class="space-y-5">
                        <p class="font-serif text-xl italic leading-relaxed text-cc-text-primary">
                            {{ $content->description ?: 'No synopsis available yet.' }}
                        </p>
                    </article>

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
                                <video id="detail-hls-player" controls preload="metadata" poster="{{ $posterUrl }}" class="h-full w-full bg-black"></video>
                            @elseif ($playbackUrl)
                                <video controls preload="metadata" poster="{{ $posterUrl }}" class="h-full w-full bg-black">
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

