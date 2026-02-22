@props([
    'title' => 'Nothing here yet',
    'description' => null,
    'actionLabel' => null,
    'actionHref' => null,
])

<section {{ $attributes->merge(['class' => 'cc-surface cc-stack-4 px-6 py-8 text-center sm:px-8']) }}>
    <div class="mx-auto cc-elevated flex h-12 w-12 items-center justify-center rounded-sm border border-cc-border text-cc-text-muted">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 6h16M4 12h16M4 18h10" />
        </svg>
    </div>

    <div class="cc-stack-2">
        <h3 class="cc-title-section text-cc-text-primary">{{ $title }}</h3>

        @if ($description)
            <p class="mx-auto max-w-xl text-sm leading-editorial text-cc-text-secondary">{{ $description }}</p>
        @endif
    </div>

    @if ($actionLabel && $actionHref)
        <div class="pt-2">
            <x-ui.button :href="$actionHref" variant="secondary">{{ $actionLabel }}</x-ui.button>
        </div>
    @endif

    @if (trim((string) $slot))
        <div class="pt-1">
            {{ $slot }}
        </div>
    @endif
</section>
