<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Episode;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeasonEpisodeController extends Controller
{
    public function destroySeason(int $id)
    {
        $season = Season::findOrFail($id);
        $season->delete();

        return back()->with('success', __('Movie deleted successfully'));
    }

    public function storeSeason(Request $request, int $id)
    {
        $request->validate([
            'season_number' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'poster_path' => 'nullable|image|mimes:jpeg,png,jpg|max:51200',
            'overview' => 'nullable|string',
        ]);

        $content = Content::findOrFail($id);

        return DB::transaction(function () use ($request, $content, $id) {
            Season::create([
                'serie_id' => $content->id,
                'season_number' => $request->season_number,
                'release_date' => $request->release_date,
                'poster_path' => $request->poster_path,
                'overview' => $request->overview,
            ]);

            return redirect()->route('seasons.manage', $id)->with('success', 'Temporada añadida correctamente');
        });
    }

    public function updateEpisode(Request $request, int $id, int $episodeId)
    {
        $season = Season::findOrFail($id);
        $episode = Episode::findOrFail($episodeId);

        return DB::transaction(function () use ($request, $episode, $season) {
            $episode->update([
                'episode_number' => $request->episode_number,
                'title' => $request->title,
                'duration' => $request->duration,
                'release_date' => $request->release_date,
                'plot' => $request->plot,
                'cover_path' => $request->hasFile('cover_path') ? $this->handleEpisodeImageUpload($request) : $episode->cover_path,
            ]);

            return redirect()->route('seasons.manage', $season->serie_id)->with('success', __(':episode updated successfully', ['episode' => $episode->title]));
        });
    }

    public function storeEpisode(Request $request, int $id)
    {
        $request->validate([
            'episode_number' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'overview' => 'nullable|string',
            'plot' => 'nullable|string',
            'cover_path' => 'nullable|image|mimes:jpeg,png,jpg|max:51200',
            'episode_path' => 'nullable|mimetypes:video/mp4',
        ]);

        $season = Season::findOrFail($id);

        $episode = new Episode([
            'episode_number' => $request->episode_number,
            'title' => $request->title,
            'duration' => $request->duration,
            'release_date' => $request->release_date,
            'overview' => $request->overview,
            'plot' => $request->plot,
            'cover_path' => $this->handleEpisodeImageUpload($request),
            'episode_path' => $this->handleEpisodeUpload($request),
        ]);

        $season->episodes()->save($episode);

        return back()->with('success', 'Episodio añadido correctamente');
    }

    private function handleEpisodeImageUpload(Request $request): ?string
    {
        if (!$request->hasFile('cover_path')) {
            return null;
        }

        $file = $request->file('cover_path');
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->storeAs('public/episodes', $filename);

        return $filename;
    }

    private function handleEpisodeUpload(Request $request): ?string
    {
        if (!$request->hasFile('episode_path')) {
            return null;
        }

        $file = $request->file('episode_path');
        $filename = time().'_'.uniqid().'_'.$request->episode_number.'_'.$request->title.'.'.$file->getClientOriginalExtension();
        $file->storeAs('episodes', $filename, 'public');

        return $filename;
    }
}
