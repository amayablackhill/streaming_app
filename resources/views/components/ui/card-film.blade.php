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

            <h3 class="cc-card-title font-sans text-base font-semibold leading-snug tracking-tight text-cc-text-primary sm:text-[1.0625rem]">
                {{ $title }}
            </h3>

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
