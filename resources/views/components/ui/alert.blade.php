@props([
    'tone' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $toneClasses = [
        'info' => 'border-[#1F3A44]/45 bg-[#1F3A44]/18 text-[#C2D6DB]',
        'success' => 'border-[#3E4A3F]/45 bg-[#3E4A3F]/18 text-[#C7D6C9]',
        'warning' => 'border-[#6B3F2B]/45 bg-[#6B3F2B]/18 text-[#E0C6B7]',
        'error' => 'border-[#5C2E2E]/45 bg-[#5C2E2E]/18 text-[#E3BFC0]',
    ];

    $classes = implode(' ', [
        'cc-stack-2 relative rounded-md border px-4 py-3 text-sm leading-editorial',
        $toneClasses[$tone] ?? $toneClasses['info'],
    ]);
@endphp

<div
    {{ $attributes->merge(['class' => $classes]) }}
    @if ($dismissible)
        x-data="{ hidden: false }"
        x-show="!hidden"
        x-transition:enter="transition-opacity cc-motion-base"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity cc-motion-fast cc-motion-exit"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    @endif
>
    @if ($title)
        <p class="font-medium tracking-wide text-cc-text-primary">{{ $title }}</p>
    @endif

    <div class="text-sm text-current/95">
        {{ $slot }}
    </div>

    @if ($dismissible)
        <button
            type="button"
            class="absolute right-2 top-2 rounded-sm px-2 py-1 text-xs text-current/80 transition-colors cc-motion-base hover:text-current"
            @click="hidden = true"
            aria-label="Dismiss alert"
        >
            Close
        </button>
    @endif
</div>
