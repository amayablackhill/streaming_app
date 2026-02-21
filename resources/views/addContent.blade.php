<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-lg">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-slate-100">Add New Content</h1>
                    <p class="mt-1 text-sm text-slate-400">Upload trailers or short demo clips for HLS processing.</p>
                </div>

                @if (session('video_asset_id'))
                    <div class="mb-6 rounded-lg border border-amber-500/30 bg-amber-500/10 p-4">
                        <div class="mb-1 flex items-center gap-2">
                            <x-status-badge status="processing" />
                            <span class="text-sm font-medium text-amber-200">Video pipeline started</span>
                        </div>
                        <a class="text-sm text-amber-300 underline hover:text-amber-200" href="{{ route('video-assets.show', session('video_asset_id')) }}">
                            Open playback/status page
                        </a>
                    </div>
                @endif

                <form action="{{ route('content.add') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="type" class="block text-sm font-medium text-slate-200">Type*</label>
                        <select name="type" id="type" class="mt-1 block w-full rounded-md border-slate-700 bg-slate-950 text-slate-100 focus:border-red-500 focus:ring-red-500">
                            <option value="">Select type</option>
                            <option value="film" @selected(old('type') === 'film')>Film</option>
                            <option value="serie" @selected(old('type') === 'serie')>Serie</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-slate-200">Content Title*</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="mt-1 block w-full rounded-md border-slate-700 bg-slate-950 text-slate-100 focus:border-red-500 focus:ring-red-500">
                        @error('title')
                            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-slate-200">Description*</label>
                        <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-slate-700 bg-slate-950 text-slate-100 focus:border-red-500 focus:ring-red-500">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="release_date" class="block text-sm font-medium text-slate-200">Release Date*</label>
                        <input type="date" name="release_date" id="release_date" value="{{ old('release_date') }}" class="mt-1 block w-full rounded-md border-slate-700 bg-slate-950 text-slate-100 focus:border-red-500 focus:ring-red-500">
                        @error('release_date')
                            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="duration" class="block text-sm font-medium text-slate-200">Duration (minutes)*</label>
                        <input type="number" name="duration" id="duration" min="1" value="{{ old('duration') }}" class="mt-1 block w-full rounded-md border-slate-700 bg-slate-950 text-slate-100 focus:border-red-500 focus:ring-red-500">
                        @error('duration')
                            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="director" class="block text-sm font-medium text-slate-200">Director*</label>
                        <input type="text" name="director" id="director" value="{{ old('director') }}" class="mt-1 block w-full rounded-md border-slate-700 bg-slate-950 text-slate-100 focus:border-red-500 focus:ring-red-500">
                        @error('director')
                            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="genre_id" class="block text-sm font-medium text-slate-200">Genre*</label>
                        <select name="genre_id" id="genre_id" class="mt-1 block w-full rounded-md border-slate-700 bg-slate-950 text-slate-100 focus:border-red-500 focus:ring-red-500">
                            <option value="">Select a genre</option>
                            @foreach($genres as $genre)
                                <option value="{{ $genre->id }}" @selected(old('genre_id') == $genre->id)>{{ $genre->name }}</option>
                            @endforeach
                        </select>
                        @error('genre_id')
                            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="rating" class="block text-sm font-medium text-slate-200">Rating (1-100)</label>
                        <input type="number" name="rating" id="rating" min="0" max="100" step="0.1" value="{{ old('rating') }}" class="mt-1 block w-full rounded-md border-slate-700 bg-slate-950 text-slate-100 focus:border-red-500 focus:ring-red-500">
                        @error('rating')
                            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="picture" class="block text-sm font-medium text-slate-200">Content Poster</label>
                        <input type="file" name="picture" id="picture" accept="image/*" class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-200 file:mr-3 file:rounded-md file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-slate-200 hover:file:bg-slate-700">
                        @error('picture')
                            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="video" class="block text-sm font-medium text-slate-200">Content Video</label>
                        <input type="file" name="video" id="video" accept="video/*" class="mt-1 block w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-200 file:mr-3 file:rounded-md file:border-0 file:bg-slate-800 file:px-3 file:py-2 file:text-slate-200 hover:file:bg-slate-700">
                        @error('video')
                            <p class="mt-1 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-slate-950">
                            Add Content
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
