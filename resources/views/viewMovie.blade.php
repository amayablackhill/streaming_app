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
    @endphp

    <article class="cc-stack-6">
        <header class="cc-stack-2">
            <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Film Detail</p>
            <h1 class="cc-title-display">{{ $content->title }}</h1>
            <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                Watch the official trailer or demo playback and review the editorial metadata.
            </p>
        </header>

        <x-ui.alert tone="info" title="Playback policy">
            Catalog playback uses trailers and short demo clips only.
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
                    @elseif ($playbackUrl)
                        <video controls preload="metadata" poster="{{ $posterUrl }}" class="h-full w-full bg-black">
                            <source src="{{ $playbackUrl }}" type="video/mp4">
                            Your browser does not support HTML5 video.
                        </video>
                    @else
                        <x-ui.empty-state
                            title="Trailer not available"
                            description="This title has no trailer URL or local demo clip configured yet."
                        />
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge tone="neutral">Film</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $releaseYear }}</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $durationLabel }}</x-ui.badge>
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
                        <dt class="text-cc-caption uppercase tracking-label text-cc-text-muted">Director</dt>
                        <dd class="text-cc-text-primary">{{ $content->director ?: 'Unknown' }}</dd>
                    </div>
                    <div>
                        <dt class="text-cc-caption uppercase tracking-label text-cc-text-muted">Genre</dt>
                        <dd class="text-cc-text-primary">{{ $genreName }}</dd>
                    </div>
                    <div>
                        <dt class="text-cc-caption uppercase tracking-label text-cc-text-muted">Release date</dt>
                        <dd class="text-cc-text-primary">{{ $content->release_date ?: 'N/A' }}</dd>
                    </div>
                </dl>

                <x-ui.button href="{{ route('content.movies.list') }}" variant="secondary" size="sm">
                    Back to films
                </x-ui.button>
            </aside>
        </div>

        <section class="cc-surface cc-stack-2 p-4 sm:p-5">
            <h2 class="cc-title-section">Synopsis</h2>
            <p class="text-sm leading-editorial text-cc-text-secondary">
                {{ $content->description ?: 'No synopsis available yet.' }}
            </p>
        </section>
    </article>
</x-app-layout>
