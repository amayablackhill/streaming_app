<x-app-layout>
    <x-slot name="header">
        <h1 class="font-serif text-2xl text-cc-text-primary">TMDB Import</h1>
    </x-slot>

    <section class="cc-stack-5">
        <header class="cc-stack-2">
            <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Metadata Source</p>
            <h2 class="cc-title-section">Import curated titles from TMDB</h2>
            <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                Search by title, preview results, and import metadata into the local catalog.
                TV imports create seasons immediately and queue episodes sync in background jobs.
            </p>
        </header>

        @if (session('status'))
            <x-ui.alert tone="success" title="Import completed">{{ session('status') }}</x-ui.alert>
        @endif

        @if (session('error'))
            <x-ui.alert tone="error" title="Import error">{{ session('error') }}</x-ui.alert>
        @endif

        @if (!$tmdbEnabled)
            <x-ui.alert tone="warning" title="TMDB disabled">
                Set <code>TMDB_TOKEN</code> in your environment to enable TMDB search and import.
            </x-ui.alert>
        @endif

        @if ($errorMessage)
            <x-ui.alert tone="error" title="Search failed">{{ $errorMessage }}</x-ui.alert>
        @endif

        <section class="cc-surface cc-stack-4 p-4 sm:p-5">
            <form method="GET" action="{{ route('admin.tmdb.search') }}" class="grid gap-3 md:grid-cols-[minmax(0,1fr)_180px_auto] md:items-end">
                <div class="cc-stack-2">
                    <label for="tmdb-query" class="text-xs uppercase tracking-[0.12em] text-cc-text-muted">Title</label>
                    <x-ui.input
                        id="tmdb-query"
                        name="q"
                        :value="$query"
                        placeholder="Search movie or TV title"
                        :disabled="!$tmdbEnabled"
                        required
                    />
                </div>

                <div class="cc-stack-2">
                    <label for="tmdb-type" class="text-xs uppercase tracking-[0.12em] text-cc-text-muted">Type</label>
                    <select
                        id="tmdb-type"
                        name="type"
                        @class([
                            'h-10 rounded-sm border bg-cc-bg-primary px-3 text-sm',
                            'border-cc-border text-cc-text-primary',
                        ])
                        @disabled(!$tmdbEnabled)
                    >
                        <option value="movie" @selected($type === 'movie')>Movie</option>
                        <option value="tv" @selected($type === 'tv')>TV</option>
                    </select>
                </div>

                <div>
                    <x-ui.button type="submit" variant="secondary" size="sm" :disabled="!$tmdbEnabled">
                        Search
                    </x-ui.button>
                </div>
            </form>
        </section>

        @if ($query !== '' && empty($results))
            <x-ui.empty-state
                title="No TMDB matches"
                description="Try a different title or switch the type filter between movie and TV."
            />
        @endif

        @if (!empty($results))
            <section class="cc-surface overflow-hidden">
                <table class="w-full border-collapse text-left text-sm">
                    <thead class="bg-cc-bg-elevated/70 text-xs uppercase tracking-[0.1em] text-cc-text-muted">
                        <tr>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Release</th>
                            <th class="px-4 py-3">Rating</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results as $result)
                            <tr class="border-t border-cc-border">
                                <td class="px-4 py-3 text-cc-text-primary">{{ $result['title'] }}</td>
                                <td class="px-4 py-3 text-cc-text-secondary">{{ $result['release_date'] ?: 'N/A' }}</td>
                                <td class="px-4 py-3 text-cc-text-secondary">
                                    {{ $result['rating_average'] !== null ? number_format((float) $result['rating_average'], 1) : 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-ui.badge tone="neutral">{{ strtoupper($result['tmdb_type']) }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.tmdb.import') }}" class="inline-flex">
                                        @csrf
                                        <input type="hidden" name="tmdb_id" value="{{ $result['tmdb_id'] }}">
                                        <input type="hidden" name="tmdb_type" value="{{ $result['tmdb_type'] }}">
                                        <input type="hidden" name="q" value="{{ $query }}">
                                        <input type="hidden" name="type" value="{{ $type }}">
                                        <input type="hidden" name="page" value="{{ $page }}">

                                        <x-ui.button type="submit" variant="ghost" size="sm" :disabled="!$tmdbEnabled">
                                            Import
                                        </x-ui.button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif
    </section>
</x-app-layout>
