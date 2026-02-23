<x-app-layout>
    @php
        $seriesPoster = $content->poster_url;
        $seriesBackdrop = $content->backdrop_url ?: $seriesPoster;
        $episodeLabel = sprintf('S%02dE%02d', (int) $season->season_number, (int) $episode->episode_number);

        $episodePlot = trim((string) ($episode->plot ?? ''));
        $seriesOverview = trim((string) ($content->display_overview ?? ''));
        $overviewText = $episodePlot !== '' ? $episodePlot : ($seriesOverview !== '' ? $seriesOverview : 'No synopsis available for this episode.');

        $episodeRuntime = (int) ($episode->runtime_minutes ?? 0);
        if ($episodeRuntime <= 0) {
            $episodeRuntime = (int) ($episode->duration ?? 0);
        }
        $runtimeLabel = $episodeRuntime > 0 ? "{$episodeRuntime} min" : 'Runtime N/A';

        $episodeVideoUrl = null;
        if (!empty($episode->episode_path)) {
            $episodePath = (string) $episode->episode_path;
            $candidatePaths = [
                $episodePath,
                'episodes/' . ltrim($episodePath, '/'),
            ];

            foreach ($candidatePaths as $candidatePath) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($candidatePath)) {
                    $episodeVideoUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($candidatePath);
                    break;
                }
            }
        }

        $episodeCoverUrl = null;
        $stillPath = trim((string) ($episode->still_path ?? ''));
        $coverPath = trim((string) ($episode->cover_path ?? ''));

        if ($stillPath !== '') {
            $episodeCoverUrl = \Illuminate\Support\Str::startsWith($stillPath, ['http://', 'https://'])
                ? $stillPath
                : "https://image.tmdb.org/t/p/w780/" . ltrim($stillPath, '/');
        } elseif ($coverPath !== '') {
            if (\Illuminate\Support\Str::startsWith($coverPath, ['http://', 'https://'])) {
                $episodeCoverUrl = $coverPath;
            } elseif (\Illuminate\Support\Str::startsWith($coverPath, '/')) {
                $episodeCoverUrl = "https://image.tmdb.org/t/p/w780/" . ltrim($coverPath, '/');
            } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists('episodes/' . $coverPath)) {
                $episodeCoverUrl = \Illuminate\Support\Facades\Storage::disk('public')->url('episodes/' . $coverPath);
            }
        }

        $breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Series', 'href' => route('content.series.list')],
            ['label' => $content->title, 'href' => url('/series/' . $content->id)],
            ['label' => $episodeLabel],
        ];

        $previousEpisodeUrl = $previousEpisode
            ? route('episodes.watch', [$content->id, $previousEpisode->season_id, $previousEpisode->id])
            : null;
        $nextEpisodeUrl = $nextEpisode
            ? route('episodes.watch', [$content->id, $nextEpisode->season_id, $nextEpisode->id])
            : null;
    @endphp

    <article class="cc-stack-6">
        <x-ui.breadcrumbs :items="$breadcrumbs" />

        <section class="relative overflow-hidden rounded-sm border border-cc-border bg-cc-bg-elevated">
            @if ($seriesBackdrop)
                <img
                    src="{{ $seriesBackdrop }}"
                    alt="{{ $content->title }} backdrop"
                    width="1920"
                    height="1080"
                    loading="lazy"
                    class="h-56 w-full object-cover sm:h-72"
                >
            @else
                <div class="flex h-56 w-full items-center justify-center bg-cc-bg-elevated text-sm text-cc-text-muted sm:h-72">
                    Backdrop unavailable
                </div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-cc-bg-primary via-cc-bg-primary/70 to-transparent"></div>
            <div class="absolute bottom-0 left-0 w-full p-5 sm:p-6">
                <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">{{ $episodeLabel }}</p>
                <h1 class="font-serif text-3xl text-cc-text-primary sm:text-4xl">{{ $episode->title }}</h1>
                <p class="mt-2 text-sm text-cc-text-secondary">{{ $runtimeLabel }}</p>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1.7fr)_minmax(0,1fr)]">
            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                @if ($episodeVideoUrl)
                    <div class="cc-elevated overflow-hidden">
                        <div class="aspect-video bg-black">
                            <video controls preload="metadata" class="h-full w-full bg-black" poster="{{ $episodeCoverUrl ?: ($seriesPoster ?: '') }}">
                                <source src="{{ $episodeVideoUrl }}" type="video/mp4">
                                Your browser does not support HTML5 video.
                            </video>
                        </div>
                    </div>
                @else
                    <x-ui.empty-state
                        title="Episode video unavailable"
                        description="This episode does not have an uploaded video yet. Add one from admin season management."
                    />
                @endif

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge tone="neutral">{{ $episodeLabel }}</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $runtimeLabel }}</x-ui.badge>
                    @if ($episode->release_date)
                        <x-ui.badge tone="neutral">{{ $episode->release_date->toDateString() }}</x-ui.badge>
                    @endif
                </div>

                <p class="text-sm leading-editorial text-cc-text-secondary">
                    {{ $overviewText }}
                </p>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button :href="$previousEpisodeUrl" variant="ghost" size="sm" :disabled="!$previousEpisodeUrl">
                        Previous episode
                    </x-ui.button>
                    <x-ui.button :href="$nextEpisodeUrl" variant="ghost" size="sm" :disabled="!$nextEpisodeUrl">
                        Next episode
                    </x-ui.button>
                    <x-ui.button :href="url('/series/' . $content->id)" variant="secondary" size="sm">
                        Back to series
                    </x-ui.button>
                </div>
            </section>

            <aside class="cc-surface cc-stack-4 p-4 sm:p-5">
                <div class="overflow-hidden rounded-sm border border-cc-border bg-cc-bg-elevated">
                    <div class="aspect-[2/3]">
                        @if ($seriesPoster)
                            <img
                                src="{{ $seriesPoster }}"
                                alt="{{ $content->title }} poster"
                                width="500"
                                height="750"
                                loading="lazy"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div class="flex h-full items-center justify-center p-4 text-center text-xs text-cc-text-muted">
                                Poster unavailable
                            </div>
                        @endif
                    </div>
                </div>

                <dl class="cc-stack-2 text-sm leading-editorial text-cc-text-secondary">
                    <div>
                        <dt class="text-cc-caption uppercase tracking-label text-cc-text-muted">Series</dt>
                        <dd class="text-cc-text-primary">{{ $content->title }}</dd>
                    </div>
                    <div>
                        <dt class="text-cc-caption uppercase tracking-label text-cc-text-muted">Season</dt>
                        <dd class="text-cc-text-primary">Season {{ $season->season_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-cc-caption uppercase tracking-label text-cc-text-muted">Episode</dt>
                        <dd class="text-cc-text-primary">{{ $episode->episode_number }} - {{ $episode->title }}</dd>
                    </div>
                </dl>
            </aside>
        </div>
    </article>
</x-app-layout>
