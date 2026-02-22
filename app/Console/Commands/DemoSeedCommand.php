<?php

namespace App\Console\Commands;

use App\Models\Content;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Season;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoSeedCommand extends Command
{
    protected $signature = 'app:demo-seed
        {--append : Keep existing catalog rows and only upsert demo baseline}';

    protected $description = 'Seed a deterministic demo catalog for portfolio usage (local-first, TMDB optional)';

    public function handle(): int
    {
        $append = (bool) $this->option('append');

        if (!$append) {
            $this->resetCatalog();
            $this->line('Catalog reset: contents, seasons, episodes and genres cleared.');
        }

        $stats = $this->seedBaselineCatalog();
        $this->info("Baseline catalog seeded. Films={$stats['films']} Series={$stats['series']} Genres={$stats['genres']}.");

        $this->info('Demo catalog is ready.');

        return self::SUCCESS;
    }

    private function resetCatalog(): void
    {
        DB::transaction(function (): void {
            Episode::query()->delete();
            Season::query()->delete();
            Content::query()->delete();
            Genre::query()->delete();
        });
    }

    /**
     * @return array{films:int,series:int,genres:int}
     */
    private function seedBaselineCatalog(): array
    {
        $hasFeaturedColumn = Schema::hasColumn('contents', 'is_featured');

        $genreNames = [
            'Action',
            'Adventure',
            'Drama',
            'Sci-Fi',
            'Thriller',
            'Comedy',
            'Crime',
        ];

        $genreByName = collect($genreNames)
            ->mapWithKeys(function (string $name): array {
                $genre = Genre::query()->firstOrCreate(['name' => $name]);
                return [$name => $genre->id];
            });

        $items = [
            [
                'title' => 'La Haine',
                'genre' => 'Drama',
                'release_date' => '1995-05-31',
                'duration' => 98,
                'rating' => 85,
                'description' => 'A day in the life of three young friends in the banlieues of Paris after a night of civil unrest.',
                'director' => 'Mathieu Kassovitz',
                'type' => 'film',
                'video' => 'https://www.youtube.com/watch?v=FKwcXt3JIaU',
                'picture' => null,
                'is_featured' => true,
            ],
            [
                'title' => 'Inception',
                'genre' => 'Sci-Fi',
                'release_date' => '2010-07-16',
                'duration' => 148,
                'rating' => 88,
                'description' => 'A skilled thief enters dreams to steal secrets and is offered a final mission: plant an idea.',
                'director' => 'Christopher Nolan',
                'type' => 'film',
                'video' => 'YoHD9XEInc0',
                'picture' => 'tmdb_1084242.jpg',
                'is_featured' => false,
            ],
            [
                'title' => 'Interstellar',
                'genre' => 'Sci-Fi',
                'release_date' => '2014-11-07',
                'duration' => 169,
                'rating' => 86,
                'description' => 'Explorers travel through a wormhole in space in an attempt to ensure humanity survival.',
                'director' => 'Christopher Nolan',
                'type' => 'film',
                'video' => 'zSWdZVtXT7E',
                'picture' => 'tmdb_1168190.jpg',
                'is_featured' => false,
            ],
            [
                'title' => 'Parasite',
                'genre' => 'Thriller',
                'release_date' => '2019-05-30',
                'duration' => 132,
                'rating' => 86,
                'description' => 'A poor family infiltrates a wealthy household in a darkly comic social thriller.',
                'director' => 'Bong Joon-ho',
                'type' => 'film',
                'video' => '5xH0HfJHsaY',
                'picture' => 'tmdb_1236153.jpg',
                'is_featured' => false,
            ],
            [
                'title' => 'Arrival',
                'genre' => 'Sci-Fi',
                'release_date' => '2016-11-11',
                'duration' => 116,
                'rating' => 79,
                'description' => 'A linguist is recruited by the military to communicate with extraterrestrial visitors.',
                'director' => 'Denis Villeneuve',
                'type' => 'film',
                'video' => 'tFMo3UJ4B4g',
                'picture' => 'tmdb_1242898.jpg',
                'is_featured' => false,
            ],
            [
                'title' => 'Blade Runner 2049',
                'genre' => 'Sci-Fi',
                'release_date' => '2017-10-06',
                'duration' => 164,
                'rating' => 80,
                'description' => 'A young blade runner discovers a secret that could plunge society into chaos.',
                'director' => 'Denis Villeneuve',
                'type' => 'film',
                'video' => 'gCcx85zbxz4',
                'picture' => 'tmdb_1253000.jpg',
                'is_featured' => false,
            ],
            [
                'title' => 'Whiplash',
                'genre' => 'Drama',
                'release_date' => '2014-10-10',
                'duration' => 107,
                'rating' => 85,
                'description' => 'A drummer pushes himself to the limit under the mentorship of a relentless instructor.',
                'director' => 'Damien Chazelle',
                'type' => 'film',
                'video' => '7d_jQycdQGo',
                'picture' => 'tmdb_1272837.jpg',
                'is_featured' => false,
            ],
            [
                'title' => 'The Grand Budapest Hotel',
                'genre' => 'Comedy',
                'release_date' => '2014-03-28',
                'duration' => 99,
                'rating' => 81,
                'description' => 'The adventures of a legendary concierge and a lobby boy at a famous European hotel.',
                'director' => 'Wes Anderson',
                'type' => 'film',
                'video' => '1Fg5iWmQjwk',
                'picture' => 'tmdb_1316092.jpg',
                'is_featured' => false,
            ],
            [
                'title' => 'Chernobyl',
                'genre' => 'Drama',
                'release_date' => '2019-05-06',
                'duration' => 60,
                'rating' => 94,
                'description' => 'A dramatization of the 1986 nuclear disaster and the efforts to contain it.',
                'director' => 'Craig Mazin',
                'type' => 'serie',
                'video' => 's9APLXM9Ei8',
                'picture' => null,
                'is_featured' => false,
            ],
            [
                'title' => 'True Detective',
                'genre' => 'Crime',
                'release_date' => '2014-01-12',
                'duration' => 55,
                'rating' => 89,
                'description' => 'Anthology crime stories focused on detectives confronting dark cases and personal demons.',
                'director' => 'Nic Pizzolatto',
                'type' => 'serie',
                'video' => 'fVQUcaO4AvE',
                'picture' => null,
                'is_featured' => false,
            ],
        ];

        foreach ($items as $item) {
            $genreId = $genreByName->get($item['genre']);
            if (!$genreId) {
                continue;
            }

            Content::query()->updateOrCreate(
                [
                    'title' => $item['title'],
                    'type' => $item['type'],
                    'release_date' => $item['release_date'],
                ],
                array_filter([
                    'genre_id' => $genreId,
                    'duration' => $item['duration'],
                    'rating' => $item['rating'],
                    'description' => $item['description'],
                    'director' => $item['director'],
                    'video' => $item['video'],
                    'picture' => $item['picture'],
                    'is_featured' => $hasFeaturedColumn ? $item['is_featured'] : null,
                ], static fn ($value): bool => $value !== null)
            );
        }

        if ($hasFeaturedColumn) {
            Content::query()
                ->where('title', '!=', 'La Haine')
                ->update(['is_featured' => false]);
        }

        return [
            'films' => Content::query()->where('type', 'film')->count(),
            'series' => Content::query()->where('type', 'serie')->count(),
            'genres' => Genre::query()->count(),
        ];
    }
}
