@props([
    'type' => 'text',
    'invalid' => false,
])

@php
    $classes = 'cc-input block w-full text-sm placeholder:text-cc-text-muted shadow-subtle focus:ring-0';

    if ($invalid) {
        $classes .= ' border-[#5C2E2E]';
    }
@endphp

<input type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} />
