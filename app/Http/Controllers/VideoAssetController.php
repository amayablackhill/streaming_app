<?php

namespace App\Http\Controllers;

use App\Models\VideoAsset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class VideoAssetController extends Controller
{
    public function show(VideoAsset $videoAsset): View
    {
        $hlsUrl = null;
        $thumbnailUrl = null;
        if ($videoAsset->hls_master_path) {
            $hlsUrl = $this->resolvePublicUrl($videoAsset->hls_disk, $videoAsset->hls_master_path);
        }
        if ($videoAsset->thumbnails_path) {
            $thumbnailUrl = $this->resolvePublicUrl($videoAsset->hls_disk, $videoAsset->thumbnails_path);
        }

        return view('video-assets.show', [
            'videoAsset' => $videoAsset,
            'hlsUrl' => $hlsUrl,
            'thumbnailUrl' => $thumbnailUrl,
        ]);
    }

    public function status(VideoAsset $videoAsset): JsonResponse
    {
        $masterUrl = $videoAsset->hls_master_path
            ? $this->resolvePublicUrl($videoAsset->hls_disk, $videoAsset->hls_master_path)
            : null;
        $thumbnailUrl = $videoAsset->thumbnails_path
            ? $this->resolvePublicUrl($videoAsset->hls_disk, $videoAsset->thumbnails_path)
            : null;

        return response()->json([
            'id' => $videoAsset->id,
            'status' => $videoAsset->status,
            'error_message' => $videoAsset->error_message,
            'hls_url' => $masterUrl,
            'thumbnails_path' => $videoAsset->thumbnails_path,
            'thumbnails_url' => $thumbnailUrl,
            'processed_at' => optional($videoAsset->processed_at)->toIso8601String(),
            'failed_at' => optional($videoAsset->failed_at)->toIso8601String(),
        ]);
    }

    private function resolvePublicUrl(string $disk, string $path): string
    {
        if ($disk === 'public') {
            return '/storage/'.ltrim($path, '/');
        }

        return Storage::disk($disk)->url($path);
    }
}
