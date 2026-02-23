@props([
    'title',
    'href' => '#',
    'image' => null,
    'year' => null,
    'eyebrow' => null,
    'meta' => null,
    'badgeLabel' => null,
    'badgeTone' => 'neutral',
])

<article class="group cc-card-film cc-surface overflow-hidden transition-all cc-motion-base hover:-translate-y-0.5 hover:border-cc-text-muted/40">
    <a href="{{ $href }}" class="block">
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

        <div class="space-y-2 p-3">
            @if ($eyebrow)
                <p class="text-[11px] uppercase tracking-[0.1em] text-cc-text-muted">{{ $eyebrow }}</p>
            @endif

            <h3 class="font-serif text-lg leading-tight text-cc-text-primary">{{ $title }}</h3>

            <div class="flex items-center justify-between gap-2 text-xs text-cc-text-muted">
                <span>{{ $year ?? 'N/A' }}</span>

                @if ($meta)
                    <span>{{ $meta }}</span>
                @endif
            </div>

            @if ($badgeLabel)
                <x-ui.badge :tone="$badgeTone">{{ $badgeLabel }}</x-ui.badge>
            @endif
        </div>
    </a>
</article>
