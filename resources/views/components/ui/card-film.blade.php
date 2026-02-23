@props([
    'title',
    'href' => '#',
    'image' => null,
    'year' => null,
    'eyebrow' => null,
    'meta' => null,
    'badgeLabel' => null,
    'badgeTone' => 'neutral',
    'fullWidth' => false,
])

@php
    $tmdbPosterPath = null;
    if (is_string($image) && preg_match('#^https://image\.tmdb\.org/t/p/(?:w\d+|original)(/.*)$#', $image, $matches) === 1) {
        $tmdbPosterPath = $matches[1];
    }

    $posterMobileSrc = $tmdbPosterPath ? 'https://image.tmdb.org/t/p/w342' . $tmdbPosterPath : null;
    $posterDesktopSrc = $tmdbPosterPath ? 'https://image.tmdb.org/t/p/w500' . $tmdbPosterPath : null;
@endphp

<article @class([
    'group cc-surface h-full overflow-hidden transition-all cc-motion-base hover:-translate-y-0.5 hover:border-cc-text-muted/40',
    'cc-card-film' => ! $fullWidth,
    'w-full min-w-0' => $fullWidth,
])>
    <a href="{{ $href }}" class="flex h-full flex-col">
        <div class="relative aspect-[2/3] overflow-hidden bg-cc-bg-elevated">
            @if ($image)
                <img
                    src="{{ $image }}"
                    @if ($posterMobileSrc && $posterDesktopSrc)
                        srcset="{{ $posterMobileSrc }} 342w, {{ $posterDesktopSrc }} 500w"
                        sizes="(max-width: 639px) 45vw, (max-width: 1023px) 30vw, 14rem"
                    @endif
                    alt="{{ $title }}"
                    loading="lazy"
                    decoding="async"
                    width="560"
                    height="840"
                    draggable="false"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                    class="pointer-events-none h-full w-full select-none object-cover transition-transform cc-motion-slow group-hover:scale-[1.02]"
                />
                <div class="cc-card-media-fallback hidden">
                    <span>{{ $title }}</span>
                </div>
            @else
                <div class="cc-card-media-fallback flex">
                    <span>{{ $title }}</span>
                </div>
            @endif
        </div>

        <div class="flex flex-1 flex-col gap-2 p-3">
            @if ($eyebrow)
                <p class="text-[11px] uppercase tracking-[0.1em] text-cc-text-muted">{{ $eyebrow }}</p>
            @endif

            <h3 class="cc-card-title font-serif text-lg leading-tight text-cc-text-primary">{{ $title }}</h3>

            <div class="mt-auto flex items-center justify-between gap-2 text-xs text-cc-text-muted">
                <span>{{ $year ?? 'N/A' }}</span>

                @if ($meta)
                    <span class="truncate">{{ $meta }}</span>
                @endif
            </div>

            @if ($badgeLabel)
                <x-ui.badge :tone="$badgeTone">{{ $badgeLabel }}</x-ui.badge>
            @endif
        </div>
    </a>
</article>
