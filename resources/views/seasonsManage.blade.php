<x-app-layout>
    <section class="cc-stack-6" x-data="{
        query: '',
        matches(haystack) {
            const q = this.query.trim().toLowerCase();
            return !q || String(haystack).toLowerCase().includes(q);
        }
    }">
        <header class="cc-stack-2 sm:flex sm:items-end sm:justify-between">
            <div class="cc-stack-2">
                <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Admin · Seasons Workspace</p>
                <h1 class="cc-title-display">{{ $content->title }}</h1>
                <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                    Manage seasons and episode structure with clear publishing actions.
                </p>
                <div class="flex items-center gap-2">
                    <x-ui.badge tone="neutral">{{ $content->seasons->count() }} seasons</x-ui.badge>
                    <x-ui.badge tone="neutral">{{ $content->seasons->sum(fn($season) => $season->episodes->count()) }} episodes</x-ui.badge>
                </div>
            </div>

            <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
                <x-ui.button :href="route('series.table')" variant="ghost" size="sm" class="w-full sm:w-auto">Back to series table</x-ui.button>
                <x-ui.button :href="route('content.edit', $content->id)" variant="secondary" size="sm" class="w-full sm:w-auto">Edit series</x-ui.button>
            </div>
        </header>

        @if (session('success'))
            <x-ui.alert tone="success" title="Saved">
                {{ session('success') }}
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert tone="error" title="Validation error">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <section class="cc-surface cc-stack-4 p-4 sm:p-5">
            <header class="cc-stack-2">
                <h2 class="cc-title-section">Add New Season</h2>
                <p class="text-xs text-cc-text-muted">Create a season before adding episodes.</p>
            </header>

            <form action="{{ route('seasons.store', $content->id) }}" method="POST" enctype="multipart/form-data" class="cc-stack-4">
                @csrf

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="cc-stack-2">
                        <label for="season_number" class="text-sm font-medium text-cc-text-secondary">Season number *</label>
                        <x-ui.input id="season_number" name="season_number" type="number" min="1" :value="old('season_number')" :invalid="$errors->has('season_number')" />
                    </div>

                    <div class="cc-stack-2">
                        <label for="release_date" class="text-sm font-medium text-cc-text-secondary">Release date *</label>
                        <x-ui.input id="release_date" name="release_date" type="date" :value="old('release_date')" :invalid="$errors->has('release_date')" />
                    </div>

                    <div class="cc-stack-2">
                        <label for="poster_path" class="text-sm font-medium text-cc-text-secondary">Season poster</label>
                        <input
                            id="poster_path"
                            name="poster_path"
                            type="file"
                            accept="image/*"
                            class="cc-input w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-cc-bg-elevated file:px-3 file:py-2 file:text-cc-text-secondary hover:file:text-cc-text-primary"
                        >
                    </div>
                </div>

                <div class="cc-stack-2">
                    <label for="overview" class="text-sm font-medium text-cc-text-secondary">Overview</label>
                    <textarea id="overview" name="overview" rows="3" class="cc-input w-full text-sm leading-editorial">{{ old('overview') }}</textarea>
                </div>

                <div>
                    <x-ui.button type="submit" variant="primary">Create season</x-ui.button>
                </div>
            </form>
        </section>

        <section class="cc-surface cc-stack-4 p-4 sm:p-5">
            <header class="cc-stack-2 sm:flex sm:items-end sm:justify-between">
                <div class="cc-stack-2">
                    <h2 class="cc-title-section">Seasons & Episodes</h2>
                    <p class="text-xs text-cc-text-muted">Filter by season number, episode title, or release date.</p>
                </div>

                <div class="w-full sm:max-w-sm">
                    <x-ui.input x-model.debounce.200ms="query" type="text" placeholder="Search season or episode..." />
                </div>
            </header>

            @if ($content->seasons->isEmpty())
                <x-ui.empty-state
                    title="No seasons yet"
                    description="Create the first season to start structuring this series."
                />
            @else
                <div class="cc-stack-4">
                    @foreach ($content->seasons->sortBy('season_number') as $season)
                        @php
                            $episodes = $season->episodes->sortBy('episode_number')->values();
                            $searchBlob = 'season ' . $season->season_number . ' ' . ($season->release_date ?? '') . ' ' . $episodes->pluck('title')->join(' ');
                            $posterPath = null;
                            if (!empty($season->poster_path)) {
                                $posterPath = str_starts_with($season->poster_path, 'http')
                                    ? $season->poster_path
                                    : asset('storage/' . ltrim($season->poster_path, '/'));
                            }
                        @endphp

                        <article
                            x-data="{ open: true }"
                            x-show="matches(@js($searchBlob))"
                            class="cc-elevated cc-stack-4 p-4"
                        >
                            <header class="flex flex-wrap items-center justify-between gap-3">
                                <div class="cc-stack-2">
                                    <h3 class="text-lg font-medium text-cc-text-primary">Season {{ $season->season_number }}</h3>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <x-ui.badge tone="neutral">{{ $episodes->count() }} episodes</x-ui.badge>
                                        @if ($season->release_date)
                                            <x-ui.badge tone="neutral">{{ $season->release_date }}</x-ui.badge>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
                                    <x-ui.button :href="route('episodes.create', $season->id)" variant="secondary" size="sm" class="w-full sm:w-auto">Add episode</x-ui.button>
                                    <x-ui.button type="button" variant="ghost" size="sm" class="w-full sm:w-auto" @click="open = !open" x-text="open ? 'Collapse' : 'Expand'">Collapse</x-ui.button>
                                    <form action="{{ route('seasons.destroy', $season->id) }}" method="POST" onsubmit="return confirm('Delete this season?')">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button type="submit" variant="danger" size="sm" class="w-full sm:w-auto">Delete</x-ui.button>
                                    </form>
                                </div>
                            </header>

                            @if ($posterPath)
                                <img src="{{ $posterPath }}" alt="Season {{ $season->season_number }} poster" class="h-40 rounded-sm border border-cc-border object-cover">
                            @endif

                            @if ($season->overview)
                                <p class="text-sm leading-editorial text-cc-text-secondary">{{ $season->overview }}</p>
                            @endif

                            <div
                                x-show="open"
                                x-transition:enter="transition-opacity cc-motion-base"
                                x-transition:leave="transition-opacity cc-motion-fast cc-motion-exit"
                                class="cc-stack-3"
                            >
                                @if ($episodes->isEmpty())
                                    <x-ui.alert tone="warning" title="No episodes">
                                        Add episodes to make this season playable.
                                    </x-ui.alert>
                                @else
                                    <div class="overflow-x-auto">
                                        <table class="min-w-[40rem] divide-y divide-cc-border text-sm sm:min-w-full">
                                            <thead class="bg-cc-bg-surface/70">
                                                <tr class="text-left text-xs uppercase tracking-label text-cc-text-muted">
                                                    <th class="px-3 py-2">Episode</th>
                                                    <th class="px-3 py-2">Title</th>
                                                    <th class="hidden px-3 py-2 sm:table-cell">Duration</th>
                                                    <th class="hidden px-3 py-2 md:table-cell">Release</th>
                                                    <th class="px-3 py-2">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-cc-border">
                                                @foreach ($episodes as $episode)
                                                    <tr class="hover:bg-cc-bg-surface/60 transition-colors cc-motion-base">
                                                        <td class="px-3 py-2 text-cc-text-secondary">{{ $episode->episode_number }}</td>
                                                        <td class="px-3 py-2 text-cc-text-primary">{{ $episode->title }}</td>
                                                        <td class="hidden px-3 py-2 text-cc-text-secondary sm:table-cell">{{ $episode->duration ? $episode->duration . ' min' : 'N/A' }}</td>
                                                        <td class="hidden px-3 py-2 text-cc-text-secondary md:table-cell">{{ $episode->release_date ?? 'N/A' }}</td>
                                                        <td class="px-3 py-2">
                                                            <div class="flex flex-wrap items-center gap-2">
                                                                <x-ui.button :href="route('episodes.watch', [$content->id, $season->id, $episode->id])" variant="ghost" size="sm" class="w-full sm:w-auto">Watch</x-ui.button>
                                                                <x-ui.button :href="route('episodes.edit', [$season->id, $episode->id])" variant="secondary" size="sm" class="w-full sm:w-auto">Edit</x-ui.button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </section>
</x-app-layout>
