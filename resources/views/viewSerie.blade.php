<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie - {{ $content->title }}</title>
    
    
    @csrf
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-app-layout>

    <div class="max-w-7xl mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold mb-6">{{ $content->title }}</h2>
        <div class="grid grid-cols-4 gap-4 mb-4">
            <div class="col-span-2">
                <img src="{{ asset('storage/series/' . $content->picture) }}" alt="{{ $content->title }}" loading="lazy" class="w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/5 p-4">
            </div>
            <div class="col-span-2 p-4">
                <p class="text-2xl font-bold">{{ $content->title }}</p>
                <p class="text-lg">{{ $content->description }}</p>
            </div>
        </div>

        <div class="mt-10">
            <h3 class="text-2xl font-semibold mb-4">Temporadas</h3>

            @foreach ($content->seasons as $season)
                <div class="mb-6 border border-gray-200 rounded-lg p-4">
                    <h4 class="text-xl font-bold mb-2">Temporada {{ $season->season_number }}</h4>
                    <ul class="list-disc pl-5">
                        @foreach ($season->episodes as $episode)
                            <li>
                                <a href="{{ route('episodes.watch', [$content->id, $season->id, $episode->id]) }}" class="text-blue-600 hover:underline">
                                    <strong>Capítulo {{ $episode->episode_number }}:</strong> {{ $episode->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>


    </x-app-layout>


</body>
</html>
