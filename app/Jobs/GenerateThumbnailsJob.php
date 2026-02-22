<?php

namespace App\Jobs;

use App\Models\VideoAsset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;

class GenerateThumbnailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(public int $videoAssetId)
    {
        $this->onQueue('video');
    }

    public function handle(): void
    {
        $videoAsset = VideoAsset::find($this->videoAssetId);
        if (!$videoAsset || $videoAsset->status !== VideoAsset::STATUS_READY) {
            return;
        }

        if ($videoAsset->thumbnails_path && Storage::disk($videoAsset->hls_disk)->exists($videoAsset->thumbnails_path)) {
            return;
        }

        try {
            $inputPath = Storage::disk($videoAsset->source_disk)->path($videoAsset->source_path);
        } catch (Throwable $exception) {
            Log::warning('GenerateThumbnailsJob skipped: source path unavailable', [
                'video_asset_id' => $this->videoAssetId,
                'message' => $exception->getMessage(),
            ]);
            return;
        }

        $thumbPath = 'videos/hls/'.$videoAsset->uuid.'/thumbs/thumb_001.jpg';
        Storage::disk($videoAsset->hls_disk)->makeDirectory(dirname($thumbPath));

        $seekSeconds = $this->resolveSeekSecond($videoAsset);
        $ffmpegPath = env('FFMPEG_PATH', 'ffmpeg');
        $process = new Process([
            $ffmpegPath,
            '-y',
            '-ss', (string) $seekSeconds,
            '-i', $inputPath,
            '-frames:v', '1',
            '-vf', 'scale=-2:360',
            '-q:v', '2',
            Storage::disk($videoAsset->hls_disk)->path($thumbPath),
        ]);

        try {
            $process->run();
        } catch (Throwable $exception) {
            Log::warning('GenerateThumbnailsJob crashed', [
                'video_asset_id' => $this->videoAssetId,
                'message' => $exception->getMessage(),
            ]);
            return;
        }

        if ($process->isSuccessful() && Storage::disk($videoAsset->hls_disk)->exists($thumbPath)) {
            $videoAsset->update(['thumbnails_path' => $thumbPath]);
            return;
        }

        Log::warning('GenerateThumbnailsJob failed', [
            'video_asset_id' => $this->videoAssetId,
            'exit_code' => $process->getExitCode(),
            'command' => $process->getCommandLine(),
            'stderr' => mb_substr(trim($process->getErrorOutput()), 0, 1000),
        ]);
    }

    private function resolveSeekSecond(VideoAsset $videoAsset): float
    {
        $duration = (float) ($videoAsset->duration_seconds ?? 0);
        if ($duration <= 0.0) {
            return 1.0;
        }

        $half = $duration / 2;

        return max(0.2, min(1.0, $half));
    }
}
