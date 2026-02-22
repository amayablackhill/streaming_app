<x-app-layout>
    <section class="cc-stack-6" x-data="{
        query: '',
        minSeasons: '',
        matches(title, genre, seasonsCount) {
            const q = this.query.trim().toLowerCase();
            const haystack = `${title} ${genre}`.toLowerCase();
            const passesQuery = !q || haystack.includes(q);
            const passesSeasonFilter = !this.minSeasons || Number(seasonsCount) >= Number(this.minSeasons);
            return passesQuery && passesSeasonFilter;
        }
    }">
        <header class="cc-stack-2 sm:flex sm:items-end sm:justify-between">
            <div class="cc-stack-2">
                <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Admin · Catalog Table</p>
                <h1 class="cc-title-display">Series</h1>
                <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                    Keep serialized content organized with fast access to season management.
                </p>
                <div class="flex items-center gap-2">
                    <x-ui.badge tone="neutral">{{ $series->count() }} total series</x-ui.badge>
                </div>
            </div>

            <x-ui.button :href="route('content.add')" variant="secondary" size="sm" class="w-full sm:w-auto">
                Add new content
            </x-ui.button>
        </header>

        <section class="cc-surface cc-stack-4 p-4 sm:p-5">
            <header class="cc-stack-2">
                <h2 class="cc-title-section">Filters</h2>
                <p class="text-xs text-cc-text-muted">Narrow by title/genre and minimum number of seasons.</p>
            </header>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="cc-stack-2">
                    <label for="seriesQuery" class="text-xs uppercase tracking-label text-cc-text-muted">Search title / genre</label>
                    <x-ui.input id="seriesQuery" x-model.debounce.200ms="query" type="text" placeholder="e.g. dark, thriller" />
                </div>

                <div class="cc-stack-2">
                    <label for="seriesSeasonFilter" class="text-xs uppercase tracking-label text-cc-text-muted">Minimum seasons</label>
                    <select id="seriesSeasonFilter" x-model="minSeasons" class="cc-input w-full text-sm">
                        <option value="">Any</option>
                        <option value="1">1+</option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="5">5+</option>
                    </select>
                </div>
            </div>
        </section>

        @if ($series->isEmpty())
            <x-ui.empty-state
                title="No series in catalog"
                description="Create your first series entry to start managing seasons and episodes."
                :action-label="'Add series'"
                :action-href="route('content.add')"
            />
        @else
            <section class="cc-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-[44rem] divide-y divide-cc-border text-sm sm:min-w-full">
                        <thead class="bg-cc-bg-elevated/70">
                            <tr class="text-left text-xs uppercase tracking-label text-cc-text-muted">
                                <th scope="col" class="px-4 py-3">Title</th>
                                <th scope="col" class="hidden px-4 py-3 sm:table-cell">Release</th>
                                <th scope="col" class="hidden px-4 py-3 md:table-cell">Genre</th>
                                <th scope="col" class="px-4 py-3">Seasons</th>
                                <th scope="col" class="hidden px-4 py-3 sm:table-cell">Rating</th>
                                <th scope="col" class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cc-border bg-cc-bg-surface/40">
                            @foreach ($series as $serie)
                                @php
                                    $genreName = optional($serie->genre)->name ?? 'Unknown';
                                    $seasonsCount = $serie->seasons->count();
                                    $rating = $serie->rating ?? 0;
                                @endphp
                                <tr
                                    x-show="matches(@js($serie->title), @js($genreName), @js($seasonsCount))"
                                    class="transition-colors cc-motion-base hover:bg-cc-bg-elevated/40"
                                >
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-medium text-cc-text-primary">{{ $serie->title }}</p>
                                        <p class="mt-1 text-xs text-cc-text-muted">ID {{ $serie->id }}</p>
                                    </td>
                                    <td class="hidden px-4 py-3 align-top text-cc-text-secondary sm:table-cell">{{ $serie->release_date }}</td>
                                    <td class="hidden px-4 py-3 align-top text-cc-text-secondary md:table-cell">{{ $genreName }}</td>
                                    <td class="px-4 py-3 align-top">
                                        <x-ui.badge tone="neutral">{{ $seasonsCount }}</x-ui.badge>
                                    </td>
                                    <td class="hidden px-4 py-3 align-top sm:table-cell">
                                        <x-ui.badge tone="neutral">{{ number_format((float) $rating, 1) }}</x-ui.badge>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <x-ui.button :href="url('/series/' . $serie->id)" variant="ghost" size="sm" class="w-full sm:w-auto">View</x-ui.button>
                                            <x-ui.button :href="route('seasons.manage', $serie->id)" variant="secondary" size="sm" class="w-full sm:w-auto">Seasons</x-ui.button>
                                            <x-ui.button :href="route('content.edit', $serie->id)" variant="secondary" size="sm" class="w-full sm:w-auto">Edit</x-ui.button>
                                            <form action="{{ route('content.destroy', $serie->id) }}" method="POST" onsubmit="return confirm('Delete this series?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button type="submit" variant="danger" size="sm" class="w-full sm:w-auto">Delete</x-ui.button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </section>
</x-app-layout>
