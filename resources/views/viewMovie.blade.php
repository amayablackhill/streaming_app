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
            <div class="movie-display mb-12">
                <h3 class="text-3xl font-bold mb-4">{{ $content->title }}</h3>
                <div class="aspect-w-16 aspect-h-12">
                    <label for="resolution" class="block text-sm font-medium text-gray-700">Select Resolution</label>
                    <select id="resolution" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="max">Max resolution</option>
                        <option value="mid">Medium resolution</option>
                        <option value="min">Min resolution</option>
                    </select>
                    <video controls class="w-full mt-4" id="original-video">
                        <source id="video-source" src="" type="video/mp4">
                    </video>

                    <div class="grid grid-cols-9 gap-0">
                        @foreach (range(1, 9) as $i)
                            <div>
                                <a onclick="loadTimeStamp(frameTimes[{{ $i - 1 }}])">
                                    <img src="{{ asset("storage/content/frames/{$content->video}/frame_00{$i}.jpg") }}" alt="" style="width: 100%; height: auto;">
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <script>
                        var frameTimes = @json(json_decode(file_get_contents(storage_path('app/public/content/frames/'.$content->video.'/times.json'))));

                        function loadTimeStamp(time) {
                            var video = document.getElementById("original-video");
                            video.currentTime = time;
                            video.play();
                        }

                        document.addEventListener("DOMContentLoaded", function() {
                            var resolutionSelect = document.getElementById('resolution');
                            var videoSource = document.getElementById('video-source');
                            
                            if (resolutionSelect && videoSource) {
                                var resolutionDefault = (window.innerWidth > 1024) ? "max" : (window.innerWidth > 768) ? "mid" : (window.innerWidth > 480) ? "min" : "min";
                                resolutionSelect.value = resolutionDefault;
                                
                                videoSource.src = `{{ asset('storage/content/') }}/${resolutionDefault}_{{ $content->video }}`;
                                videoSource.parentElement.load();
                            }

                            document.getElementById('resolution').addEventListener('change', function() {
                                var resolution = this.value;
                                var videoSource = document.getElementById('video-source');
                                if (videoSource) {
                                    videoSource.src = `{{ asset('storage/content/') }}/${resolution}_{{ $content->video }}`;
                                    videoSource.parentElement.load();
                                }
                            });
                        });
                    </script>

            <div class="movie-info flex flex-wrap items-center justify-center">
                <img src="{{ asset('storage/movies/' . $content->picture) }}" alt="{{ $content->title }}" loading="lazy" class="w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/5 p-4">
                <div class="movie-info-text w-1/2 md:w-2/3 lg:w-3/4 xl:w-4/5 p-4">
                    <h3 class="text-3xl font-bold mb-2">Title: {{ $content->title }}</h3>
                    <h3 class="text-3xl font-bold mb-2">Director: {{ $content->director }}</h3>
                    <h3 class="text-3xl font-bold mb-2">Release Year: {{ $content->release_date }}</h3>
                    <h3 class="text-3xl font-bold mb-2">Rating: {{ $content->rating }}</h3>
                    <h3 class="text-3xl font-bold mb-2">Genre: {{ $content->genre->name }}</h3>
                    <p class="text-xl">{{ $content->description }}</p>
                </div>
            </div>
        </div>

    </x-app-layout>


</body>
</html>
