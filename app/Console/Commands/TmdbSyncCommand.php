<?php

namespace App\Console\Commands;

use App\Services\TmdbImportService;
use Illuminate\Console\Command;

class TmdbSyncCommand extends Command
{
    protected $signature = 'tmdb:sync
        {--pages=1 : Number of TMDB pages to scan}
        {--limit=20 : Max items to import}
        {--download-posters : Download poster files to public storage}
        {--refresh-existing : Force refresh of existing movies}';

    protected $description = 'Sync real movie catalog from TMDB with controlled API calls';

    public function handle(TmdbImportService $tmdbImportService): int
    {
        if (!$tmdbImportService->canRun()) {
            $this->warn('TMDB credentials missing. Set TMDB_API_KEY or TMDB_BEARER_TOKEN.');
            return self::SUCCESS;
        }

        $pages = (int) $this->option('pages');
        $limit = (int) $this->option('limit');
        $downloadPosters = (bool) $this->option('download-posters');
        $refreshExisting = (bool) $this->option('refresh-existing');

        $stats = $tmdbImportService->importPopularMovies(
            pages: max(1, $pages),
            limit: max(1, $limit),
            downloadPosters: $downloadPosters,
            refreshExisting: $refreshExisting
        );

        $this->info('TMDB sync finished');
        $this->line('Processed: '.$stats['processed']);
        $this->line('Created: '.$stats['created']);
        $this->line('Updated: '.$stats['updated']);
        $this->line('Skipped: '.$stats['skipped']);
        $this->line('Errors: '.$stats['errors']);

        return self::SUCCESS;
    }
}
