@props([
    'context' => 'editorial',
])

@php
    $user = auth()->user();
    $isAdmin = $user && method_exists($user, 'canAccessAdminPanel') && $user->canAccessAdminPanel();

    $baseLinks = [
        ['label' => 'Home', 'route' => 'home', 'patterns' => ['home', 'search']],
        ['label' => 'Films', 'route' => 'content.movies.list', 'patterns' => ['content.movies.list', 'movies.*']],
        ['label' => 'Series', 'route' => 'content.series.list', 'patterns' => ['content.series.list', 'series.*', 'episodes.watch']],
    ];

    $adminMenuLinks = [
        ['label' => 'Dashboard', 'href' => route('admin.home'), 'patterns' => ['admin.home']],
        ['label' => 'Films', 'href' => route('movies.table'), 'patterns' => ['movies.table']],
        ['label' => 'Series', 'href' => route('series.table'), 'patterns' => ['series.table', 'seasons.*', 'episodes.*']],
        ['label' => 'Add Content', 'href' => route('content.add'), 'patterns' => ['content.add', 'content.edit', 'content.update']],
        ['label' => 'TMDB Import', 'href' => route('admin.tmdb.search'), 'patterns' => ['admin.tmdb.search', 'admin.tmdb.import', 'admin.tmdb.series.episodes.import']],
        ['label' => 'Video Assets', 'href' => route('admin.home').'#video-assets', 'patterns' => ['video-assets.*']],
        ['label' => 'Health', 'href' => route('admin.health'), 'patterns' => ['admin.health', 'admin.health.api', 'admin.health.video-pipeline']],
    ];

    $adminPatterns = collect($adminMenuLinks)->flatMap(
        fn (array $link): array => $link['patterns'] ?? []
    )->values()->all();
    $isAdminMenuActive = request()->routeIs(...$adminPatterns);

    $navTone = $context === 'admin'
        ? 'bg-cc-bg-elevated/95 border-cc-border'
        : 'bg-cc-bg-surface/90 border-cc-border';
@endphp

<nav
    x-data="{ open: false, adminMenuOpen: false, adminMobileOpen: false }"
    @keydown.escape.window="adminMenuOpen = false; adminMobileOpen = false"
    class="sticky top-0 z-40 border-b backdrop-blur {{ $navTone }}"
    aria-label="Primary"
>
    <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <x-layout.logo :href="route('home')" />

            <div class="hidden items-center gap-1 md:flex">
                @foreach ($baseLinks as $link)
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

                @if ($isAdmin)
                    <div class="relative ml-1">
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-sm px-3 py-2 text-sm transition-all cc-motion-base {{ $isAdminMenuActive ? 'text-cc-text-primary bg-cc-bg-elevated border border-cc-border' : 'text-cc-text-secondary hover:text-cc-text-primary' }}"
                            @click="adminMenuOpen = !adminMenuOpen"
                            @keydown.arrow-down.prevent="$nextTick(() => { adminMenuOpen = true; $refs.adminDesktopFirstLink.focus(); })"
                            :aria-expanded="adminMenuOpen.toString()"
                            aria-haspopup="true"
                            aria-controls="admin-dropdown-menu"
                        >
                            <span>Admin</span>
                            <x-ui.icon name="arrow-right" class="h-3.5 w-3.5 transition-transform cc-motion-base" x-bind:class="adminMenuOpen ? 'rotate-90' : ''" />
                        </button>

                        <div
                            x-show="adminMenuOpen"
                            x-cloak
                            x-transition:enter="transition-opacity cc-motion-base"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition-opacity cc-motion-fast cc-motion-exit"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            @click.outside="adminMenuOpen = false"
                            id="admin-dropdown-menu"
                            role="menu"
                            class="absolute right-0 top-full z-50 mt-2 w-52 border border-cc-border bg-cc-bg-elevated/95 p-1 backdrop-blur"
                        >
                            @foreach ($adminMenuLinks as $adminLink)
                                @php
                                    $adminLinkActive = request()->routeIs(...($adminLink['patterns'] ?? []));
                                @endphp
                                <a
                                    href="{{ $adminLink['href'] }}"
                                    role="menuitem"
                                    @if ($loop->first) x-ref="adminDesktopFirstLink" @endif
                                    @click="adminMenuOpen = false"
                                    class="block rounded-sm px-3 py-2 text-sm transition-colors cc-motion-base {{ $adminLinkActive ? 'bg-cc-bg-primary text-cc-text-primary' : 'text-cc-text-secondary hover:bg-cc-bg-primary hover:text-cc-text-primary' }}"
                                >
                                    {{ $adminLink['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="hidden items-center gap-6 md:flex">
            <form action="{{ route('search') }}" method="GET" role="search" class="hidden items-center lg:flex">
                <label for="top-nav-search" class="sr-only">Search catalog</label>
                <div class="relative">
                    <x-ui.input
                        id="top-nav-search"
                        name="q"
                        type="search"
                        :value="request('q')"
                        placeholder="Search"
                        class="h-10 w-52 bg-cc-bg-primary/60 pl-3.5 pr-9 text-[13px] leading-none placeholder:text-cc-text-muted/90"
                    />
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-cc-text-muted" aria-hidden="true">
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
                class="h-10 w-full px-3.5 text-[13px] placeholder:text-cc-text-muted/90"
            />
        </form>

        <div class="grid gap-1">
            @foreach ($baseLinks as $link)
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

        @if ($isAdmin)
            <div class="mt-3 border-t border-cc-border pt-3">
                <button
                    type="button"
                    class="flex w-full items-center justify-between rounded-sm px-3 py-2 text-sm text-cc-text-secondary transition-colors cc-motion-base hover:bg-cc-bg-elevated hover:text-cc-text-primary"
                    @click="adminMobileOpen = !adminMobileOpen"
                    :aria-expanded="adminMobileOpen.toString()"
                    aria-controls="mobile-admin-menu"
                >
                    <span class="{{ $isAdminMenuActive ? 'text-cc-text-primary' : '' }}">Admin</span>
                    <x-ui.icon name="arrow-right" class="h-4 w-4 transition-transform cc-motion-base" x-bind:class="adminMobileOpen ? 'rotate-90' : ''" />
                </button>

                <div
                    x-show="adminMobileOpen"
                    x-cloak
                    x-transition:enter="transition-opacity cc-motion-base"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity cc-motion-fast cc-motion-exit"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    id="mobile-admin-menu"
                    class="mt-1 grid gap-1"
                >
                    @foreach ($adminMenuLinks as $adminLink)
                        @php
                            $adminLinkActive = request()->routeIs(...($adminLink['patterns'] ?? []));
                        @endphp
                        <a
                            href="{{ $adminLink['href'] }}"
                            class="rounded-sm px-3 py-2 text-sm transition-colors cc-motion-base {{ $adminLinkActive ? 'bg-cc-bg-elevated text-cc-text-primary' : 'text-cc-text-secondary' }}"
                            @click="open = false; adminMobileOpen = false"
                        >
                            {{ $adminLink['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-3 border-t border-cc-border pt-3">
            @auth
                <div class="mb-2 flex items-center gap-2">
                    <x-ui.badge :tone="$isAdmin ? 'premium' : 'neutral'">
                        {{ $isAdmin ? 'curator' : 'member' }}
                    </x-ui.badge>
                    <p class="min-w-0 flex-1 truncate text-sm leading-6 text-cc-text-secondary">
                        {{ $user->name ?? $user->username }}
                    </p>
                </div>
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
