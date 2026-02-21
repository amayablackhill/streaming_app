<?php

namespace App\Jobs;

use App\Models\VideoAsset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Throwable;

class TranscodeToHlsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 3600;
    public string $queue = 'video';

    public function __construct(public int $videoAssetId)
    {
    }

    public function handle(): void
    {
        $videoAsset = VideoAsset::find($this->videoAssetId);

        if (!$videoAsset) {
            return;
        }

        $videoAsset->update([
            'status' => VideoAsset::STATUS_PROCESSING,
            'error_message' => null,
            'failed_at' => null,
            'processed_at' => null,
        ]);

        try {
            $inputPath = Storage::disk($videoAsset->source_disk)->path($videoAsset->source_path);
        } catch (Throwable $exception) {
            $this->markFailed($videoAsset, 'Source file path cannot be resolved: '.$exception->getMessage());
            return;
        }

        $baseOutputDir = 'videos/hls/'.$videoAsset->uuid;
        $variantDir = $baseOutputDir.'/v0';
        $segmentPattern = $variantDir.'/seg_%05d.ts';
        $playlistPath = $variantDir.'/index.m3u8';
        $masterPath = $baseOutputDir.'/master.m3u8';

        Storage::disk($videoAsset->hls_disk)->makeDirectory($variantDir);

        $ffmpegPath = env('FFMPEG_PATH', 'ffmpeg');
        $process = new Process([
            $ffmpegPath,
            '-y',
            '-i', $inputPath,
            '-vf', 'scale=-2:720',
            '-c:v', 'libx264',
            '-preset', 'veryfast',
            '-profile:v', 'main',
            '-crf', '23',
            '-c:a', 'aac',
            '-ar', '48000',
            '-ac', '2',
            '-b:a', '128k',
            '-f', 'hls',
            '-hls_time', '6',
            '-hls_playlist_type', 'vod',
            '-hls_flags', 'independent_segments',
            '-hls_segment_filename', Storage::disk($videoAsset->hls_disk)->path($segmentPattern),
            '-master_pl_name', 'master.m3u8',
            '-var_stream_map', 'v:0,a:0',
            Storage::disk($videoAsset->hls_disk)->path($playlistPath),
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            $this->markFailed($videoAsset, trim($process->getErrorOutput()) ?: 'ffmpeg HLS transcoding failed.');
            return;
        }

        if (!Storage::disk($videoAsset->hls_disk)->exists($masterPath)) {
            $this->markFailed($videoAsset, 'HLS transcoding finished but master playlist was not generated.');
            return;
        }

        $videoAsset->update([
            'hls_master_path' => $masterPath,
            'status' => VideoAsset::STATUS_READY,
            'error_message' => null,
            'failed_at' => null,
            'processed_at' => Carbon::now(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $videoAsset = VideoAsset::find($this->videoAssetId);
        if (!$videoAsset) {
            return;
        }

        $this->markFailed($videoAsset, $exception->getMessage());
    }

    private function markFailed(VideoAsset $videoAsset, string $message): void
    {
        Log::error('TranscodeToHlsJob failed', [
            'video_asset_id' => $videoAsset->id,
            'message' => $message,
        ]);

        $videoAsset->update([
            'status' => VideoAsset::STATUS_FAILED,
            'error_message' => mb_substr($message, 0, 5000),
            'failed_at' => Carbon::now(),
            'processed_at' => null,
        ]);
    }
}
