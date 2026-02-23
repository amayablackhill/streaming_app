<x-app-layout>
    @php
        $isEditing = isset($episode);
        $series = $season->contents ?? $season->content;
        $seriesTitle = $series?->title ?? 'Series';
        $formAction = $isEditing
            ? route('episodes.update', [$season->id, $episode->id])
            : route('episodes.store', $season->id);
    @endphp

    <section class="cc-stack-6">
        <header class="cc-stack-2">
            <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Admin · Episodes Workspace</p>
            <h1 class="cc-title-display">{{ $isEditing ? 'Edit Episode' : 'Add Episode' }}</h1>
            <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                {{ $seriesTitle }} · Season {{ $season->season_number }}
            </p>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge tone="neutral">Season {{ $season->season_number }}</x-ui.badge>
                @if ($isEditing)
                    <x-ui.badge tone="premium">Episode #{{ $episode->episode_number }}</x-ui.badge>
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

        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="cc-stack-6">
            @csrf

            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                <header class="cc-stack-2">
                    <x-ui.badge tone="neutral">Metadata</x-ui.badge>
                    <h2 class="cc-title-section">Episode Info</h2>
                </header>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="cc-stack-2">
                        <label for="episode_number" class="text-sm font-medium text-cc-text-secondary">Episode number *</label>
                        <x-ui.input
                            id="episode_number"
                            name="episode_number"
                            type="number"
                            min="1"
                            :value="old('episode_number', $episode->episode_number ?? '')"
                            :invalid="$errors->has('episode_number')"
                        />
                    </div>

                    <div class="cc-stack-2 md:col-span-2">
                        <label for="title" class="text-sm font-medium text-cc-text-secondary">Title *</label>
                        <x-ui.input
                            id="title"
                            name="title"
                            type="text"
                            :value="old('title', $episode->title ?? '')"
                            :invalid="$errors->has('title')"
                        />
                    </div>

                    <div class="cc-stack-2">
                        <label for="duration" class="text-sm font-medium text-cc-text-secondary">Duration (minutes) *</label>
                        <x-ui.input
                            id="duration"
                            name="duration"
                            type="number"
                            min="1"
                            :value="old('duration', $episode->duration ?? '')"
                            :invalid="$errors->has('duration')"
                        />
                    </div>

                    <div class="cc-stack-2">
                        <label for="release_date" class="text-sm font-medium text-cc-text-secondary">Release date *</label>
                        <x-ui.input
                            id="release_date"
                            name="release_date"
                            type="date"
                            :value="old('release_date', $episode->release_date ?? '')"
                            :invalid="$errors->has('release_date')"
                        />
                    </div>
                </div>

                <div class="cc-stack-2">
                    <label for="plot" class="text-sm font-medium text-cc-text-secondary">Plot</label>
                    <textarea
                        id="plot"
                        name="plot"
                        rows="4"
                        class="cc-input w-full text-sm leading-editorial"
                    >{{ old('plot', $episode->plot ?? '') }}</textarea>
                </div>
            </section>

            <section class="cc-surface cc-stack-4 p-4 sm:p-5">
                <header class="cc-stack-2">
                    <x-ui.badge tone="neutral">Media</x-ui.badge>
                    <h2 class="cc-title-section">Cover & Episode File</h2>
                </header>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="cc-stack-2">
                        <label for="cover_path" class="text-sm font-medium text-cc-text-secondary">Cover image</label>
                        <input
                            id="cover_path"
                            name="cover_path"
                            type="file"
                            accept="image/*"
                            class="cc-input w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-cc-bg-elevated file:px-3 file:py-2 file:text-cc-text-secondary hover:file:text-cc-text-primary"
                        >
                    </div>

                    <div class="cc-stack-2">
                        <label for="episode_path" class="text-sm font-medium text-cc-text-secondary">Episode video (optional)</label>
                        <input
                            id="episode_path"
                            name="episode_path"
                            type="file"
                            accept="video/mp4,video/*"
                            class="cc-input w-full text-sm file:mr-3 file:rounded-sm file:border-0 file:bg-cc-bg-elevated file:px-3 file:py-2 file:text-cc-text-secondary hover:file:text-cc-text-primary"
                        >
                        <p class="text-xs text-cc-text-muted">Max 25MB (demo clip). Replaces existing file if uploaded.</p>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button type="submit" variant="primary">
                    {{ $isEditing ? 'Update episode' : 'Create episode' }}
                </x-ui.button>
                <x-ui.button :href="route('seasons.manage', $season->serie_id)" variant="ghost">Back to seasons</x-ui.button>
            </div>
        </form>
    </section>
</x-app-layout>
