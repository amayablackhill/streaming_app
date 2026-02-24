<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Episode;
use App\Models\Season;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SeasonEpisodeController extends Controller
{
    public function destroySeason(int $id): RedirectResponse
    {
        Gate::authorize('create', Content::class);

        $season = Season::findOrFail($id);
        $serieId = $season->serie_id;

        DB::transaction(function () use ($season): void {
            $season->episodes()->delete();
            $season->delete();
        });

        return redirect()
            ->route('seasons.manage', $serieId)
            ->with('success', __('Season deleted successfully'));
    }

    public function storeSeason(Request $request, int $id): RedirectResponse
    {
        Gate::authorize('create', Content::class);

        $request->validate([
            'season_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('seasons', 'season_number')->where(fn ($query) => $query->where('serie_id', $id)),
            ],
            'release_date' => 'required|date',
            'poster_path' => 'nullable|file|image|mimetypes:image/jpeg,image/png,image/webp|max:10240|dimensions:max_width=6000,max_height=6000',
            'overview' => 'nullable|string',
        ]);

        $content = Content::findOrFail($id);

        DB::transaction(function () use ($request, $content): void {
            $posterPath = $request->hasFile('poster_path')
                ? $request->file('poster_path')->store('seasons', 'public')
                : null;

            Season::create([
                'serie_id' => $content->id,
                'season_number' => $request->integer('season_number'),
                'release_date' => $request->date('release_date'),
                'poster_path' => $posterPath,
                'overview' => $request->input('overview'),
            ]);
        });

        return redirect()->route('seasons.manage', $id)->with('success', 'Season created successfully');
    }

    public function storeEpisode(Request $request, int $id): RedirectResponse
    {
        Gate::authorize('create', Content::class);

        $request->validate([
            'episode_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('episodes', 'episode_number')->where(fn ($query) => $query->where('season_id', $id)),
            ],
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'overview' => 'nullable|string',
            'plot' => 'nullable|string',
            'cover_path' => 'nullable|file|image|mimetypes:image/jpeg,image/png,image/webp|max:10240|dimensions:max_width=6000,max_height=6000',
            'episode_path' => 'nullable|file|mimes:mp4|mimetypes:video/mp4|max:25600',
        ]);

        $season = Season::findOrFail($id);

        $episode = new Episode([
            'episode_number' => $request->integer('episode_number'),
            'title' => $request->input('title'),
            'duration' => $request->integer('duration'),
            'release_date' => $request->date('release_date'),
            'overview' => $request->input('overview'),
            'plot' => $request->input('plot'),
            'cover_path' => $this->handleEpisodeImageUpload($request),
            'episode_path' => $this->handleEpisodeUpload($request),
        ]);

        $season->episodes()->save($episode);

        return redirect()->route('seasons.manage', $season->serie_id)->with('success', 'Episode created successfully');
    }

    public function updateEpisode(Request $request, int $id, int $episodeId): RedirectResponse
    {
        Gate::authorize('create', Content::class);

        $season = Season::findOrFail($id);
        $episode = Episode::where('season_id', $season->id)->findOrFail($episodeId);

        $request->validate([
            'episode_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('episodes', 'episode_number')
                    ->where(fn ($query) => $query->where('season_id', $season->id))
                    ->ignore($episode->id),
            ],
            'title' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'plot' => 'nullable|string',
            'cover_path' => 'nullable|file|image|mimetypes:image/jpeg,image/png,image/webp|max:10240|dimensions:max_width=6000,max_height=6000',
            'episode_path' => 'nullable|file|mimes:mp4|mimetypes:video/mp4|max:25600',
        ]);

        DB::transaction(function () use ($request, $episode): void {
            $episode->update([
                'episode_number' => $request->integer('episode_number'),
                'title' => $request->input('title'),
                'duration' => $request->integer('duration'),
                'release_date' => $request->date('release_date'),
                'plot' => $request->input('plot'),
                'cover_path' => $request->hasFile('cover_path') ? $this->handleEpisodeImageUpload($request) : $episode->cover_path,
                'episode_path' => $request->hasFile('episode_path') ? $this->handleEpisodeUpload($request) : $episode->episode_path,
            ]);
        });

        return redirect()
            ->route('seasons.manage', $season->serie_id)
            ->with('success', __('Episode ":episode" updated successfully', ['episode' => $episode->title]));
    }

    public function destroyEpisode(int $id, int $episodeId): RedirectResponse
    {
        Gate::authorize('create', Content::class);

        $season = Season::findOrFail($id);
        $episode = Episode::where('season_id', $season->id)->findOrFail($episodeId);
        $episodeTitle = $episode->title;
        $episode->delete();

        return redirect()
            ->route('seasons.manage', $season->serie_id)
            ->with('success', __('Episode ":episode" deleted successfully', ['episode' => $episodeTitle]));
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
        $safeTitle = str_replace(['/', '\\', ' '], '_', (string) $request->input('title', 'episode'));
        $filename = time().'_'.uniqid().'_'.$request->input('episode_number').'_'.$safeTitle.'.'.$file->getClientOriginalExtension();
        $file->storeAs('episodes', $filename, 'public');

        return $filename;
    }
}
