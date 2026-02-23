<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Services\Tmdb\Exceptions\TmdbException;
use App\Services\Tmdb\Exceptions\TmdbNotConfiguredException;
use App\Services\Tmdb\TmdbImportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TmdbImportController extends Controller
{
    public function search(Request $request, TmdbImportService $tmdbImportService): View
    {
        $query = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', 'movie');
        $type = in_array($type, ['movie', 'tv'], true) ? $type : 'movie';
        $page = max(1, (int) $request->query('page', 1));

        $tmdbEnabled = $this->isTmdbEnabled();
        $results = [];
        $errorMessage = null;

        if ($tmdbEnabled && $query !== '') {
            try {
                $results = $tmdbImportService->search($query, $type, $page);
            } catch (TmdbNotConfiguredException $exception) {
                $tmdbEnabled = false;
                $errorMessage = $exception->getMessage();
            } catch (TmdbException $exception) {
                report($exception);
                $errorMessage = 'TMDB search failed. Please try again in a moment.';
            }
        }

        return view('admin.tmdb-search', [
            'query' => $query,
            'type' => $type,
            'page' => $page,
            'results' => $results,
            'tmdbEnabled' => $tmdbEnabled,
            'errorMessage' => $errorMessage,
        ]);
    }

    public function import(Request $request, TmdbImportService $tmdbImportService): RedirectResponse
    {
        if (!$this->isTmdbEnabled()) {
            return redirect()
                ->route('admin.tmdb.search')
                ->with('error', 'TMDB import is disabled. Configure TMDB_TOKEN first.');
        }

        $validated = $request->validate([
            'tmdb_id' => ['required', 'integer', 'min:1'],
            'tmdb_type' => ['required', Rule::in(['movie', 'tv'])],
        ]);

        try {
            $content = $tmdbImportService->importByTmdb($validated['tmdb_type'], (int) $validated['tmdb_id']);
        } catch (TmdbException $exception) {
            report($exception);

            return redirect()
                ->route('admin.tmdb.search', [
                    'q' => $request->input('q'),
                    'type' => $request->input('type', 'movie'),
                    'page' => $request->input('page', 1),
                ])
                ->with('error', $exception->getMessage());
        }

        if ($content->type === 'serie') {
            $queuedSeasons = $tmdbImportService->dispatchTvEpisodeImports($content);
            $message = $queuedSeasons > 0
                ? "TMDB import completed. Episodes sync queued for {$queuedSeasons} seasons."
                : 'TMDB import completed successfully.';

            return redirect('/series/' . $content->id)->with('status', $message);
        }

        return redirect('/movies/' . $content->id)->with('status', 'TMDB import completed successfully.');
    }

    public function importSeriesEpisodes(Content $content, Request $request, TmdbImportService $tmdbImportService): RedirectResponse
    {
        if (!$this->isTmdbEnabled()) {
            return redirect()
                ->back()
                ->with('error', 'TMDB import is disabled. Configure TMDB_TOKEN first.');
        }

        if ($content->type !== 'serie' || $content->tmdb_type !== 'tv' || empty($content->tmdb_id)) {
            return redirect()
                ->back()
                ->with('error', 'This series is not linked to TMDB.');
        }

        $importAll = $request->boolean('all', false);
        $maxSeasons = $importAll ? 0 : null;
        $queuedSeasons = $tmdbImportService->dispatchTvEpisodeImports($content, $maxSeasons);

        return redirect()
            ->back()
            ->with('status', "Episodes sync queued for {$queuedSeasons} seasons.");
    }

    private function isTmdbEnabled(): bool
    {
        return trim((string) config('services.tmdb.token', '')) !== '';
    }
}
