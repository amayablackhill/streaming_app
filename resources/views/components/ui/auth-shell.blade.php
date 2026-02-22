@props([
    'title' => 'Cineclub',
    'subtitle' => 'Sign in to continue',
])

<div class="min-h-screen bg-cc-bg-primary text-cc-text-primary">
    <div class="mx-auto flex min-h-screen w-full max-w-5xl items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md cc-surface bg-cc-bg-surface p-6 sm:p-8">
            <div class="mb-6">
                <a href="{{ url('/') }}" class="font-serif text-3xl leading-none tracking-wide text-cc-text-primary">
                    {{ $title }}
                </a>
                <p class="mt-2 text-sm text-cc-text-secondary">{{ $subtitle }}</p>
            </div>

            {{ $slot }}
        </div>
    </div>
</div>
