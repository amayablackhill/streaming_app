@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 border rounded-md font-medium tracking-wide transition-all duration-200 ease-soft focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50';

    $sizeClasses = [
        'sm' => 'h-9 px-3 text-xs',
        'md' => 'h-10 px-4 text-sm',
        'lg' => 'h-11 px-5 text-sm',
    ];

    $variantClasses = [
        'primary' => 'bg-cc-accent text-cc-text-primary border-transparent hover:brightness-110',
        'secondary' => 'bg-cc-bg-elevated text-cc-text-primary border-cc-border hover:border-cc-text-muted/60',
        'ghost' => 'bg-transparent text-cc-text-secondary border-transparent hover:text-cc-text-primary hover:bg-cc-bg-elevated/70',
        'danger' => 'bg-[#5C2E2E] text-cc-text-primary border-transparent hover:brightness-110',
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
