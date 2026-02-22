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

class CleanupSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $videoAssetId)
    {
        $this->onQueue('video');
    }

    public function handle(): void
    {
        $videoAsset = VideoAsset::find($this->videoAssetId);
        if (!$videoAsset || !$videoAsset->source_path) {
            return;
        }

        if ($videoAsset->status !== VideoAsset::STATUS_READY || !$videoAsset->hls_master_path) {
            return;
        }

        if (!Storage::disk($videoAsset->hls_disk)->exists($videoAsset->hls_master_path)) {
            Log::warning('CleanupSourceJob skipped: HLS master playlist missing', [
                'video_asset_id' => $videoAsset->id,
                'hls_disk' => $videoAsset->hls_disk,
                'hls_master_path' => $videoAsset->hls_master_path,
            ]);
            return;
        }

        if (!Storage::disk($videoAsset->source_disk)->exists($videoAsset->source_path)) {
            return;
        }

        Storage::disk($videoAsset->source_disk)->delete($videoAsset->source_path);

        Log::info('CleanupSourceJob deleted source file', [
            'video_asset_id' => $videoAsset->id,
            'source_disk' => $videoAsset->source_disk,
            'source_path' => $videoAsset->source_path,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CleanupSourceJob failed', [
            'video_asset_id' => $this->videoAssetId,
            'message' => $exception->getMessage(),
        ]);
    }
}
