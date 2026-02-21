<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Series</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-app-layout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Edit Series</h2>
                        
                        <form action="{{ route('serie.update', $serie->id) }}" method="POST" enctype="multipart/form-data" class="max-w-2xl mx-auto">
                            @csrf
                            @method('PUT')
                            
                            <!-- Series Title -->
                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700">Series Title*</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $serie->title) }}" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description*</label>
                                <textarea name="description" id="description" rows="3" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $serie->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Release Date -->
                            <div class="mb-4">
                                <label for="release_date" class="block text-sm font-medium text-gray-700">Release Date*</label>
                                <input type="date" name="release_date" id="release_date" value="{{ old('release_date', $serie->release_date) }}" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('release_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Duration -->
                            <div class="mb-4">
                                <label for="duration" class="block text-sm font-medium text-gray-700">Duration (minutes)*</label>
                                <input type="number" name="duration" id="duration" min="1" value="{{ old('duration', $serie->duration) }}" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('duration')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Director -->
                            <div class="mb-4">
                                <label for="director" class="block text-sm font-medium text-gray-700">Director*</label>
                                <input type="text" name="director" id="director" value="{{ old('director', $serie->director) }}" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('director')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Genre  -->
                            <div class="mb-4">
                                <label for="genre_id" class="block text-sm font-medium text-gray-700">Genre*</label>
                                <select name="genre_id" id="genre_id" 
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select a genre</option>
                                    @foreach($genres as $genre)
                                        <option value="{{ $genre->id }}" {{ $serie->genre_id == $genre->id ? 'selected' : '' }}>{{ $genre->name }}</option>
                                    @endforeach
                                </select>
                                @error('genre_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Rating -->
                            <div class="mb-4">
                                <label for="rating" class="block text-sm font-medium text-gray-700">Rating (1-10)</label>
                                <input type="number" name="rating" id="rating" min="0" max="100" step="0.1" value="{{ old('rating', $serie->rating) }}"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('rating')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Series Picture -->
                            <div class="mb-6">
                                <label for="picture" class="block text-sm font-medium text-gray-700">Series Poster</label>
                                <input type="file" name="picture" id="picture" accept="image/*"
                                    class="mt-1 block w-full text-gray-900 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                @error('picture')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @if($serie->picture)
                                    <img src="{{ asset('storage/series/' . $serie->picture) }}" alt="Current Series Poster" class="mt-4 w-32 h-auto">
                                @endif
                            </div>
                            
                            <div class="flex items-center justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Update Series
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
</body>
</html>

