<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @csrf
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-app-layout>
        <div class="max-w-7xl mx-auto px-4 py-12">
            <h2 class="text-3xl font-bold mb-6">Añadir Nuevo Episodio</h2>
            <form action="{{ isset($episode) && isset($season) ? route('episodes.update', [$season->id, $episode->id]) : route('episodes.store', $season->id) }}" method="POST" enctype="multipart/form-data" class="max-w-2xl mx-auto">
                @csrf
                <div class="mb-4">
                    <label for="episode_number" class="block text-sm font-medium text-gray-700">Número de Episodio</label>
                    <input type="number" name="episode_number" id="episode_number" min="1" value="{{ old('episode_number', $episode->episode_number ?? '') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    @error('episode_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700">Episodio Título</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $episode->title ?? '') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="duration" class="block text-sm font-medium text-gray-700">Duración (minutos)</label>
                    <input type="number" name="duration" id="duration" min="1" value="{{ old('duration', $episode->duration ?? '') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    @error('duration')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="release_date" class="block text-sm font-medium text-gray-700">Fecha de Lanzamiento</label>
                    <input type="date" name="release_date" id="release_date" value="{{ old('release_date', $episode->release_date ?? '') }}"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    @error('release_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="plot" class="block text-sm font-medium text-gray-700">Trama</label>
                    <textarea name="plot" id="plot" rows="3"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('plot', $episode->plot ?? '') }}</textarea>
                    @error('plot')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="cover_path" class="block text-sm font-medium text-gray-700">Path de Portada</label>
                    <input type="file" name="cover_path" id="cover_path" accept="image/*"
                        class="mt-1 block w-full text-gray-900 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    @error('cover_path')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="path" class="block text-sm font-medium text-gray-700">Path del Episodio</label>
                    <input type="file" name="path" id="path" accept="video/*"
                        class="mt-1 block w-full text-gray-900 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    @error('path')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ isset($episode) && isset($season) ? 'Actualizar Episodio' : 'Añadir Episodio' }}
                    </button>
                </div>
            </form>
        </div>
    </x-app-layout>
</body>
</html>

