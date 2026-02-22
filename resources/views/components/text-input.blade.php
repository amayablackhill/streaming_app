@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'cc-input block h-10 w-full px-3 text-sm placeholder:text-cc-text-muted focus-visible:outline-none']) !!}>
