<?php

namespace Database\Seeders;

use App\Services\TmdbImportService;
use Illuminate\Database\Seeder;

class TmdbContentSeeder extends Seeder
{
    public function run(): void
    {
        /** @var TmdbImportService $tmdbImportService */
        $tmdbImportService = app(TmdbImportService::class);

        $limit = (int) env('TMDB_SEED_LIMIT', 12);
        $pages = (int) env('TMDB_SEED_PAGES', 1);
        $downloadPosters = (bool) env('TMDB_SEED_DOWNLOAD_POSTERS', true);
        $refreshExisting = (bool) env('TMDB_SEED_REFRESH_EXISTING', false);

        if (!$tmdbImportService->canRun()) {
            $this->command->warn('TMDB credentials are not set. Skipping TmdbContentSeeder.');
            return;
        }

        $stats = $tmdbImportService->importPopularMovies(
            pages: max(1, $pages),
            limit: max(1, $limit),
            downloadPosters: $downloadPosters,
            refreshExisting: $refreshExisting
        );

        $this->command->info(
            sprintf(
                'TMDB seed finished. Processed=%d Created=%d Updated=%d Skipped=%d Errors=%d',
                $stats['processed'],
                $stats['created'],
                $stats['updated'],
                $stats['skipped'],
                $stats['errors']
            )
        );
    }
}
