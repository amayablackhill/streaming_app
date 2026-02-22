<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex h-10 items-center justify-center rounded-md border border-cc-border bg-cc-bg-elevated px-4 text-sm font-medium tracking-wide text-cc-text-primary transition-all cc-motion-base hover:border-cc-text-muted/60 hover:text-cc-text-primary focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60']) }}>
    {{ $slot }}
</button>
