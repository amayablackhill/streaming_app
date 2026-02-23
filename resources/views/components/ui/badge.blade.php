@props([
    'tone' => 'neutral',
])

@php
    $toneClasses = [
        'neutral' => 'bg-cc-bg-elevated text-cc-text-secondary border-cc-border',
        'processing' => 'bg-[#6B3F2B]/25 text-[#D2AE9A] border-[#6B3F2B]/40',
        'ready' => 'bg-[#3E4A3F]/25 text-[#C4D1C5] border-[#3E4A3F]/45',
        'failed' => 'bg-[#5C2E2E]/25 text-[#E0B6B6] border-[#5C2E2E]/45',
        'premium' => 'bg-[#1F3A44]/25 text-[#B7CDD3] border-[#1F3A44]/45',
    ];

    $classes = implode(' ', [
        'inline-flex h-6 items-center rounded-sm border px-2.5 text-[11px] font-medium uppercase leading-none tracking-[0.08em]',
        $toneClasses[$tone] ?? $toneClasses['neutral'],
    ]);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
