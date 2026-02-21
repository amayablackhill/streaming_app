<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Genre;
use App\Models\User;
use App\Models\VideoAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role as PermissionRole;
use Tests\TestCase;

class VideoAssetEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_get_video_asset_status_json(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create();
        PermissionRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncRoles(['admin']);

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

        $videoAsset = VideoAsset::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'content_id' => $content->id,
            'original_filename' => 'demo.mp4',
            'source_disk' => 'public',
            'source_path' => 'videos/source/demo.mp4',
            'hls_disk' => 'public',
            'hls_master_path' => 'videos/hls/demo/master.m3u8',
            'status' => VideoAsset::STATUS_READY,
        ]);

        Storage::disk('public')->put($videoAsset->hls_master_path, '#EXTM3U');

        $response = $this->actingAs($admin)->getJson("/admin/video-assets/{$videoAsset->id}/status");

        $response
            ->assertOk()
            ->assertJsonPath('id', $videoAsset->id)
            ->assertJsonPath('status', VideoAsset::STATUS_READY)
            ->assertJsonStructure(['hls_url', 'processed_at', 'failed_at']);
    }
}
