<x-app-layout>
    @php
        $isSeries = $content->type === 'serie';
        $posterDirectory = $isSeries ? 'series' : 'movies';
        $currentPoster = $content->picture ? asset('storage/' . $posterDirectory . '/' . $content->picture) : null;
        $tableRoute = $isSeries ? 'series.table' : 'movies.table';
    @endphp

    <section class="cc-stack-6">
        <header class="cc-stack-2">
            <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Admin · Curator Workflow</p>
            <h1 class="cc-title-display">Edit {{ $isSeries ? 'Series' : 'Film' }}</h1>
            <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                Update metadata and media while keeping catalog quality consistent.
            </p>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge tone="neutral">Content #{{ $content->id }}</x-ui.badge>
                <x-ui.badge tone="neutral">{{ $isSeries ? 'Series' : 'Film' }}</x-ui.badge>
                @if (!$isSeries && $content->is_featured)
                    <x-ui.badge tone="premium">Featured</x-ui.badge>
                @endif
            </div>
        </header>

        @if ($errors->any())
            <x-ui.alert tone="error" title="Validation error">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form action="{{ route('content.update', $content->id) }}" method="POST" enctype="multipart/form-data" class="cc-stack-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="{{ $content->type }}">

            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                <header class="cc-stack-2">
                    <x-ui.badge tone="neutral">Identity</x-ui.badge>
                    <h2 class="cc-title-section">Main Info</h2>
                </header>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="cc-stack-2 md:col-span-2">
                        <label for="title" class="text-sm font-medium text-cc-text-secondary">Title *</label>
                        <x-ui.input id="title" name="title" type="text" :value="old('title', $content->title)" :invalid="$errors->has('title')" />
                    </div>

                    <div class="cc-stack-2 md:col-span-2">
                        <label for="description" class="text-sm font-medium text-cc-text-secondary">Synopsis *</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="cc-input w-full text-sm leading-editorial"
                        >{{ old('description', $content->description) }}</textarea>
                    </div>

                    <div class="cc-stack-2">
                        <label for="release_date" class="text-sm font-medium text-cc-text-secondary">Release date *</label>
                        <x-ui.input id="release_date" name="release_date" type="date" :value="old('release_date', $content->release_date)" :invalid="$errors->has('release_date')" />
                    </div>

                    <div class="cc-stack-2">
                        <label for="duration" class="text-sm font-medium text-cc-text-secondary">Duration (minutes) *</label>
                        <x-ui.input id="duration" name="duration" type="number" min="1" :value="old('duration', $content->duration)" :invalid="$errors->has('duration')" />
                    </div>

                    <div class="cc-stack-2">
                        <label for="director" class="text-sm font-medium text-cc-text-secondary">Director *</label>
                        <x-ui.input id="director" name="director" type="text" :value="old('director', $content->director)" :invalid="$errors->has('director')" />
                    </div>

                    <div class="cc-stack-2">
                        <label for="genre_id" class="text-sm font-medium text-cc-text-secondary">Genre *</label>
                        <select name="genre_id" id="genre_id" class="cc-input w-full text-sm">
                            <option value="">Select genre</option>
                            @foreach ($genres as $genre)
                                <option value="{{ $genre->id }}" @selected((string) old('genre_id', $content->genre_id) === (string) $genre->id)>
                                    {{ $genre->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="cc-stack-2 md:max-w-xs">
                    <label for="rating" class="text-sm font-medium text-cc-text-secondary">Rating (0-100)</label>
                    <x-ui.input id="rating" name="rating" type="number" min="0" max="100" step="0.1" :value="old('rating', $content->rating)" :invalid="$errors->has('rating')" />
                </div>

                @if (!$isSeries)
                    <div class="cc-stack-2">
                        <label class="inline-flex items-center gap-2 text-sm text-cc-text-secondary">
                            <input
                                type="checkbox"
                                name="is_featured"
                                value="1"
                                @checked(old('is_featured', $content->is_featured))
                                class="rounded-sm border-cc-border bg-cc-bg-elevated text-cc-accent focus:ring-0"
                            >
                            <span>Set as featured film on Home hero</span>
                        </label>
                    </div>
                @endif
            </section>

            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                <header class="cc-stack-2">
                    <x-ui.badge tone="neutral">Media</x-ui.badge>
                    <h2 class="cc-title-section">Poster & Demo Clip</h2>
                </header>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="cc-stack-2">
                        <label for="picture" class="text-sm font-medium text-cc-text-secondary">Poster image</label>
                        <input
                            type="file"
                            name="picture"
                            id="picture"
                            accept="image/*"
                            class="cc-input w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-cc-bg-elevated file:px-3 file:py-2 file:text-cc-text-secondary hover:file:text-cc-text-primary"
                        >
                        @if ($currentPoster)
                            <img src="{{ $currentPoster }}" alt="{{ $content->title }} poster" class="mt-2 h-40 w-auto rounded-sm border border-cc-border object-cover">
                        @endif
                    </div>

                    <div class="cc-stack-2">
                        <label for="video" class="text-sm font-medium text-cc-text-secondary">Replace trailer / demo clip</label>
                        <input
                            type="file"
                            name="video"
                            id="video"
                            accept="video/mp4,video/*"
                            class="cc-input w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-cc-bg-elevated file:px-3 file:py-2 file:text-cc-text-secondary hover:file:text-cc-text-primary"
                        >
                        <p class="text-xs text-cc-text-muted">Optional. If provided, new processing jobs will be queued.</p>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button type="submit" variant="primary" size="sm">Update content</x-ui.button>
                <x-ui.button :href="route($tableRoute)" variant="ghost" size="sm">Back to table</x-ui.button>
                @if ($isSeries)
                    <x-ui.button :href="route('seasons.manage', $content->id)" variant="secondary" size="sm">Manage seasons</x-ui.button>
                @endif
            </div>
        </form>
    </section>
</x-app-layout>
