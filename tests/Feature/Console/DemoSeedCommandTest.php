<?php

namespace Tests\Feature\Console;

use App\Models\Content;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeedCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_is_reproducible_without_tmdb_token(): void
    {
        config(['services.tmdb.token' => null]);

        $this->artisan('app:demo-seed')
            ->expectsOutputToContain('Baseline catalog seeded.')
            ->assertExitCode(0);

        $this->assertSame(8, Content::query()->where('type', 'film')->count());
        $this->assertSame(2, Content::query()->where('type', 'serie')->count());
        $this->assertSame(7, Genre::query()->count());

        $this->assertDatabaseHas('contents', [
            'title' => 'La Haine',
            'type' => 'film',
        ]);

        $this->assertDatabaseHas('contents', [
            'title' => 'Chernobyl',
            'type' => 'serie',
        ]);
    }

    public function test_demo_seed_append_mode_remains_idempotent(): void
    {
        $this->artisan('app:demo-seed')->assertExitCode(0);

        $baselineCount = Content::query()->count();

        $this->artisan('app:demo-seed --append')
            ->expectsOutputToContain('Baseline catalog seeded.')
            ->assertExitCode(0);

        $this->assertSame($baselineCount, Content::query()->count());
        $this->assertSame(8, Content::query()->where('type', 'film')->count());
        $this->assertSame(2, Content::query()->where('type', 'serie')->count());
    }
}

