<?php

namespace Tests\Feature;

use App\Jobs\CleanupSourceJob;
use App\Models\Content;
use App\Models\Genre;
use App\Models\VideoAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CleanupSourceJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_deletes_source_when_asset_is_ready_and_master_exists(): void
    {
        Storage::fake('public');
        $videoAsset = $this->createVideoAsset(VideoAsset::STATUS_READY);

        Storage::disk('public')->put($videoAsset->source_path, 'source');
        Storage::disk('public')->put($videoAsset->hls_master_path, '#EXTM3U');

        (new CleanupSourceJob($videoAsset->id))->handle();

        Storage::disk('public')->assertMissing($videoAsset->source_path);
    }

    public function test_cleanup_keeps_source_when_asset_failed(): void
    {
        Storage::fake('public');
        $videoAsset = $this->createVideoAsset(VideoAsset::STATUS_FAILED);

        Storage::disk('public')->put($videoAsset->source_path, 'source');
        Storage::disk('public')->put($videoAsset->hls_master_path, '#EXTM3U');

        (new CleanupSourceJob($videoAsset->id))->handle();

        Storage::disk('public')->assertExists($videoAsset->source_path);
    }

    private function createVideoAsset(string $status): VideoAsset
    {
        $genre = Genre::create(['name' => 'Action']);
        $content = Content::create([
            'title' => 'Demo',
            'description' => 'Demo',
            'release_date' => '2026-01-01',
            'director' => 'Demo',
            'genre_id' => $genre->id,
            'rating' => 80,
            'duration' => 1,
            'type' => 'film',
            'picture' => null,
            'video' => 'demo.mp4',
        ]);

        return VideoAsset::create([
            'uuid' => (string) Str::uuid(),
            'content_id' => $content->id,
            'original_filename' => 'demo.mp4',
            'source_disk' => 'public',
            'source_path' => 'videos/source/demo.mp4',
            'hls_disk' => 'public',
            'hls_master_path' => 'videos/hls/demo/master.m3u8',
            'status' => $status,
        ]);
    }
}
