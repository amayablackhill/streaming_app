@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-medium uppercase tracking-[0.08em] text-cc-text-secondary']) }}>
    {{ $value ?? $slot }}
</label>
