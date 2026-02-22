@props([
    'title' => null,
    'subtitle' => null,
    'ariaLabel' => null,
    'trackClass' => '',
    'itemClass' => '',
    'showFades' => true,
])

<section
    class="cc-rail"
    x-data="{
        dragging: false,
        startX: 0,
        scrollStart: 0,
        onDown(event) {
            this.dragging = true;
            this.startX = event.pageX;
            this.scrollStart = this.$refs.track.scrollLeft;
        },
        onMove(event) {
            if (!this.dragging) return;
            const delta = event.pageX - this.startX;
            this.$refs.track.scrollLeft = this.scrollStart - delta;
        },
        onUp() {
            this.dragging = false;
        },
        onWheel(event) {
            const track = this.$refs.track;
            if (track.scrollWidth <= track.clientWidth) return;
            if (Math.abs(event.deltaY) > Math.abs(event.deltaX)) {
                event.preventDefault();
                track.scrollLeft += event.deltaY;
            }
        }
    }"
>
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

    <div class="relative">
        @if ($showFades)
            <div aria-hidden="true" class="cc-rail-fade-left"></div>
            <div aria-hidden="true" class="cc-rail-fade-right"></div>
        @endif

        <div
            x-ref="track"
            class="cc-rail-track {{ $trackClass }} {{ $itemClass ? '' : '[&>*]:w-[12.5rem] sm:[&>*]:w-[14rem] lg:[&>*]:w-[15rem]' }} {{ $itemClass }}"
            role="region"
            aria-label="{{ $ariaLabel ?? $title ?? 'Content rail' }}"
            @mousedown="onDown($event)"
            @mousemove="onMove($event)"
            @mouseup="onUp()"
            @mouseleave="onUp()"
            @wheel="onWheel($event)"
        >
            {{ $slot }}
        </div>
    </div>
</section>
