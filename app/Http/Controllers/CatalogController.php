<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Episode;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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

    public function dashboard(): View
    {
        return view('vueProject');
    }

    public function showMovie(int $id): View
    {
        $content = Content::findOrFail($id);

        return view('viewMovie', compact('content'));
    }

    public function showSeries(int $id): View
    {
        $content = Content::findOrFail($id);

        return view('viewSerie', compact('content'));
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
        $episode = Episode::findOrFail($episodeId);

        return view('watchEpisode', compact('episode'));
    }
}
