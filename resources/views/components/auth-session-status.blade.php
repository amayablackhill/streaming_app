@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-md border border-[#3E4A3F]/45 bg-[#3E4A3F]/25 px-3 py-2 text-sm font-medium text-[#C4D1C5]']) }}>
        {{ $status }}
    </div>
@endif
