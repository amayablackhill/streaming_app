<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Season;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AdminPageController extends Controller
{
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

    public function addSeasonsIndex(): View
    {
        $series = Content::where('type', 'serie')->get();

        return view('addSeasons', compact('series'));
    }

    public function seasonsManage(int $id): View
    {
        $content = Content::with(['seasons.episodes'])->findOrFail($id);

        return view('seasonsManage', compact('content'));
    }

    public function createEpisodeForm(int $id): View
    {
        $season = Season::findOrFail($id);

        return view('addEpisodes', compact('season'));
    }

    public function editEpisodeForm(int $id, int $episodeId): View
    {
        $episode = Episode::findOrFail($episodeId);
        $season = Season::findOrFail($id);

        return view('addEpisodes', compact('episode', 'season'));
    }

    public function fallback(): RedirectResponse
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }

        abort(404);
    }

    public function getMovies(): JsonResponse
    {
        $movies = Content::where('type', 'film')->get();

        return response()->json($movies);
    }
}
