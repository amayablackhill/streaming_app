@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 rounded-sm border font-medium tracking-[0.02em] transition-[color,background-color,border-color,opacity] cc-motion-base focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-cc-accent/60 disabled:cursor-not-allowed disabled:opacity-50';

    $sizeClasses = [
        'sm' => 'h-9 px-3 text-xs',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-11 px-5 text-sm',
    ];

    $variantClasses = [
        'primary' => 'border-cc-accent bg-cc-accent text-cc-text-primary hover:border-cc-accent/85 hover:bg-cc-accent/85',
        'secondary' => 'border-cc-border bg-cc-bg-elevated text-cc-text-primary hover:border-cc-text-muted/45 hover:bg-cc-bg-surface',
        'ghost' => 'border-transparent bg-transparent text-cc-text-secondary hover:border-cc-border hover:bg-cc-bg-elevated/60 hover:text-cc-text-primary',
        'danger' => 'border-[#5C2E2E] bg-[#5C2E2E] text-cc-text-primary hover:border-[#6a3535] hover:bg-[#6a3535]',
    ];

    $classes = implode(' ', [
        $baseClasses,
        $sizeClasses[$size] ?? $sizeClasses['md'],
        $variantClasses[$variant] ?? $variantClasses['primary'],
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
