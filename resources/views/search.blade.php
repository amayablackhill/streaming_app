<x-app-layout>
    @php
        $adminActionEnabled = auth()->check() && auth()->user()->canAccessAdminPanel();
    @endphp
    <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="cc-stack-6">
        <header class="cc-stack-2">
            <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Curated Search</p>
            <h1 class="cc-title-display">Search Catalog</h1>
            <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                Explore films and series by title, director, year, or synopsis.
            </p>
        </header>

        <section class="cc-surface p-4 sm:p-5">
            <form action="{{ route('search') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <label for="search-q" class="sr-only">Search catalog</label>
                <x-ui.input
                    id="search-q"
                    name="q"
                    type="search"
                    :value="$query"
                    placeholder="Search by title, director, year..."
                    class="w-full"
                />
                <x-ui.button type="submit" variant="secondary" class="w-full sm:w-auto">
                    Search
                </x-ui.button>
            </form>
        </section>

        @if ($query === '')
            <x-ui.empty-state
                title="Start with a title"
                description="Type the name of a film or series to discover curated results."
                :action-label="'Explore home'"
                :action-href="route('home')"
            />
        @elseif ($results && $results->isNotEmpty())
            <section class="cc-stack-4">
                <header class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-cc-text-secondary">
                        {{ $results->total() }} results for
                        <span class="break-all font-medium text-cc-text-primary">"{{ $query }}"</span>
                    </p>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.badge tone="neutral">
                            Page {{ $results->currentPage() }} / {{ $results->lastPage() }}
                        </x-ui.badge>
                        @if ($adminActionEnabled)
                            <x-ui.button :href="route('admin.home')" variant="ghost" size="sm">Admin</x-ui.button>
                            <x-ui.button :href="route('content.add')" variant="secondary" size="sm">Add Content</x-ui.button>
                        @endif
                    </div>
                </header>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($results as $content)
                        @php
                            $detailUrl = url('/' . ($content->type === 'serie' ? 'series' : 'movies') . '/' . $content->id);
                            $releaseYear = $content->release_date ? substr((string) $content->release_date, 0, 4) : 'N/A';
                            $meta = $content->director ?: optional($content->genre)->name;
                        @endphp

                        <x-ui.card-film
                            :title="$content->title"
                            :href="$detailUrl"
                            :image="$content->poster_url"
                            :year="$releaseYear"
                            :eyebrow="$content->type === 'serie' ? 'Series' : 'Film'"
                            :meta="$meta"
                            full-width
                        />

                        @if ($adminActionEnabled)
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <x-ui.button :href="route('content.edit', $content->id)" variant="ghost" size="sm">Edit</x-ui.button>
                                @if ($content->type === 'serie')
                                    <x-ui.button :href="route('seasons.manage', $content->id)" variant="ghost" size="sm">Seasons</x-ui.button>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>

                @if ($results->hasPages())
                    <nav class="flex flex-wrap items-center justify-between gap-3 border-t border-cc-border pt-4" aria-label="Search results pagination">
                        @if ($results->onFirstPage())
                            <span class="text-sm text-cc-text-muted">Previous</span>
                        @else
                            <a href="{{ $results->previousPageUrl() }}" class="text-sm underline decoration-cc-border underline-offset-4 transition-colors cc-motion-base hover:text-cc-text-primary">
                                Previous
                            </a>
                        @endif

                        <p class="text-xs uppercase tracking-label text-cc-text-muted">
                            Showing {{ $results->firstItem() }}-{{ $results->lastItem() }} of {{ $results->total() }}
                        </p>

                        @if ($results->hasMorePages())
                            <a href="{{ $results->nextPageUrl() }}" class="text-sm underline decoration-cc-border underline-offset-4 transition-colors cc-motion-base hover:text-cc-text-primary">
                                Next
                            </a>
                        @else
                            <span class="text-sm text-cc-text-muted">Next</span>
                        @endif
                    </nav>
                @endif
            </section>
        @else
            <x-ui.empty-state
                title="No results found"
                description="We could not find anything. Try another title or explore the curated selection."
                :action-label="'Explore curated home'"
                :action-href="route('home')"
            />
        @endif
        </div>
    </section>
</x-app-layout>
