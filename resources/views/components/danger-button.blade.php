<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex h-10 items-center justify-center rounded-md border border-[#5C2E2E]/45 bg-[#5C2E2E] px-4 text-sm font-medium tracking-wide text-cc-text-primary transition-all cc-motion-base hover:brightness-110 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-60']) }}>
    {{ $slot }}
</button>
