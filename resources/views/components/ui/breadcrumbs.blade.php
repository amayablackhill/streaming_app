@props([
    'items' => [],
])

@if (!empty($items))
    <nav {{ $attributes->merge(['class' => 'text-[11px] uppercase tracking-[0.12em] text-cc-text-muted']) }} aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-2">
            @foreach ($items as $item)
                @php
                    $label = (string) ($item['label'] ?? '');
                    $href = $item['href'] ?? null;
                    $isLast = $loop->last;
                @endphp

                @continue($label === '')

                <li class="inline-flex items-center gap-2">
                    @if ($href && !$isLast)
                        <a href="{{ $href }}" class="transition-colors cc-motion-base hover:text-cc-text-primary">
                            {{ $label }}
                        </a>
                    @else
                        <span class="{{ $isLast ? 'text-cc-text-secondary' : '' }}">{{ $label }}</span>
                    @endif

                    @unless ($isLast)
                        <span aria-hidden="true" class="text-cc-text-muted/70">/</span>
                    @endunless
                </li>
            @endforeach
        </ol>
    </nav>
@endif
