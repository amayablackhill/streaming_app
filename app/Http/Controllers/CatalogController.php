<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Episode;
use App\Models\Season;
use App\Models\VideoAsset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CatalogController extends Controller
{
    public function index(): View
    {
        return $this->home();
    }

    public function home(): View
    {
        $contents = Content::all();

        return view('content-list', compact('contents'));
    }

    public function showMovie(int $id): View
    {
        $content = Content::findOrFail($id);
        $hlsUrl = $this->resolveReadyHlsUrl($content);

        return view('viewMovie', compact('content', 'hlsUrl'));
    }

    public function showSeries(int $id): View
    {
        $content = Content::findOrFail($id);
        $hlsUrl = $this->resolveReadyHlsUrl($content);

        return view('viewSerie', compact('content', 'hlsUrl'));
    }

    public function movies(): View
    {
        $contents = Content::where('type', 'film')->get();

        return view('content-list', compact('contents'));
    }

    public function series(): View
    {
        $contents = Content::where('type', 'serie')->get();

        return view('content-list', compact('contents'));
    }

    public function search(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $results = null;

        if ($query !== '') {
            $results = Content::query()
                ->where(function ($builder) use ($query): void {
                    $builder
                        ->where('title', 'like', '%' . $query . '%')
                        ->orWhere('director', 'like', '%' . $query . '%')
                        ->orWhere('description', 'like', '%' . $query . '%')
                        ->orWhere('release_date', 'like', '%' . $query . '%');
                })
                ->orderByDesc('release_date')
                ->paginate(12)
                ->withQueryString();
        }

        return view('search', [
            'query' => $query,
            'results' => $results,
        ]);
    }

    public function watchEpisode(int $id, int $seasonId, int $episodeId): View
    {
        $content = Content::query()
            ->where('type', 'serie')
            ->findOrFail($id);

        $season = Season::query()
            ->where('serie_id', $content->id)
            ->findOrFail($seasonId);

        $episode = Episode::query()
            ->where('season_id', $season->id)
            ->findOrFail($episodeId);

        $previousEpisode = Episode::query()
            ->where('season_id', $season->id)
            ->where('episode_number', '<', $episode->episode_number)
            ->orderByDesc('episode_number')
            ->first();

        if (!$previousEpisode) {
            $previousSeason = Season::query()
                ->where('serie_id', $content->id)
                ->where('season_number', '<', $season->season_number)
                ->orderByDesc('season_number')
                ->first();

            if ($previousSeason) {
                $previousEpisode = Episode::query()
                    ->where('season_id', $previousSeason->id)
                    ->orderByDesc('episode_number')
                    ->first();
            }
        }

        $nextEpisode = Episode::query()
            ->where('season_id', $season->id)
            ->where('episode_number', '>', $episode->episode_number)
            ->orderBy('episode_number')
            ->first();

        if (!$nextEpisode) {
            $nextSeason = Season::query()
                ->where('serie_id', $content->id)
                ->where('season_number', '>', $season->season_number)
                ->orderBy('season_number')
                ->first();

            if ($nextSeason) {
                $nextEpisode = Episode::query()
                    ->where('season_id', $nextSeason->id)
                    ->orderBy('episode_number')
                    ->first();
            }
        }

        return view('watchEpisode', [
            'content' => $content,
            'season' => $season,
            'episode' => $episode,
            'previousEpisode' => $previousEpisode,
            'nextEpisode' => $nextEpisode,
        ]);
    }

    private function resolveReadyHlsUrl(Content $content): ?string
    {
        $videoAsset = $content->videoAssets()
            ->where('status', VideoAsset::STATUS_READY)
            ->whereNotNull('hls_master_path')
            ->orderByDesc('processed_at')
            ->orderByDesc('id')
            ->first();

        if (!$videoAsset) {
            return null;
        }

        try {
            if ($videoAsset->hls_disk === 'public') {
                return '/storage/'.ltrim($videoAsset->hls_master_path, '/');
            }

            return Storage::disk($videoAsset->hls_disk)->url($videoAsset->hls_master_path);
        } catch (Throwable) {
            return null;
        }
    }
}
