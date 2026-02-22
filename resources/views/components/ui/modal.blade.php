@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
])

@php
    $widthClasses = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
    ];
@endphp

<div
    x-data="{ show: @js($show) }"
    x-on:open-modal.window="$event.detail === '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail === '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        x-show="show"
        class="fixed inset-0 bg-black/70 transition-opacity duration-200 ease-soft"
        x-on:click="show = false"
        x-transition:enter="ease-soft duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <div
        x-show="show"
        class="relative mx-auto mb-6 w-full {{ $widthClasses[$maxWidth] ?? $widthClasses['2xl'] }}"
        x-transition:enter="ease-soft duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in-out duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 sm:scale-95"
    >
        <div class="cc-surface bg-cc-bg-surface p-5 shadow-subtle">
            {{ $slot }}
        </div>
    </div>
</div>
