<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContentRequest;
use App\Jobs\CleanupSourceJob;
use App\Jobs\GenerateThumbnailsJob;
use App\Jobs\ProbeVideoJob;
use App\Jobs\TranscodeToHlsJob;
use App\Models\Content;
use App\Models\VideoAsset;
use App\Services\Tmdb\TmdbClient;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminContentController extends Controller
{
    public function addContent(ContentRequest $request)
    {
        $this->authorize('create', Content::class);

        return DB::transaction(function () use ($request) {
            $content = Content::create([
                'title' => $request->title,
                'description' => $request->description,
                'release_date' => $request->release_date,
                'director' => $request->director,
                'genre_id' => $request->genre_id,
                'rating' => $request->rating ?? null,
                'duration' => $request->duration,
                'type' => $request->type,
                'picture' => $this->handleImageUpload($request, $request->type),
                'video' => null,
                'is_featured' => $request->boolean('is_featured') && $request->type === 'film',
                'poster_path' => $this->resolveArtworkValue($request, 'poster_path', 'poster_image'),
                'backdrop_path' => $this->resolveArtworkValue($request, 'backdrop_path', 'backdrop_image'),
            ]);

            if ($content->is_featured) {
                Content::query()
                    ->where('type', 'film')
                    ->where('id', '!=', $content->id)
                    ->update(['is_featured' => false]);
            }

            $videoAsset = $this->queueVideoProcessing($request, $content);
            if ($videoAsset) {
                $content->update(['video' => $videoAsset->original_filename]);
                DB::afterCommit(function () use ($videoAsset): void {
                    $this->dispatchVideoPipeline($videoAsset);
                });
            }

            return redirect()
                ->route($this->catalogTableRoute($content->type))
                ->with('success', __('Content created successfully'))
                ->with('video_asset_id', $videoAsset?->id);
        });
    }

    public function updateContent(ContentRequest $request, int $id, TmdbClient $tmdbClient)
    {
        $content = Content::findOrFail($id);
        $this->authorize('update', $content);

        return DB::transaction(function () use ($request, $content, $tmdbClient) {
            $videoAsset = $request->hasFile('video') ? $this->queueVideoProcessing($request, $content) : null;
            if ($videoAsset) {
                DB::afterCommit(function () use ($videoAsset): void {
                    $this->dispatchVideoPipeline($videoAsset);
                });
            }

            $isFeatured = $request->has('is_featured')
                ? ($request->boolean('is_featured') && $content->type === 'film')
                : (bool) $content->is_featured;

            $tmdbArtwork = $this->resolveTmdbArtworkReset($request, $content, $tmdbClient);

            $content->update([
                'title' => $request->title,
                'description' => $request->description,
                'release_date' => $request->release_date,
                'director' => $request->director,
                'genre_id' => $request->genre_id,
                'rating' => $request->rating ?? null,
                'type' => $content->type,
                'duration' => $request->duration,
                'picture' => $request->hasFile('picture') ? $this->handleImageUpload($request, $content->type) : $content->picture,
                'video' => $videoAsset?->original_filename ?? $content->video,
                'is_featured' => $isFeatured,
                'poster_path' => $this->resolveArtworkValue(
                    $request,
                    'poster_path',
                    'poster_image',
                    $content->poster_path,
                    $tmdbArtwork['poster_path'] ?? null,
                    $request->boolean('poster_reset_tmdb')
                ),
                'backdrop_path' => $this->resolveArtworkValue(
                    $request,
                    'backdrop_path',
                    'backdrop_image',
                    $content->backdrop_path,
                    $tmdbArtwork['backdrop_path'] ?? null,
                    $request->boolean('backdrop_reset_tmdb')
                ),
            ]);

            if ($isFeatured && $content->type === 'film') {
                Content::query()
                    ->where('type', 'film')
                    ->where('id', '!=', $content->id)
                    ->update(['is_featured' => false]);
            }

            return redirect()
                ->route($this->catalogTableRoute($content->type))
                ->with('success', __(':title updated successfully', ['title' => $content->title]))
                ->with('video_asset_id', $videoAsset?->id);
        });
    }

    public function destroyContent(int $id)
    {
        $content = Content::findOrFail($id);
        $this->authorize('delete', $content);
        $redirectRoute = $this->catalogTableRoute($content->type);
        $title = $content->title;
        $content->delete();

        return redirect()
            ->route($redirectRoute)
            ->with('success', __(':title deleted successfully', ['title' => $title]));
    }

    private function handleImageUpload($request, ?string $contentType = null): ?string
    {
        if (!$request->hasFile('picture')) {
            return null;
        }

        $file = $request->file('picture');
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $type = $request->get('type') ?: $contentType;
        $where = $type === 'film' ? 'movies' : ($type === 'serie' ? 'series' : 'episodes');

        $file->storeAs('public/'.$where, $filename);

        return $filename;
    }

    private function queueVideoProcessing($request, Content $content): ?VideoAsset
    {
        if (!$request->hasFile('video')) {
            return null;
        }

        $file = $request->file('video');
        $uuid = (string) Str::uuid();
        $sourcePath = $file->storeAs(
            'videos/source',
            $uuid.'.'.$file->getClientOriginalExtension(),
            'public'
        );

        $videoAsset = VideoAsset::create([
            'uuid' => $uuid,
            'content_id' => $content->id,
            'original_filename' => $file->getClientOriginalName(),
            'source_disk' => 'public',
            'source_path' => $sourcePath,
            'hls_disk' => 'public',
            'status' => VideoAsset::STATUS_PENDING,
        ]);

        return $videoAsset;
    }

    private function dispatchVideoPipeline(VideoAsset $videoAsset): void
    {
        Bus::chain([
            new ProbeVideoJob($videoAsset->id),
            new TranscodeToHlsJob($videoAsset->id),
            new GenerateThumbnailsJob($videoAsset->id),
            new CleanupSourceJob($videoAsset->id),
        ])->onQueue('video')->dispatch();
    }

    private function catalogTableRoute(string $type): string
    {
        return $type === 'serie' ? 'series.table' : 'movies.table';
    }

    private function nullableTrimmed(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function resolveArtworkValue(
        ContentRequest $request,
        string $pathField,
        string $fileField,
        ?string $currentValue = null,
        ?string $tmdbValue = null,
        bool $useTmdbReset = false
    ): ?string {
        if ($useTmdbReset) {
            return $tmdbValue;
        }

        if ($request->hasFile($fileField)) {
            $file = $request->file($fileField);
            $storedPath = $file->storeAs(
                'content/artwork',
                time().'_'.uniqid().'_'.$fileField.'.'.$file->getClientOriginalExtension(),
                'public'
            );

            return 'local:'.$storedPath;
        }

        if ($request->exists($pathField)) {
            return $this->nullableTrimmed($request->input($pathField));
        }

        return $currentValue;
    }

    private function resolveTmdbArtworkReset(ContentRequest $request, Content $content, TmdbClient $tmdbClient): ?array
    {
        $shouldResetPoster = $request->boolean('poster_reset_tmdb');
        $shouldResetBackdrop = $request->boolean('backdrop_reset_tmdb');

        if (! $shouldResetPoster && ! $shouldResetBackdrop) {
            return null;
        }

        if (! $content->tmdb_id || ! $content->tmdb_type) {
            return [
                'poster_path' => null,
                'backdrop_path' => null,
            ];
        }

        try {
            $details = $tmdbClient->getDetails($content->tmdb_type, (int) $content->tmdb_id);
        } catch (\Throwable $exception) {
            return [
                'poster_path' => $content->poster_path,
                'backdrop_path' => $content->backdrop_path,
            ];
        }

        return [
            'poster_path' => $details['poster_path'] ?? null,
            'backdrop_path' => $details['backdrop_path'] ?? null,
        ];
    }
}
