@props([
    'context' => 'editorial',
])

@php
    $user = auth()->user();
    $isAdmin = $user && method_exists($user, 'canAccessAdminPanel') && $user->canAccessAdminPanel();

    $baseLinks = [
        ['label' => 'Home', 'route' => 'home', 'patterns' => ['home', 'search']],
        ['label' => 'Movies', 'route' => 'content.movies.list', 'patterns' => ['content.movies.list', 'movies.*']],
        ['label' => 'Series', 'route' => 'content.series.list', 'patterns' => ['content.series.list', 'series.*', 'episodes.watch']],
    ];

    $adminLinks = [
        ['label' => 'Admin', 'route' => 'admin.home', 'patterns' => ['admin.home']],
        ['label' => 'Add Content', 'route' => 'content.add', 'patterns' => ['content.add', 'content.edit', 'content.update']],
        ['label' => 'Movies Table', 'route' => 'movies.table', 'patterns' => ['movies.table']],
        ['label' => 'Series Table', 'route' => 'series.table', 'patterns' => ['series.table', 'seasons.*', 'episodes.*', 'video-assets.*']],
        ['label' => 'TMDB Import', 'route' => 'admin.tmdb.search', 'patterns' => ['admin.tmdb.search', 'admin.tmdb.import']],
    ];

    $links = $isAdmin ? array_merge($baseLinks, $adminLinks) : $baseLinks;

    $navTone = $context === 'admin'
        ? 'bg-cc-bg-elevated/95 border-cc-border'
        : 'bg-cc-bg-surface/90 border-cc-border';
@endphp

<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b backdrop-blur {{ $navTone }}" aria-label="Primary">
    <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <x-layout.logo :href="route('home')" />

            <div class="hidden items-center gap-1 md:flex">
                @foreach ($links as $link)
                    @php
                        $isActive = request()->routeIs(...$link['patterns']);
                    @endphp
                    <a
                        href="{{ route($link['route']) }}"
                        class="rounded-sm px-3 py-2 text-sm transition-all cc-motion-base {{ $isActive ? 'text-cc-text-primary bg-cc-bg-elevated border border-cc-border' : 'text-cc-text-secondary hover:text-cc-text-primary' }}"
                        @if ($isActive) aria-current="page" @endif
                    >
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="hidden items-center gap-2 md:flex">
            <form action="{{ route('search') }}" method="GET" role="search" class="hidden lg:flex">
                <label for="top-nav-search" class="sr-only">Search catalog</label>
                <div class="relative">
                    <x-ui.input
                        id="top-nav-search"
                        name="q"
                        type="search"
                        :value="request('q')"
                        placeholder="Search"
                        class="h-9 w-44 bg-cc-bg-primary/60 pr-8"
                    />
                    <span class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 text-cc-text-muted" aria-hidden="true">
                        <x-ui.icon name="search" class="h-4 w-4" />
                    </span>
                </div>
            </form>

            @auth
                <x-ui.badge :tone="$isAdmin ? 'premium' : 'neutral'">
                    {{ $isAdmin ? 'curator' : 'member' }}
                </x-ui.badge>

                <a href="{{ route('profile.edit') }}" class="rounded-sm px-3 py-2 text-sm text-cc-text-secondary transition-colors cc-motion-base hover:text-cc-text-primary">
                    {{ $user->name ?? $user->username }}
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-ui.button type="submit" variant="ghost" size="sm">Log Out</x-ui.button>
                </form>
            @else
                <x-ui.button href="{{ route('login') }}" variant="ghost" size="sm">Log In</x-ui.button>
                <x-ui.button href="{{ route('register') }}" variant="secondary" size="sm">Register</x-ui.button>
            @endauth
        </div>

        <button
            type="button"
            class="inline-flex items-center justify-center rounded-sm border border-cc-border p-2 text-cc-text-secondary transition-colors cc-motion-base hover:text-cc-text-primary md:hidden"
            @click="open = !open"
            aria-label="Toggle navigation"
            :aria-expanded="open.toString()"
            aria-controls="mobile-nav-menu"
        >
            <x-ui.icon x-show="!open" name="menu" class="h-5 w-5" />
            <x-ui.icon x-show="open" x-cloak name="close" class="h-5 w-5" />
        </button>
    </div>

    <div
        x-show="open"
        x-transition:enter="transition-opacity cc-motion-base"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity cc-motion-fast cc-motion-exit"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        id="mobile-nav-menu"
        class="border-t border-cc-border bg-cc-bg-surface/95 px-4 py-3 md:hidden"
    >
        <form action="{{ route('search') }}" method="GET" role="search" class="mb-3 border-b border-cc-border pb-3">
            <label for="top-nav-search-mobile" class="sr-only">Search catalog</label>
            <x-ui.input
                id="top-nav-search-mobile"
                name="q"
                type="search"
                :value="request('q')"
                placeholder="Search catalog..."
                class="w-full"
            />
        </form>

        <div class="grid gap-1">
            @foreach ($links as $link)
                @php
                    $isActive = request()->routeIs(...$link['patterns']);
                @endphp
                <a
                    href="{{ route($link['route']) }}"
                    class="rounded-sm px-3 py-2 text-sm {{ $isActive ? 'bg-cc-bg-elevated text-cc-text-primary' : 'text-cc-text-secondary' }}"
                    @if ($isActive) aria-current="page" @endif
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <div class="mt-3 border-t border-cc-border pt-3">
            @auth
                <p class="mb-2 text-sm text-cc-text-secondary">{{ $user->name ?? $user->username }}</p>
                <div class="flex items-center gap-2">
                    <x-ui.button href="{{ route('profile.edit') }}" variant="ghost" size="sm">Profile</x-ui.button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button type="submit" variant="ghost" size="sm">Log Out</x-ui.button>
                    </form>
                </div>
            @else
                <div class="flex items-center gap-2">
                    <x-ui.button href="{{ route('login') }}" variant="ghost" size="sm">Log In</x-ui.button>
                    <x-ui.button href="{{ route('register') }}" variant="secondary" size="sm">Register</x-ui.button>
                </div>
            @endauth
        </div>
    </div>
</nav>
