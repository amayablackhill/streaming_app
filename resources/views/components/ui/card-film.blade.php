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

<article class="group cc-surface overflow-hidden transition-all duration-200 ease-soft hover:-translate-y-0.5 hover:border-cc-text-muted/40">
    <a href="{{ $href }}" class="block">
        <div class="aspect-[2/3] bg-cc-bg-elevated overflow-hidden">
            @if ($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $title }}"
                    loading="lazy"
                    class="h-full w-full object-cover transition-transform duration-250 ease-soft group-hover:scale-[1.02]"
                />
            @else
                <div class="flex h-full items-center justify-center px-4 text-center text-xs uppercase tracking-[0.1em] text-cc-text-muted">
                    No poster
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
