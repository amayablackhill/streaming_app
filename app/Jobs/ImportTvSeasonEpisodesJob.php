<?php

namespace App\Jobs;

use App\Models\Content;
use App\Models\Season;
use App\Services\Tmdb\Exceptions\TmdbException;
use App\Services\Tmdb\TmdbImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportTvSeasonEpisodesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(public int $contentId, public int $seasonId)
    {
        $this->onQueue('default');
    }

    public function handle(TmdbImportService $tmdbImportService): void
    {
        $content = Content::query()->find($this->contentId);
        $season = Season::query()->find($this->seasonId);

        if (!$content || !$season || (int) $season->serie_id !== (int) $content->id) {
            return;
        }

        try {
            $importedEpisodes = $tmdbImportService->importTvSeasonEpisodes($content, $season);

            Log::info('TMDB season episodes imported', [
                'content_id' => $content->id,
                'season_id' => $season->id,
                'season_number' => $season->season_number,
                'imported_episodes' => $importedEpisodes,
            ]);
        } catch (TmdbException $exception) {
            Log::warning('TMDB season episodes import failed', [
                'content_id' => $content->id,
                'season_id' => $season->id,
                'error' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Unexpected TMDB season episodes import error', [
                'content_id' => $content->id,
                'season_id' => $season->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
