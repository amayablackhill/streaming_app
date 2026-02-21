<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Asset Playback</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>
</head>
<body>
<x-app-layout>
    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h1 class="text-2xl font-semibold mb-4">Demo HLS Playback</h1>
                <p class="mb-2"><strong>Asset ID:</strong> {{ $videoAsset->id }}</p>
                <p class="mb-2"><strong>Status:</strong> {{ $videoAsset->status }}</p>
                @if($videoAsset->error_message)
                    <p class="mb-4 text-red-600"><strong>Error:</strong> {{ $videoAsset->error_message }}</p>
                @endif

                @if($hlsUrl && $videoAsset->status === \App\Models\VideoAsset::STATUS_READY)
                    <video id="video-player" controls class="w-full rounded border" style="max-height: 70vh;"></video>
                    <p class="mt-3 text-sm text-gray-600">Master playlist: <a href="{{ $hlsUrl }}" target="_blank" class="text-blue-600 underline">{{ $hlsUrl }}</a></p>
                    <script>
                        (function () {
                            const video = document.getElementById('video-player');
                            const src = @json($hlsUrl);
                            if (window.Hls && Hls.isSupported()) {
                                const hls = new Hls();
                                hls.loadSource(src);
                                hls.attachMedia(video);
                            } else {
                                video.src = src;
                            }
                        })();
                    </script>
                @else
                    <p class="text-gray-700">Asset not ready yet. Refresh this page or check status endpoint.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
</body>
</html>
