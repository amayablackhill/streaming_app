<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl leading-tight text-cc-text-primary">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <div class="rounded-md border border-cc-border bg-cc-bg-surface p-5 sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="rounded-md border border-cc-border bg-cc-bg-surface p-5 sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="rounded-md border border-cc-border bg-cc-bg-surface p-5 sm:p-7">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
