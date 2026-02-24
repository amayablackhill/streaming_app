<x-app-layout>
    <section class="cc-stack-6">
        <header class="cc-stack-2">
            <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Admin · Curator Workflow</p>
            <h1 class="cc-title-display">Add New Content</h1>
            <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                Publish catalog entries with legal trailers or short demo clips for the HLS pipeline.
            </p>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge tone="neutral">Step 1 Identity</x-ui.badge>
                <x-ui.badge tone="neutral">Step 2 Metadata</x-ui.badge>
                <x-ui.badge tone="neutral">Step 3 Media</x-ui.badge>
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

        @if (session('video_asset_id'))
            <x-ui.alert tone="success" title="Video pipeline started">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="text-sm">Your clip is processing asynchronously.</span>
                    <x-ui.button
                        :href="route('video-assets.show', session('video_asset_id'))"
                        variant="secondary"
                        size="sm"
                    >
                        Open status page
                    </x-ui.button>
                </div>
            </x-ui.alert>
        @endif

        <form action="{{ route('content.add') }}" method="POST" enctype="multipart/form-data" class="cc-stack-6">
            @csrf

            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                <header class="cc-stack-2">
                    <x-ui.badge tone="neutral">Step 1</x-ui.badge>
                    <h2 class="cc-title-section">Identity</h2>
                </header>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="cc-stack-2">
                        <label for="type" class="text-sm font-medium text-cc-text-secondary">Type *</label>
                        <select name="type" id="type" class="cc-input w-full text-sm">
                            <option value="">Select type</option>
                            <option value="film" @selected(old('type') === 'film')>Film</option>
                            <option value="serie" @selected(old('type') === 'serie')>Series</option>
                        </select>
                        @error('type')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="cc-stack-2">
                        <label for="title" class="text-sm font-medium text-cc-text-secondary">Title *</label>
                        <x-ui.input
                            id="title"
                            name="title"
                            type="text"
                            :value="old('title')"
                            :invalid="$errors->has('title')"
                            autocomplete="off"
                        />
                        @error('title')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="cc-stack-2">
                    <label for="description" class="text-sm font-medium text-cc-text-secondary">Synopsis *</label>
                    <textarea
                        name="description"
                        id="description"
                        rows="4"
                        class="cc-input w-full text-sm leading-editorial"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>

                <div class="cc-stack-2">
                    <label class="inline-flex items-center gap-2 text-sm text-cc-text-secondary">
                        <input
                            type="checkbox"
                            name="is_featured"
                            value="1"
                            @checked(old('is_featured'))
                            class="rounded-sm border-cc-border bg-cc-bg-elevated text-cc-accent focus:ring-0"
                        >
                        <span>Set as featured film on Home hero</span>
                    </label>
                    <p class="text-xs text-cc-text-muted">If enabled, this film becomes the main spotlight in Home.</p>
                </div>
            </section>

            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                <header class="cc-stack-2">
                    <x-ui.badge tone="neutral">Step 2</x-ui.badge>
                    <h2 class="cc-title-section">Metadata</h2>
                </header>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="cc-stack-2">
                        <label for="release_date" class="text-sm font-medium text-cc-text-secondary">Release date *</label>
                        <x-ui.input id="release_date" name="release_date" type="date" :value="old('release_date')" :invalid="$errors->has('release_date')" />
                        @error('release_date')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="cc-stack-2">
                        <label for="duration" class="text-sm font-medium text-cc-text-secondary">Duration (minutes) *</label>
                        <x-ui.input id="duration" name="duration" type="number" min="1" :value="old('duration')" :invalid="$errors->has('duration')" />
                        @error('duration')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="cc-stack-2">
                        <label for="director" class="text-sm font-medium text-cc-text-secondary">Director *</label>
                        <x-ui.input id="director" name="director" type="text" :value="old('director')" :invalid="$errors->has('director')" />
                        @error('director')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="cc-stack-2">
                        <label for="genre_id" class="text-sm font-medium text-cc-text-secondary">Genre *</label>
                        <select name="genre_id" id="genre_id" class="cc-input w-full text-sm">
                            <option value="">Select genre</option>
                            @foreach ($genres as $genre)
                                <option value="{{ $genre->id }}" @selected((string) old('genre_id') === (string) $genre->id)>
                                    {{ $genre->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('genre_id')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="cc-stack-2 md:max-w-xs">
                    <label for="rating" class="text-sm font-medium text-cc-text-secondary">Rating (0–100)</label>
                    <x-ui.input id="rating" name="rating" type="number" min="0" max="100" step="0.1" :value="old('rating')" :invalid="$errors->has('rating')" />
                    @error('rating')
                        <p class="text-xs text-rose-300">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                <header class="cc-stack-2">
                    <x-ui.badge tone="neutral">Step 3</x-ui.badge>
                    <h2 class="cc-title-section">Media</h2>
                    <p class="text-xs text-cc-text-muted">
                        Demo clip limits: MP4, 25MB max. If clip duration exceeds 20s, pipeline will fail gracefully.
                    </p>
                </header>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="cc-stack-2">
                        <label for="poster_path" class="text-sm font-medium text-cc-text-secondary">Alternative poster (TMDB path or URL)</label>
                        <x-ui.input id="poster_path" name="poster_path" type="text" :value="old('poster_path')" placeholder="/abc123.jpg or https://..." :invalid="$errors->has('poster_path')" />
                        <p class="text-xs text-cc-text-muted">Optional. Accepts TMDB path or full image URL.</p>
                        @error('poster_path')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="cc-stack-2">
                        <label for="backdrop_path" class="text-sm font-medium text-cc-text-secondary">Alternative backdrop (TMDB path or URL)</label>
                        <x-ui.input id="backdrop_path" name="backdrop_path" type="text" :value="old('backdrop_path')" placeholder="/xyz789.jpg or https://..." :invalid="$errors->has('backdrop_path')" />
                        <p class="text-xs text-cc-text-muted">Optional. Used for hero/detail backdrop.</p>
                        @error('backdrop_path')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="cc-stack-2">
                        <label for="picture" class="text-sm font-medium text-cc-text-secondary">Poster image</label>
                        <input
                            type="file"
                            name="picture"
                            id="picture"
                            accept="image/*"
                            class="cc-input w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-cc-bg-elevated file:px-3 file:py-2 file:text-cc-text-secondary hover:file:text-cc-text-primary"
                        >
                        @error('picture')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="cc-stack-2">
                        <label for="video" class="text-sm font-medium text-cc-text-secondary">Trailer / demo clip</label>
                        <input
                            type="file"
                            name="video"
                            id="video"
                            accept="video/mp4,video/*"
                            class="cc-input w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-cc-bg-elevated file:px-3 file:py-2 file:text-cc-text-secondary hover:file:text-cc-text-primary"
                        >
                        @error('video')
                            <p class="text-xs text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button type="submit" variant="primary" size="sm">Publish content</x-ui.button>
                <x-ui.button :href="route('admin.home')" variant="ghost" size="sm">Cancel</x-ui.button>
            </div>
        </form>
    </section>
</x-app-layout>
