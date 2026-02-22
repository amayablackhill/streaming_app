<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1"></script>

    <div
        x-data="videoAssetStatus({
            id: {{ $videoAsset->id }},
            statusUrl: @js(route('video-assets.status', $videoAsset)),
            initialStatus: @js($videoAsset->status),
            initialError: @js($videoAsset->error_message),
            initialHlsUrl: @js($hlsUrl),
            initialThumbUrl: @js($thumbnailUrl),
        })"
        x-init="init()"
        class="py-10"
    >
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-lg">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl font-semibold text-slate-100">Demo HLS Playback</h1>
                    <span
                        class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold uppercase tracking-wide"
                        :class="{
                            'border-emerald-500/30 bg-emerald-500/10 text-emerald-300': status === 'ready',
                            'border-amber-500/30 bg-amber-500/10 text-amber-300': status === 'processing',
                            'border-slate-500/40 bg-slate-500/10 text-slate-300': status === 'pending',
                            'border-rose-500/30 bg-rose-500/10 text-rose-300': status === 'failed'
                        }"
                        x-text="status"
                    ></span>
                </div>

                <div class="grid gap-2 text-sm text-slate-300">
                    <p><span class="font-semibold text-slate-200">Asset ID:</span> {{ $videoAsset->id }}</p>
                    <p x-show="lastCheckedAt">
                        <span class="font-semibold text-slate-200">Last check:</span>
                        <span x-text="lastCheckedAt"></span>
                    </p>
                </div>

                <div class="mt-4" x-show="thumbnailUrl">
                    <p class="mb-2 text-sm font-semibold text-slate-200">Generated Thumbnail</p>
                    <img :src="thumbnailUrl" alt="Generated thumbnail" class="w-full max-w-sm rounded-md border border-slate-700" />
                </div>

                <template x-if="errorMessage">
                    <div class="mt-4 rounded-lg border border-rose-500/30 bg-rose-500/10 p-3 text-sm text-rose-200">
                        <strong class="font-semibold">Pipeline error:</strong>
                        <span x-text="errorMessage"></span>
                    </div>
                </template>

                <div class="mt-5" x-show="status === 'ready' && hlsUrl">
                    <video id="video-player" controls class="w-full rounded-lg border border-slate-700 bg-black" style="max-height: 70vh;"></video>
                    <p class="mt-3 text-sm text-slate-300">
                        Master playlist:
                        <a :href="hlsUrl" target="_blank" rel="noopener" class="text-red-400 underline hover:text-red-300" x-text="hlsUrl"></a>
                    </p>
                </div>

                <div class="mt-5 rounded-lg border border-slate-700 bg-slate-950 p-4 text-sm text-slate-300" x-show="status !== 'ready'">
                    <p x-show="status === 'processing'">Video is being processed in queue. This page auto-refreshes status every 3 seconds.</p>
                    <p x-show="status === 'pending'">Video is queued and waiting for a worker.</p>
                    <p x-show="status === 'failed' && !errorMessage">Video processing failed. Check logs for details.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function videoAssetStatus(config) {
            return {
                id: config.id,
                statusUrl: config.statusUrl,
                status: config.initialStatus,
                errorMessage: config.initialError,
                hlsUrl: config.initialHlsUrl,
                thumbnailUrl: config.initialThumbUrl,
                timer: null,
                hlsInstance: null,
                lastCheckedAt: null,
                init() {
                    this.attachPlayer();
                    this.pollStatus();
                },
                pollStatus() {
                    this.fetchStatus();
                    this.timer = setInterval(() => this.fetchStatus(), 3000);
                },
                async fetchStatus() {
                    try {
                        const response = await fetch(this.statusUrl, {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });
                        if (!response.ok) {
                            return;
                        }

                        const data = await response.json();
                        this.status = data.status;
                        this.errorMessage = data.error_message;
                        this.hlsUrl = data.hls_url;
                        this.thumbnailUrl = data.thumbnails_url;
                        this.lastCheckedAt = new Date().toLocaleTimeString();

                        if (this.status === 'ready') {
                            this.attachPlayer();
                            clearInterval(this.timer);
                        }
                    } catch (error) {
                        console.error('Unable to fetch status', error);
                    }
                },
                attachPlayer() {
                    if (this.status !== 'ready' || !this.hlsUrl) {
                        return;
                    }
                    const video = document.getElementById('video-player');
                    if (!video) {
                        return;
                    }
                    if (this.hlsInstance) {
                        this.hlsInstance.destroy();
                        this.hlsInstance = null;
                    }
                    if (window.Hls && window.Hls.isSupported()) {
                        this.hlsInstance = new window.Hls();
                        this.hlsInstance.loadSource(this.hlsUrl);
                        this.hlsInstance.attachMedia(video);
                        return;
                    }
                    video.src = this.hlsUrl;
                }
            };
        }
    </script>
</x-app-layout>
