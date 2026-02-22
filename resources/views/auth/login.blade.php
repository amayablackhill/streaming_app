<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="cc-stack-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1" type="email" name="email" :value="old('email')" autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1" type="password" name="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded-sm border-cc-border bg-cc-bg-elevated text-cc-accent focus:ring-0" name="remember">
                <span class="ml-2 text-sm text-cc-text-secondary">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div>
            @if (Route::has('password.request'))
                <a class="text-sm underline decoration-cc-border underline-offset-4 transition-colors cc-motion-base hover:text-cc-text-primary focus-visible:outline-none" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div class="flex justify-center items-center pt-2">
            <x-primary-button class="w-full sm:w-auto">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <div class="flex flex-col items-center justify-center pt-2">
            <p class="text-sm text-cc-text-secondary">
                {{ __('No account yet?') }}
                <a href="{{ route('register') }}" class="ml-1 underline decoration-cc-border underline-offset-4 transition-colors cc-motion-base hover:text-cc-text-primary">
                    {{ __('Register') }}
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
