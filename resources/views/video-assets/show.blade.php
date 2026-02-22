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
            initialProcessedAt: @js(optional($videoAsset->processed_at)->toIso8601String()),
            initialFailedAt: @js(optional($videoAsset->failed_at)->toIso8601String()),
        })"
        x-init="init()"
        class="py-8"
    >
        <div class="mx-auto w-full max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <header class="rounded-md border border-cc-border bg-cc-bg-surface/90 p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.12em] text-cc-text-muted">Video Pipeline</p>
                        <h1 class="mt-1 font-serif text-3xl text-cc-text-primary">Demo Clip Status</h1>
                        <p class="mt-2 text-sm text-cc-text-secondary">
                            Asset #{{ $videoAsset->id }} is monitored in real time while jobs run in queue.
                        </p>
                    </div>
                    <span
                        class="inline-flex items-center rounded-sm border px-3 py-1 text-xs font-medium uppercase tracking-[0.08em]"
                        :class="statusToneClass()"
                        x-text="status"
                    ></span>
                </div>
            </header>

            <div class="grid gap-6 lg:grid-cols-[1.45fr_1fr]">
                <section class="space-y-6">
                    <article class="rounded-md border border-cc-border bg-cc-bg-surface/80 p-5">
                        <h2 class="font-serif text-xl text-cc-text-primary">Pipeline Timeline</h2>
                        <ol class="mt-4 space-y-4">
                            <template x-for="step in timelineSteps" :key="step.key">
                                <li class="relative pl-8">
                                    <span class="absolute left-0 top-1 inline-flex h-4 w-4 rounded-full border" :class="stepDotClass(step.key)"></span>
                                    <span class="absolute left-[7px] top-5 h-[calc(100%-2px)] w-px bg-cc-border" x-show="step.key !== 'failed'"></span>
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium uppercase tracking-[0.08em]" :class="stepTextClass(step.key)" x-text="step.label"></p>
                                        <p class="text-sm text-cc-text-secondary" x-text="step.description"></p>
                                    </div>
                                </li>
                            </template>
                        </ol>

                        <div class="mt-4 rounded-md border border-cc-border bg-cc-bg-primary/60 p-4 text-sm text-cc-text-secondary">
                            <p x-show="status === 'processing'">Worker is transcoding to HLS and generating artifacts.</p>
                            <p x-show="status === 'pending'">Clip is queued and waiting for the video worker.</p>
                            <p x-show="status === 'ready'">Pipeline completed. Playback and file links are now available.</p>
                            <p x-show="status === 'failed' && !errorMessage">Pipeline failed. Review worker logs for detailed ffmpeg output.</p>
                            <p class="mt-2 text-xs text-cc-text-muted" x-show="lastCheckedAt">
                                Last status check: <span x-text="lastCheckedAt"></span>
                            </p>
                        </div>
                    </article>

                    <article class="rounded-md border border-cc-border bg-cc-bg-surface/80 p-5" x-show="status === 'ready' && hlsUrl">
                        <h2 class="font-serif text-xl text-cc-text-primary">HLS Playback</h2>
                        <video id="video-player" controls class="mt-4 w-full rounded-sm border border-cc-border bg-black" style="max-height: 70vh;"></video>
                    </article>

                    <template x-if="errorMessage">
                        <article class="rounded-md border border-[#5C2E2E]/55 bg-[#5C2E2E]/20 p-5 text-sm text-[#E0B6B6]">
                            <p class="font-medium uppercase tracking-[0.08em]">Pipeline Error</p>
                            <p class="mt-2 leading-relaxed" x-text="errorMessage"></p>
                        </article>
                    </template>
                </section>

                <aside class="space-y-6">
                    <article class="rounded-md border border-cc-border bg-cc-bg-surface/80 p-5">
                        <h2 class="font-serif text-xl text-cc-text-primary">Technical Links</h2>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="space-y-1">
                                <dt class="text-cc-text-muted">Master Playlist</dt>
                                <dd class="text-cc-text-primary">
                                    <a
                                        x-show="hlsUrl"
                                        :href="hlsUrl"
                                        target="_blank"
                                        rel="noopener"
                                        class="break-all underline decoration-cc-border underline-offset-4 transition-colors cc-motion-base hover:text-cc-text-secondary"
                                        x-text="hlsUrl"
                                    ></a>
                                    <span x-show="!hlsUrl" class="text-cc-text-muted">Not available yet</span>
                                </dd>
                            </div>
                            <div class="space-y-1">
                                <dt class="text-cc-text-muted">Thumbnail</dt>
                                <dd class="text-cc-text-primary">
                                    <a
                                        x-show="thumbnailUrl"
                                        :href="thumbnailUrl"
                                        target="_blank"
                                        rel="noopener"
                                        class="break-all underline decoration-cc-border underline-offset-4 transition-colors cc-motion-base hover:text-cc-text-secondary"
                                        x-text="thumbnailUrl"
                                    ></a>
                                    <span x-show="!thumbnailUrl" class="text-cc-text-muted">Not generated yet</span>
                                </dd>
                            </div>
                        </dl>
                    </article>

                    <article class="rounded-md border border-cc-border bg-cc-bg-surface/80 p-5">
                        <h2 class="font-serif text-xl text-cc-text-primary">Asset Metadata</h2>
                        <dl class="mt-4 grid gap-3 text-sm">
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-cc-text-muted">Asset ID</dt>
                                <dd class="font-mono text-xs text-cc-text-secondary">{{ $videoAsset->id }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-cc-text-muted">Content ID</dt>
                                <dd class="font-mono text-xs text-cc-text-secondary">{{ $videoAsset->content_id ?? 'N/A' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-cc-text-muted">Source</dt>
                                <dd class="font-mono text-xs text-cc-text-secondary">{{ $videoAsset->source_disk }}/{{ $videoAsset->source_path }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-cc-text-muted">HLS Target</dt>
                                <dd class="font-mono text-xs text-cc-text-secondary">{{ $videoAsset->hls_disk }}/{{ $videoAsset->hls_master_path ?? 'pending' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-cc-text-muted">Duration</dt>
                                <dd class="font-mono text-xs text-cc-text-secondary">{{ $videoAsset->duration_seconds ? $videoAsset->duration_seconds . 's' : 'pending' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-cc-text-muted">Resolution</dt>
                                <dd class="font-mono text-xs text-cc-text-secondary">
                                    {{ ($videoAsset->width && $videoAsset->height) ? $videoAsset->width . 'x' . $videoAsset->height : 'pending' }}
                                </dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-cc-text-muted">Processed At</dt>
                                <dd class="font-mono text-xs text-cc-text-secondary" x-text="formatTimestamp(processedAt)"></dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-cc-text-muted">Failed At</dt>
                                <dd class="font-mono text-xs text-cc-text-secondary" x-text="formatTimestamp(failedAt)"></dd>
                            </div>
                        </dl>
                    </article>

                    <article class="rounded-md border border-cc-border bg-cc-bg-surface/80 p-5" x-show="thumbnailUrl">
                        <h2 class="font-serif text-xl text-cc-text-primary">Generated Thumbnail</h2>
                        <img :src="thumbnailUrl" alt="Generated thumbnail" class="mt-4 w-full rounded-sm border border-cc-border object-cover" />
                    </article>
                </aside>
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
                processedAt: config.initialProcessedAt,
                failedAt: config.initialFailedAt,
                timelineSteps: [
                    {
                        key: 'pending',
                        label: 'Queued',
                        description: 'Asset accepted and waiting for a queue worker.',
                    },
                    {
                        key: 'processing',
                        label: 'Processing',
                        description: 'Running probe, HLS transcode and thumbnails.',
                    },
                    {
                        key: 'ready',
                        label: 'Ready',
                        description: 'Master playlist and segments are available.',
                    },
                    {
                        key: 'failed',
                        label: 'Failed',
                        description: 'Pipeline stopped due to a controlled processing error.',
                    },
                ],
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
                            headers: { Accept: 'application/json' },
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
                        this.processedAt = data.processed_at;
                        this.failedAt = data.failed_at;
                        this.lastCheckedAt = new Date().toLocaleTimeString();

                        if (this.status === 'ready') {
                            this.attachPlayer();
                            clearInterval(this.timer);
                        }
                    } catch (error) {
                        console.error('Unable to fetch status', error);
                    }
                },
                statusToneClass() {
                    if (this.status === 'ready') {
                        return 'border-[#3E4A3F]/45 bg-[#3E4A3F]/25 text-[#C4D1C5]';
                    }
                    if (this.status === 'processing') {
                        return 'border-[#6B3F2B]/40 bg-[#6B3F2B]/25 text-[#D2AE9A]';
                    }
                    if (this.status === 'failed') {
                        return 'border-[#5C2E2E]/45 bg-[#5C2E2E]/25 text-[#E0B6B6]';
                    }

                    return 'border-cc-border bg-cc-bg-elevated text-cc-text-secondary';
                },
                isStepReached(stepKey) {
                    if (this.status === 'failed') {
                        return ['pending', 'processing', 'failed'].includes(stepKey);
                    }

                    if (this.status === 'ready') {
                        return ['pending', 'processing', 'ready'].includes(stepKey);
                    }

                    if (this.status === 'processing') {
                        return ['pending', 'processing'].includes(stepKey);
                    }

                    return stepKey === 'pending';
                },
                stepDotClass(stepKey) {
                    if (this.status === 'failed' && stepKey === 'failed') {
                        return 'border-[#5C2E2E] bg-[#5C2E2E]';
                    }
                    if (this.status === 'ready' && stepKey === 'ready') {
                        return 'border-[#3E4A3F] bg-[#3E4A3F]';
                    }
                    if (this.status === 'processing' && stepKey === 'processing') {
                        return 'border-[#6B3F2B] bg-[#6B3F2B]';
                    }
                    if (this.isStepReached(stepKey)) {
                        return 'border-cc-text-secondary bg-cc-text-secondary';
                    }

                    return 'border-cc-border bg-cc-bg-primary';
                },
                stepTextClass(stepKey) {
                    return this.isStepReached(stepKey) ? 'text-cc-text-primary' : 'text-cc-text-muted';
                },
                formatTimestamp(value) {
                    if (!value) {
                        return 'N/A';
                    }

                    return new Date(value).toLocaleString();
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
                },
            };
        }
    </script>
</x-app-layout>
