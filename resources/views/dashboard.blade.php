<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl leading-tight text-cc-text-primary">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="rounded-md border border-cc-border bg-cc-bg-surface p-6">
                <p class="text-xs uppercase tracking-[0.12em] text-cc-text-muted">{{ __('Session') }}</p>
                <p class="mt-2 text-base text-cc-text-secondary">
                    {{ __("You're logged in!") }}
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
