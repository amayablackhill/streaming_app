<?php

namespace App\Console\Commands;

use App\Models\Content;
use App\Services\Tmdb\Exceptions\TmdbException;
use App\Services\Tmdb\Exceptions\TmdbNotConfiguredException;
use App\Services\Tmdb\TmdbImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TmdbSyncCommand extends Command
{
    protected $signature = 'tmdb:sync
        {--limit=50 : Max stale records to sync}';

    protected $description = 'Refresh metadata for stale TMDB-linked local records';

    public function handle(TmdbImportService $tmdbImportService): int
    {
        if (trim((string) config('services.tmdb.token', '')) === '') {
            $this->warn('TMDB_TOKEN is not configured. Sync skipped.');
            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');
        $limit = max(1, $limit);
        $staleBefore = Carbon::now()->subDays(30);

        $targets = Content::query()
            ->whereNotNull('tmdb_id')
            ->whereNotNull('tmdb_type')
            ->where(function ($query) use ($staleBefore): void {
                $query
                    ->whereNull('tmdb_last_synced_at')
                    ->orWhere('tmdb_last_synced_at', '<=', $staleBefore);
            })
            ->orderBy('tmdb_last_synced_at')
            ->limit($limit)
            ->get();

        if ($targets->isEmpty()) {
            $this->info('No stale TMDB-linked records found.');
            return self::SUCCESS;
        }

        $updated = 0;
        $errors = 0;

        foreach ($targets as $content) {
            try {
                $tmdbImportService->importByTmdb((string) $content->tmdb_type, (int) $content->tmdb_id);
                $updated++;
                $this->line("Synced {$content->tmdb_type}:{$content->tmdb_id} ({$content->title})");
            } catch (TmdbNotConfiguredException $exception) {
                $this->warn($exception->getMessage());
                return self::SUCCESS;
            } catch (TmdbException $exception) {
                $errors++;
                report($exception);
                $this->error("Failed {$content->tmdb_type}:{$content->tmdb_id} - {$exception->getMessage()}");
            }
        }

        $this->info('TMDB sync finished');
        $this->line("Candidates: {$targets->count()}");
        $this->line("Updated: {$updated}");
        $this->line("Errors: {$errors}");

        return self::SUCCESS;
    }
}
