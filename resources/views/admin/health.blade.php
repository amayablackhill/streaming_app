<x-app-layout>
    <section
        class="cc-stack-6"
        x-data="{
            loading: true,
            error: null,
            health: null,
            videoPipeline: null,
            async load() {
                this.loading = true;
                this.error = null;
                try {
                    const [healthResponse, pipelineResponse] = await Promise.all([
                        fetch(@js(route('admin.health.api')), { headers: { 'Accept': 'application/json' } }),
                        fetch(@js(route('admin.health.video-pipeline')), { headers: { 'Accept': 'application/json' } }),
                    ]);

                    if (!healthResponse.ok) {
                        throw new Error('Health API request failed.');
                    }
                    this.health = await healthResponse.json();

                    if (pipelineResponse.ok) {
                        this.videoPipeline = await pipelineResponse.json();
                    }
                } catch (e) {
                    this.error = 'Unable to load health information. Please try again.';
                } finally {
                    this.loading = false;
                }
            }
        }"
        x-init="load()"
    >
        <header class="cc-stack-2">
            <p class="text-cc-caption uppercase tracking-label text-cc-text-muted">Admin Health</p>
            <h1 class="cc-title-display">System Status</h1>
            <p class="max-w-3xl text-sm leading-editorial text-cc-text-secondary">
                Operational snapshot for application services and video pipeline checks.
            </p>
        </header>

        <x-ui.alert x-show="error" x-cloak tone="error" title="Health check error">
            <span x-text="error"></span>
        </x-ui.alert>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" x-show="!loading" x-cloak>
            <article class="cc-surface cc-stack-2 p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm uppercase tracking-[0.12em] text-cc-text-muted">API Health</h2>
                    <x-ui.badge tone="neutral" x-show="health?.ok">ok</x-ui.badge>
                    <x-ui.badge tone="failed" x-show="health && !health.ok">degraded</x-ui.badge>
                </div>
                <p class="text-sm text-cc-text-secondary">Aggregated status of app, DB, cache, queue, storage and TMDB config.</p>
                <div class="grid grid-cols-2 gap-2 text-xs text-cc-text-secondary">
                    <div>App: <span class="text-cc-text-primary" x-text="health?.app?.ok ? 'ok' : 'fail'"></span></div>
                    <div>DB: <span class="text-cc-text-primary" x-text="health?.db?.ok ? 'ok' : 'fail'"></span></div>
                    <div>Cache: <span class="text-cc-text-primary" x-text="health?.cache?.ok ? 'ok' : 'fail'"></span></div>
                    <div>Queue: <span class="text-cc-text-primary" x-text="health?.queue?.ok ? 'ok' : 'fail'"></span></div>
                    <div>Storage: <span class="text-cc-text-primary" x-text="health?.storage?.ok ? 'ok' : 'fail'"></span></div>
                    <div>TMDB: <span class="text-cc-text-primary" x-text="health?.tmdb?.configured ? 'configured' : 'disabled'"></span></div>
                </div>
            </article>

            <article class="cc-surface cc-stack-2 p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm uppercase tracking-[0.12em] text-cc-text-muted">Video Pipeline</h2>
                    <x-ui.badge tone="neutral" x-show="videoPipeline?.ok">ok</x-ui.badge>
                    <x-ui.badge tone="failed" x-show="videoPipeline && !videoPipeline.ok">degraded</x-ui.badge>
                </div>
                <p class="text-sm text-cc-text-secondary">
                    ffmpeg / ffprobe binaries and writable public storage checks.
                </p>
                <div class="grid grid-cols-2 gap-2 text-xs text-cc-text-secondary">
                    <div>ffmpeg: <span class="text-cc-text-primary" x-text="videoPipeline?.ffmpeg?.ok ? 'ok' : 'fail'"></span></div>
                    <div>ffprobe: <span class="text-cc-text-primary" x-text="videoPipeline?.ffprobe?.ok ? 'ok' : 'fail'"></span></div>
                    <div>DB: <span class="text-cc-text-primary" x-text="videoPipeline?.database?.ok ? 'ok' : 'fail'"></span></div>
                    <div>Storage: <span class="text-cc-text-primary" x-text="videoPipeline?.storage?.writable ? 'ok' : 'fail'"></span></div>
                </div>
                <div>
                    <x-ui.button :href="route('admin.health.video-pipeline')" variant="ghost" size="sm">Open raw video-pipeline JSON</x-ui.button>
                </div>
            </article>

            <article class="cc-surface cc-stack-2 p-4 sm:p-5">
                <h2 class="text-sm uppercase tracking-[0.12em] text-cc-text-muted">Queue Info</h2>
                <p class="text-sm text-cc-text-secondary">Current queue connection and readiness notes.</p>
                <p class="text-sm text-cc-text-primary" x-text="health?.queue?.connection || 'n/a'"></p>
                <p class="text-xs text-cc-text-secondary" x-text="health?.queue?.notes || ''"></p>
            </article>
        </div>

        <div x-show="loading" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3" aria-live="polite" role="status">
            @foreach (range(1, 3) as $placeholder)
                <article class="cc-surface animate-pulse p-4 sm:p-5">
                    <div class="h-3 w-24 bg-cc-bg-elevated"></div>
                    <div class="mt-3 h-2 w-4/5 bg-cc-bg-elevated"></div>
                    <div class="mt-4 h-2 w-3/5 bg-cc-bg-elevated"></div>
                </article>
            @endforeach
        </div>
    </section>
</x-app-layout>
