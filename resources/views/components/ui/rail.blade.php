@props([
    'title' => null,
    'subtitle' => null,
    'ariaLabel' => null,
    'trackClass' => '',
    'itemClass' => '',
    'showFades' => true,
    'showControls' => true,
])

<section class="cc-rail">
    @if ($title || $subtitle)
        <header class="mb-3 flex items-end justify-between gap-4">
            <div>
                @if ($subtitle)
                    <p class="text-[11px] uppercase tracking-[0.12em] text-cc-text-muted">{{ $subtitle }}</p>
                @endif
                @if ($title)
                    <h2 class="font-serif text-2xl leading-tight text-cc-text-primary">{{ $title }}</h2>
                @endif
            </div>
        </header>
    @endif

    <div class="cc-rail-shell" data-cc-embla>
        @if ($showFades)
            <div aria-hidden="true" class="cc-rail-fade-left"></div>
            <div aria-hidden="true" class="cc-rail-fade-right"></div>
        @endif

        <div
            data-cc-embla-viewport
            class="cc-rail-viewport {{ $trackClass }}"
            role="region"
            aria-label="{{ $ariaLabel ?? $title ?? 'Content rail' }}"
        >
            <div data-cc-embla-container class="cc-rail-track {{ $itemClass }}">
                {{ $slot }}
            </div>
        </div>

        @if ($showControls)
            <div class="mt-3 flex items-center justify-end gap-2">
                <button
                    type="button"
                    class="cc-rail-control"
                    data-cc-embla-prev
                    aria-label="Scroll rail left"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </button>
                <button
                    type="button"
                    class="cc-rail-control"
                    data-cc-embla-next
                    aria-label="Scroll rail right"
                >
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
            </div>
        @endif
    </div>
</section>

