<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Season;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role as PermissionRole;
use Tests\TestCase;

class AdminUiAndSeasonProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_movie_detail_shows_admin_controls_only_for_admin_role(): void
    {
        [$admin, $member] = $this->seedUsersWithRoles();
        $genre = Genre::create(['name' => 'Drama']);

        $movie = Content::create([
            'title' => 'Admin Visibility Test',
            'genre_id' => $genre->id,
            'release_date' => '2024-01-01',
            'duration' => 95,
            'rating' => 80,
            'description' => 'Test movie',
            'type' => 'film',
            'director' => 'Tester',
        ]);

        $this->actingAs($admin)
            ->get('/movies/'.$movie->id)
            ->assertOk()
            ->assertSeeText('Admin controls')
            ->assertSeeText('Edit movie');

        $this->actingAs($member)
            ->get('/movies/'.$movie->id)
            ->assertOk()
            ->assertDontSeeText('Admin controls');
    }

    public function test_non_admin_user_cannot_mutate_seasons_and_episodes(): void
    {
        [, $member] = $this->seedUsersWithRoles();
        $genre = Genre::create(['name' => 'Thriller']);

        $series = Content::create([
            'title' => 'Series Security Test',
            'genre_id' => $genre->id,
            'release_date' => '2024-03-01',
            'duration' => 50,
            'rating' => 70,
            'description' => 'Test series',
            'type' => 'serie',
            'director' => 'Tester',
        ]);

        $season = Season::create([
            'serie_id' => $series->id,
            'season_number' => 1,
            'release_date' => '2024-03-02',
        ]);

        $episode = Episode::create([
            'season_id' => $season->id,
            'episode_number' => 1,
            'title' => 'Pilot',
            'duration' => 48,
            'release_date' => '2024-03-03',
        ]);

        $this->actingAs($member)
            ->post('/admin/series/'.$series->id.'/seasons', [
                'season_number' => 2,
                'release_date' => '2024-03-04',
            ])->assertForbidden();

        $this->actingAs($member)
            ->post('/admin/seasons/'.$season->id.'/episodes', [
                'episode_number' => 2,
                'title' => 'Episode 2',
                'duration' => 45,
                'release_date' => '2024-03-10',
            ])->assertForbidden();

        $this->actingAs($member)
            ->post('/admin/seasons/'.$season->id.'/episodes/'.$episode->id.'/edit', [
                'episode_number' => 1,
                'title' => 'Pilot updated',
                'duration' => 49,
                'release_date' => '2024-03-03',
            ])->assertForbidden();

        $this->actingAs($member)
            ->delete('/admin/seasons/'.$season->id.'/episodes/'.$episode->id)
            ->assertForbidden();

        $this->actingAs($member)
            ->delete('/admin/deleteSeason/'.$season->id)
            ->assertForbidden();

        $this->assertDatabaseHas('seasons', [
            'id' => $season->id,
            'season_number' => 1,
        ]);

        $this->assertDatabaseHas('episodes', [
            'id' => $episode->id,
            'title' => 'Pilot',
        ]);
    }

    /**
     * @return array{0: User, 1: User}
     */
    private function seedUsersWithRoles(): array
    {
        PermissionRole::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        PermissionRole::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);

        $member = User::factory()->create();
        $member->syncRoles(['user']);

        return [$admin, $member];
    }
}

