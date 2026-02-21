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

class ProbeVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const MAX_DEMO_DURATION_SECONDS = 20.0;

    public int $tries = 3;
    public int $timeout = 300;

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
            $sourcePath = Storage::disk($videoAsset->source_disk)->path($videoAsset->source_path);
        } catch (Throwable $exception) {
            $this->markFailed($videoAsset, 'Source file path cannot be resolved: '.$exception->getMessage());
            return;
        }

        $ffprobePath = env('FFPROBE_PATH', 'ffprobe');
        $process = new Process([
            $ffprobePath,
            '-v', 'quiet',
            '-print_format', 'json',
            '-show_format',
            '-show_streams',
            $sourcePath,
        ]);

        try {
            $process->run();
        } catch (Throwable $exception) {
            $this->markFailed($videoAsset, 'ffprobe process crashed: '.$exception->getMessage());
            return;
        }

        if (!$process->isSuccessful()) {
            $this->markFailed($videoAsset, $this->buildProcessErrorMessage($process, 'ffprobe execution failed'));
            return;
        }

        $probeData = json_decode($process->getOutput(), true);

        if (!is_array($probeData)) {
            $this->markFailed($videoAsset, 'ffprobe returned invalid JSON output.');
            return;
        }

        $videoStream = collect($probeData['streams'] ?? [])->first(
            fn (array $stream): bool => ($stream['codec_type'] ?? null) === 'video'
        );

        $duration = isset($probeData['format']['duration']) ? (float) $probeData['format']['duration'] : null;
        if ($duration !== null && $duration > self::MAX_DEMO_DURATION_SECONDS) {
            $this->markFailed(
                $videoAsset,
                sprintf(
                    'Demo clip duration %.2fs exceeds max allowed %.2fs.',
                    $duration,
                    self::MAX_DEMO_DURATION_SECONDS
                )
            );
            return;
        }

        $videoAsset->update([
            'duration_seconds' => $duration,
            'width' => $videoStream['width'] ?? null,
            'height' => $videoStream['height'] ?? null,
            'video_bitrate' => isset($videoStream['bit_rate'])
                ? (int) $videoStream['bit_rate']
                : (isset($probeData['format']['bit_rate']) ? (int) $probeData['format']['bit_rate'] : null),
            'meta' => $probeData,
            'status' => VideoAsset::STATUS_PROCESSING,
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
        Log::error('ProbeVideoJob failed', [
            'video_asset_id' => $videoAsset->id,
            'message' => $message,
            'source_disk' => $videoAsset->source_disk,
            'source_path' => $videoAsset->source_path,
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
