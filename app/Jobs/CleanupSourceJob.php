<?php

namespace App\Jobs;

use App\Models\VideoAsset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

        if (Storage::disk($videoAsset->source_disk)->exists($videoAsset->source_path)) {
            Storage::disk($videoAsset->source_disk)->delete($videoAsset->source_path);
        }
    }
}
