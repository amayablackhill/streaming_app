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
        if ($videoAsset->hls_master_path) {
            $hlsUrl = Storage::disk($videoAsset->hls_disk)->url($videoAsset->hls_master_path);
        }

        return view('video-assets.show', [
            'videoAsset' => $videoAsset,
            'hlsUrl' => $hlsUrl,
        ]);
    }

    public function status(VideoAsset $videoAsset): JsonResponse
    {
        $masterUrl = $videoAsset->hls_master_path
            ? Storage::disk($videoAsset->hls_disk)->url($videoAsset->hls_master_path)
            : null;

        return response()->json([
            'id' => $videoAsset->id,
            'status' => $videoAsset->status,
            'error_message' => $videoAsset->error_message,
            'hls_url' => $masterUrl,
            'thumbnails_path' => $videoAsset->thumbnails_path,
            'processed_at' => optional($videoAsset->processed_at)->toIso8601String(),
            'failed_at' => optional($videoAsset->failed_at)->toIso8601String(),
        ]);
    }
}
