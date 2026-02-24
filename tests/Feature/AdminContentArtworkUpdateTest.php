<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Genre;
use App\Models\User;
use App\Services\Tmdb\TmdbClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role as PermissionRole;
use Tests\TestCase;

class AdminContentArtworkUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reset_alternate_artwork_to_tmdb_values(): void
    {
        $admin = $this->createAdminUser();
        $genre = Genre::create(['name' => 'Sci-Fi']);

        $content = Content::create([
            'title' => 'Artwork Reset Test',
            'genre_id' => $genre->id,
            'release_date' => '2024-01-01',
            'duration' => 120,
            'rating' => 88,
            'description' => 'Test description',
            'type' => 'film',
            'director' => 'Tester',
            'tmdb_id' => 10,
            'tmdb_type' => 'movie',
            'poster_path' => '/old-poster.jpg',
            'backdrop_path' => '/old-backdrop.jpg',
        ]);

        $this->mock(TmdbClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getDetails')
                ->once()
                ->with('movie', 10)
                ->andReturn([
                    'poster_path' => '/new-poster.jpg',
                    'backdrop_path' => '/new-backdrop.jpg',
                ]);
        });

        $this->actingAs($admin)
            ->put(route('content.update', $content->id), [
                'type' => 'film',
                'title' => 'Artwork Reset Test',
                'description' => 'Test description',
                'release_date' => '2024-01-01',
                'duration' => 120,
                'director' => 'Tester',
                'genre_id' => $genre->id,
                'rating' => 88,
                'poster_reset_tmdb' => '1',
                'backdrop_reset_tmdb' => '1',
            ])
            ->assertRedirect(route('movies.table'));

        $content->refresh();

        $this->assertSame('/new-poster.jpg', $content->poster_path);
        $this->assertSame('/new-backdrop.jpg', $content->backdrop_path);
    }

    public function test_admin_can_upload_local_alternate_poster_and_backdrop(): void
    {
        $this->fakePublicDisk();

        $admin = $this->createAdminUser();
        $genre = Genre::create(['name' => 'Drama']);

        $content = Content::create([
            'title' => 'Local Artwork Upload Test',
            'genre_id' => $genre->id,
            'release_date' => '2024-01-01',
            'duration' => 95,
            'rating' => 78,
            'description' => 'Test description',
            'type' => 'film',
            'director' => 'Tester',
        ]);

        $this->mock(TmdbClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('getDetails')->never();
        });

        $this->actingAs($admin)
            ->post(route('content.update', $content->id), [
                '_method' => 'PUT',
                'type' => 'film',
                'title' => 'Local Artwork Upload Test',
                'description' => 'Test description',
                'release_date' => '2024-01-01',
                'duration' => 95,
                'director' => 'Tester',
                'genre_id' => $genre->id,
                'rating' => 78,
                'poster_image' => UploadedFile::fake()->image('poster.jpg'),
                'backdrop_image' => UploadedFile::fake()->image('backdrop.jpg'),
            ])
            ->assertRedirect(route('movies.table'));

        $content->refresh();

        $this->assertNotNull($content->poster_path);
        $this->assertNotNull($content->backdrop_path);
        $this->assertTrue(Str::startsWith($content->poster_path, 'local:'));
        $this->assertTrue(Str::startsWith($content->backdrop_path, 'local:'));

        Storage::disk('public')->assertExists(Str::after($content->poster_path, 'local:'));
        Storage::disk('public')->assertExists(Str::after($content->backdrop_path, 'local:'));
    }

    private function createAdminUser(): User
    {
        PermissionRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        PermissionRole::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);

        return $admin;
    }
}
