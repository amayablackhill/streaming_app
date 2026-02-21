<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOME</title>
    
    
    @csrf
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    
    <x-app-layout>
        <div class="py-12">
            <!-- Carrusel 1 - Popular -->
            <div class="netflix-slider">
                <h2 class="section-title">Popular on Netflix</h2>
                <div class="swiper popular-swiper">
                    <div class="swiper-wrapper">
                        @foreach($contents as $content)
                        <div class="swiper-slide">
                            <a href="/{{ $content->type == 'serie' ? 'series' : 'movies' }}/{{ $content->id }}">
                                <img src="{{ asset('storage/' . ($content->type == 'film' ? 'movies' : 'series') . '/' . $content->picture) }}" alt="{{ $content->name }}" loading="lazy" style="width: 300px; height: 450px">
                            </a>   
                            <h2>{{ $content->name }}</h2>
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>


            <!-- Carrusel 2 - Trending -->
            <div class="netflix-slider">
                <h2 class="section-title">Trending Now</h2>
                <div class="swiper trending-swiper">
                    <div class="swiper-wrapper">
                        @foreach($contents->shuffle() as $content)
                        <div class="swiper-slide">
                                <a href="/movies/{{ $content->id }}"> 
                                    <img src="{{ asset('storage/' . ($content->type == 'film' ? 'movies' : 'series') . '/' . $content->picture) }}" alt="{{ $content->name }}" loading="lazy" style="width: 300px; height: 450px">
                                </a>
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>


            <!-- Carrusel 3 - Action Movies -->
            <div class="netflix-slider">
                <h2 class="section-title">Action Movies</h2>
                <div class="swiper action-swiper">
                    <div class="swiper-wrapper">
                        @foreach($contents->filter(fn($content) => str_contains($content->genre, 'Action')) as $content)
                        <div class="swiper-slide">
                            <a href="/movies/{{ $content->id }}">
                                <img src="{{ asset('storage/' . ($content->type == 'film' ? 'movies' : 'series') . '/' . $content->picture) }}" alt="{{ $content->name }}" loading="lazy" style="width: 300px; height: 450px">
                            </a>
                            <h2>{{ $content->name }}</h2>
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>

            <!-- Carrusel 4 - Horror Movies -->
            <div class="netflix-slider">
                <h2 class="section-title">Horror Movies</h2>
                <div class="swiper action-swiper">
                    <div class="swiper-wrapper">
                        @foreach($contents->filter(fn($content) => str_contains($content->genre, 'Terror')) as $content)
                        <div class="swiper-slide">
                            <a href="/movies/{{ $content->id }}">
                                <img src="{{ asset('storage/' . ($content->type == 'film' ? 'movies' : 'series') . '/' . $content->picture) }}" alt="{{ $content->name }}" loading="lazy" style="width: 300px; height: 450px">
                            </a>
                            <h2>{{ $content->name }}</h2>
                        </div>
                        @endforeach
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>


        </div>
    </x-app-layout>
</body>
</html>