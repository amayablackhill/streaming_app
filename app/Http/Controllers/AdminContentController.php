<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContentRequest;
use App\Jobs\CleanupSourceJob;
use App\Jobs\GenerateThumbnailsJob;
use App\Jobs\ProbeVideoJob;
use App\Jobs\TranscodeToHlsJob;
use App\Models\Content;
use App\Models\VideoAsset;
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
                'picture' => $this->handleImageUpload($request),
                'video' => null,
                'is_featured' => $request->boolean('is_featured') && $request->type === 'film',
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
                ->route('content.add')
                ->with('success', __('Content created successfully'))
                ->with('video_asset_id', $videoAsset?->id);
        });
    }

    public function updateContent(ContentRequest $request, int $id)
    {
        $content = Content::findOrFail($id);
        $this->authorize('update', $content);

        return DB::transaction(function () use ($request, $content) {
            $videoAsset = $request->hasFile('video') ? $this->queueVideoProcessing($request, $content) : null;
            if ($videoAsset) {
                DB::afterCommit(function () use ($videoAsset): void {
                    $this->dispatchVideoPipeline($videoAsset);
                });
            }

            $isFeatured = $request->has('is_featured')
                ? ($request->boolean('is_featured') && $content->type === 'film')
                : (bool) $content->is_featured;

            $content->update([
                'title' => $request->title,
                'description' => $request->description,
                'release_date' => $request->release_date,
                'director' => $request->director,
                'genre_id' => $request->genre_id,
                'rating' => $request->rating ?? null,
                'type' => $content->type,
                'duration' => $request->duration,
                'picture' => $request->hasFile('picture') ? $this->handleImageUpload($request) : $content->picture,
                'video' => $videoAsset?->original_filename ?? $content->video,
                'is_featured' => $isFeatured,
            ]);

            if ($isFeatured && $content->type === 'film') {
                Content::query()
                    ->where('type', 'film')
                    ->where('id', '!=', $content->id)
                    ->update(['is_featured' => false]);
            }

            return redirect()->route('dashboard')->with('success', __(':movie updated successfully', ['movie' => $content->title]));
        });
    }

    public function destroyContent(int $id)
    {
        $content = Content::findOrFail($id);
        $this->authorize('delete', $content);
        $content->delete();

        return redirect()->route('dashboard')->with('success', __('Movie deleted successfully'));
    }

    private function handleImageUpload($request): ?string
    {
        if (!$request->hasFile('picture')) {
            return null;
        }

        $file = $request->file('picture');
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $where = $request->get('type') === 'film' ? 'movies' : ($request->get('type') === 'serie' ? 'series' : 'episodes');

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
}
