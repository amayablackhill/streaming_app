@props([
    'name',
])

@php
    $baseClass = 'h-5 w-5';
    $classes = trim($baseClass.' '.$attributes->get('class', ''));
@endphp

@switch($name)
    @case('search')
        <svg {{ $attributes->except('class')->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="m21 21-4.2-4.2m1.2-4.8a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
        </svg>
        @break
    @case('play')
        <svg {{ $attributes->except('class')->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 6.5v11l9-5.5-9-5.5Z" />
        </svg>
        @break
    @case('arrow-left')
        <svg {{ $attributes->except('class')->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 18 9 12l6-6" />
        </svg>
        @break
    @case('arrow-right')
        <svg {{ $attributes->except('class')->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 18 6-6-6-6" />
        </svg>
        @break
    @case('bookmark')
        <svg {{ $attributes->except('class')->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 5.5A1.5 1.5 0 0 1 8.5 4h7A1.5 1.5 0 0 1 17 5.5v14l-5-3-5 3v-14Z" />
        </svg>
        @break
    @case('dashboard')
        <svg {{ $attributes->except('class')->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 4.75h7.5v6.5h-7.5v-6.5Zm9 0h7.5v4.5h-7.5v-4.5Zm0 6h7.5v8.5h-7.5v-8.5Zm-9 2.5h7.5v6h-7.5v-6Z" />
        </svg>
        @break
    @case('share')
        <svg {{ $attributes->except('class')->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h8m0 0-3-3m3 3-3 3M12 4.5A7.5 7.5 0 1 0 19.5 12" />
        </svg>
        @break
    @case('menu')
        <svg {{ $attributes->except('class')->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7h16M4 12h16M4 17h16" />
        </svg>
        @break
    @case('close')
        <svg {{ $attributes->except('class')->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m6 6 12 12M18 6 6 18" />
        </svg>
        @break
    @case('list')
        <svg {{ $attributes->except('class')->merge(['class' => $classes]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4 6h16M4 12h16M4 18h10" />
        </svg>
        @break
@endswitch
