@props(['status'])

@php
    $normalizedStatus = strtolower((string) $status);
    $classes = match ($normalizedStatus) {
        'ready' => 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
        'processing' => 'border-amber-500/30 bg-amber-500/10 text-amber-300',
        'pending' => 'border-slate-500/40 bg-slate-500/10 text-slate-300',
        'failed' => 'border-rose-500/30 bg-rose-500/10 text-rose-300',
        default => 'border-slate-600 bg-slate-500/10 text-slate-300',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide {$classes}"]) }}>
    {{ $normalizedStatus ?: 'unknown' }}
</span>
