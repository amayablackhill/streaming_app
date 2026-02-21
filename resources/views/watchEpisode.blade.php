<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Episode - {{ $episode->title }}</title>
    
    
    @csrf
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    
    <x-app-layout>

        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="episode-display mb-12">
                <h3 class="text-3xl font-bold mb-4">{{ $episode->title }}</h3>
                    <video controls class="w-full mt-4" id="original-video">
                        <source id="video-source" src="{{ asset('storage/episodes/' . $episode->episode_path) }}" type="video/mp4">
                    </video>
            </div>

            <div class="episode-info flex flex-wrap items-center justify-center">
                <img src="{{ asset('storage/episodes/' . $episode->cover_path) }}" alt="{{ $episode->title }}" loading="lazy" class="w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/5 p-4">
                <div class="episode-info-text w-1/2 md:w-2/3 lg:w-3/4 xl:w-4/5 p-4">
                    <h3 class="text-3xl font-bold mb-2">Title: {{ $episode->title }}</h3>
                    <h3 class="text-3xl font-bold mb-2">Release date: {{ $episode->release_date }}</h3>
                    <p class="text-xl">{{ $episode->plot }}</p>
                </div>
            </div>
        </div>

    </x-app-layout>


</body>
</html>

