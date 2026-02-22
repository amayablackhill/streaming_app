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

    public function __construct(public int $videoAssetId)
    {
        $this->onQueue('video');
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
        $masterPath = $baseOutputDir.'/master.m3u8';
        $renditions = [
            ['variant' => 'v0', 'height' => 720, 'bandwidth' => 2800000, 'maxrate' => '2996k', 'bufsize' => '4200k', 'audio' => '128k', 'resolution' => '1280x720', 'codec' => 'avc1.64001f,mp4a.40.2'],
            ['variant' => 'v1', 'height' => 480, 'bandwidth' => 1400000, 'maxrate' => '1498k', 'bufsize' => '2100k', 'audio' => '96k', 'resolution' => '854x480', 'codec' => 'avc1.64001e,mp4a.40.2'],
            ['variant' => 'v2', 'height' => 360, 'bandwidth' => 800000, 'maxrate' => '856k', 'bufsize' => '1200k', 'audio' => '96k', 'resolution' => '640x360', 'codec' => 'avc1.64001e,mp4a.40.2'],
        ];

        $ffmpegPath = env('FFMPEG_PATH', 'ffmpeg');
        foreach ($renditions as $rendition) {
            $variantDir = $baseOutputDir.'/'.$rendition['variant'];
            $playlistPath = $variantDir.'/index.m3u8';
            $segmentPattern = $variantDir.'/seg_%05d.ts';

            Storage::disk($videoAsset->hls_disk)->makeDirectory($variantDir);

            $process = new Process([
                $ffmpegPath,
                '-y',
                '-i', $inputPath,
                '-map', '0:v:0',
                '-map', '0:a?',
                '-vf', 'scale=-2:'.$rendition['height'],
                '-c:v', 'libx264',
                '-preset', 'veryfast',
                '-profile:v', 'main',
                '-crf', '23',
                '-maxrate', $rendition['maxrate'],
                '-bufsize', $rendition['bufsize'],
                '-c:a', 'aac',
                '-ar', '48000',
                '-ac', '2',
                '-b:a', $rendition['audio'],
                '-f', 'hls',
                '-hls_time', '6',
                '-hls_playlist_type', 'vod',
                '-hls_flags', 'independent_segments',
                '-hls_segment_filename', Storage::disk($videoAsset->hls_disk)->path($segmentPattern),
                Storage::disk($videoAsset->hls_disk)->path($playlistPath),
            ]);

            try {
                $process->run();
            } catch (Throwable $exception) {
                $this->markFailed($videoAsset, 'ffmpeg process crashed: '.$exception->getMessage());
                return;
            }

            if (!$process->isSuccessful()) {
                $this->markFailed($videoAsset, $this->buildProcessErrorMessage($process, 'ffmpeg HLS transcoding failed'));
                return;
            }

            if (!Storage::disk($videoAsset->hls_disk)->exists($playlistPath)) {
                $this->markFailed($videoAsset, 'HLS transcoding finished but variant playlist was not generated: '.$playlistPath);
                return;
            }
        }

        $masterLines = ['#EXTM3U', '#EXT-X-VERSION:3'];
        foreach ($renditions as $rendition) {
            $masterLines[] = '#EXT-X-STREAM-INF:BANDWIDTH='.$rendition['bandwidth'].',RESOLUTION='.$rendition['resolution'].',CODECS="'.$rendition['codec'].'"';
            $masterLines[] = $rendition['variant'].'/index.m3u8';
        }
        $masterLines[] = '';
        Storage::disk($videoAsset->hls_disk)->put($masterPath, implode("\n", $masterLines));

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
            'source_disk' => $videoAsset->source_disk,
            'source_path' => $videoAsset->source_path,
            'hls_disk' => $videoAsset->hls_disk,
            'hls_master_path' => $videoAsset->hls_master_path,
        ]);

        $videoAsset->update([
            'status' => VideoAsset::STATUS_FAILED,
            'error_message' => mb_substr($message, 0, 5000),
            'failed_at' => Carbon::now(),
            'processed_at' => null,
        ]);
    }

    private function buildProcessErrorMessage(Process $process, string $prefix): string
    {
        $exitCode = $process->getExitCode();
        $stderr = trim($process->getErrorOutput());
        $stdout = trim($process->getOutput());
        $details = $stderr !== '' ? $stderr : ($stdout !== '' ? $stdout : 'no output');

        return sprintf(
            '%s (exit_code=%s, cmd="%s"): %s',
            $prefix,
            $exitCode === null ? 'null' : (string) $exitCode,
            $process->getCommandLine(),
            mb_substr($details, 0, 1500)
        );
    }
}
