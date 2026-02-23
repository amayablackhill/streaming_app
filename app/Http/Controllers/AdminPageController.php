<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Season;
use App\Models\VideoAsset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class AdminPageController extends Controller
{
    public function index(): View
    {
        return view('admin.index', [
            'movieCount' => Content::where('type', 'film')->count(),
            'seriesCount' => Content::where('type', 'serie')->count(),
            'videoAssetCount' => VideoAsset::query()->count(),
        ]);
    }

    public function moviesTable(): View
    {
        $movies = Content::where('type', 'film')->get();

        return view('moviesTable', compact('movies'));
    }

    public function seriesTable(): View
    {
        $series = Content::where('type', 'serie')->get();

        return view('seriesTable', compact('series'));
    }

    public function addContentForm(): View
    {
        $genres = Genre::all();

        return view('addContent', compact('genres'));
    }

    public function editContentForm(int $id): View
    {
        $content = Content::findOrFail($id);
        $genres = Genre::all();

        return view('editContent', compact('content', 'genres'));
    }

    public function seasonsManage(int $id): View
    {
        $content = Content::with(['seasons.episodes'])->findOrFail($id);

        return view('seasonsManage', compact('content'));
    }

    public function createEpisodeForm(int $id): View
    {
        $season = Season::with('contents')->findOrFail($id);

        return view('addEpisodes', compact('season'));
    }

    public function editEpisodeForm(int $id, int $episodeId): View
    {
        $season = Season::with('contents')->findOrFail($id);
        $episode = Episode::where('season_id', $season->id)->findOrFail($episodeId);

        return view('addEpisodes', compact('episode', 'season'));
    }

    public function fallback(): RedirectResponse
    {
        return redirect()->route('admin.home');
    }
}
