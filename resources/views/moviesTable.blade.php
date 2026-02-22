<x-app-layout>
    <section class="cc-stack-6" x-data="{
        query: '',
        minRating: '',
        matches(title, genre, releaseDate, rating) {
            const q = this.query.trim().toLowerCase();
            const haystack = `${title} ${genre} ${releaseDate}`.toLowerCase();
            const passesQuery = !q || haystack.includes(q);
            const passesRating = !this.minRating || Number(rating || 0) >= Number(this.minRating);
            return passesQuery && passesRating;
        }
    }">
        <header class="cc-stack-2 sm:flex sm:items-end sm:justify-between">
            <div class="cc-stack-2">
                <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Admin · Catalog Table</p>
                <h1 class="cc-title-display">Movies</h1>
                <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                    Curate film entries, review metadata quality and maintain catalog consistency.
                </p>
                <div class="flex items-center gap-2">
                    <x-ui.badge tone="neutral">{{ $movies->count() }} total movies</x-ui.badge>
                </div>
            </div>

            <x-ui.button :href="route('content.add')" variant="secondary" size="sm" class="w-full sm:w-auto">
                Add new content
            </x-ui.button>
        </header>

        <section class="cc-surface cc-stack-4 p-4 sm:p-5">
            <header class="cc-stack-2">
                <h2 class="cc-title-section">Filters</h2>
                <p class="text-xs text-cc-text-muted">Use quick filters to locate records before editing.</p>
            </header>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="cc-stack-2">
                    <label for="movieQuery" class="text-xs uppercase tracking-label text-cc-text-muted">Search title / genre / date</label>
                    <x-ui.input id="movieQuery" x-model.debounce.200ms="query" type="text" placeholder="e.g. dune, drama, 2024-05-10" />
                </div>

                <div class="cc-stack-2">
                    <label for="movieRatingFilter" class="text-xs uppercase tracking-label text-cc-text-muted">Minimum rating</label>
                    <select id="movieRatingFilter" x-model="minRating" class="cc-input w-full text-sm">
                        <option value="">Any rating</option>
                        <option value="25">25+</option>
                        <option value="50">50+</option>
                        <option value="75">75+</option>
                        <option value="90">90+</option>
                    </select>
                </div>
            </div>
        </section>

        @if ($movies->isEmpty())
            <x-ui.empty-state
                title="No movies in catalog"
                description="Create your first film entry to start managing the movie table."
                :action-label="'Add movie'"
                :action-href="route('content.add')"
            />
        @else
            <section class="cc-surface overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-[40rem] divide-y divide-cc-border text-sm sm:min-w-full">
                        <thead class="bg-cc-bg-elevated/70">
                            <tr class="text-left text-xs uppercase tracking-label text-cc-text-muted">
                                <th scope="col" class="px-4 py-3">Title</th>
                                <th scope="col" class="hidden px-4 py-3 sm:table-cell">Release</th>
                                <th scope="col" class="hidden px-4 py-3 md:table-cell">Genre</th>
                                <th scope="col" class="hidden px-4 py-3 sm:table-cell">Rating</th>
                                <th scope="col" class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cc-border bg-cc-bg-surface/40">
                            @foreach ($movies as $movie)
                                @php
                                    $genreName = optional($movie->genre)->name ?? 'Unknown';
                                    $rating = $movie->rating ?? 0;
                                @endphp
                                <tr
                                    x-show="matches(@js($movie->title), @js($genreName), @js((string) $movie->release_date), @js((float) $rating))"
                                    class="transition-colors cc-motion-base hover:bg-cc-bg-elevated/40"
                                >
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-medium text-cc-text-primary">{{ $movie->title }}</p>
                                        <p class="mt-1 text-xs text-cc-text-muted">ID {{ $movie->id }}</p>
                                    </td>
                                    <td class="hidden px-4 py-3 align-top text-cc-text-secondary sm:table-cell">{{ $movie->release_date }}</td>
                                    <td class="hidden px-4 py-3 align-top text-cc-text-secondary md:table-cell">{{ $genreName }}</td>
                                    <td class="hidden px-4 py-3 align-top sm:table-cell">
                                        <x-ui.badge tone="neutral">{{ number_format((float) $rating, 1) }}</x-ui.badge>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <x-ui.button :href="url('/movies/' . $movie->id)" variant="ghost" size="sm" class="w-full sm:w-auto">View</x-ui.button>
                                            <x-ui.button :href="route('content.edit', $movie->id)" variant="secondary" size="sm" class="w-full sm:w-auto">Edit</x-ui.button>
                                            <form action="{{ route('content.destroy', $movie->id) }}" method="POST" onsubmit="return confirm('Delete this movie?')">
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
