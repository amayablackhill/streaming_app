<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContentRequest;
use App\Models\Content;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AdminContentController extends Controller
{
    public function addContent(ContentRequest $request)
    {
        $this->authorize('create', Content::class);

        return DB::transaction(function () use ($request) {
            Content::create([
                'title' => $request->title,
                'description' => $request->description,
                'release_date' => $request->release_date,
                'director' => $request->director,
                'genre_id' => $request->genre_id,
                'rating' => $request->rating ?? null,
                'duration' => $request->duration,
                'type' => $request->type,
                'picture' => $this->handleImageUpload($request),
                'video' => $this->handleVideoUpload($request),
            ]);

            return redirect()->route('content.add')->with('success', __('Content created successfully'));
        });
    }

    public function updateContent(ContentRequest $request, int $id)
    {
        $content = Content::findOrFail($id);
        $this->authorize('update', $content);

        return DB::transaction(function () use ($request, $content) {
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
                'video' => $request->hasFile('video') ? $this->handleVideoUpload($request) : $content->video,
            ]);

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

    private function handleVideoUpload($request): ?string
    {
        if (!$request->hasFile('video')) {
            return null;
        }

        $file = $request->file('video');
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

        $outputFilenameBig = 'max_'.$filename;
        $outputFilenameMedium = 'mid_'.$filename;
        $outputFilenameSmall = 'min_'.$filename;

        $path = $file->storeAs('content', $filename, 'public');
        $fullInputPath = storage_path('app/public/'.$path);

        $fullOutputPathBig = storage_path('app/public/content/'.$outputFilenameBig);
        $fullOutputPathMedium = storage_path('app/public/content/'.$outputFilenameMedium);
        $fullOutputPathSmall = storage_path('app/public/content/'.$outputFilenameSmall);

        if (!file_exists(dirname($fullOutputPathBig))) {
            mkdir(dirname($fullOutputPathBig), 0755, true);
        }

        $this->processVideo($fullInputPath, $fullOutputPathBig, 'max', $filename);
        $this->processVideo($fullInputPath, $fullOutputPathMedium, 'mid');
        $this->processVideo($fullInputPath, $fullOutputPathSmall, 'min');

        return $filename;
    }

    private function processVideo(string $inputPath, string $outputPath, string $size, ?string $filename = null): void
    {
        $scale = 'scale=640:360';

        if ($size === 'mid') {
            $scale = 'scale=480:270';
        } elseif ($size === 'min') {
            $scale = 'scale=320:180';
        } elseif ($filename !== null) {
            $framesOutputDir = storage_path('app/public/content/frames/'.$filename);
            if (!file_exists($framesOutputDir)) {
                mkdir($framesOutputDir, 0755, true);
            }

            $getDurationCommand = [
                'ffprobe',
                '-v', 'error',
                '-show_entries', 'format=duration',
                '-of', 'default=noprint_wrappers=1:nokey=1',
                $inputPath,
            ];

            $processDuration = new Process($getDurationCommand);
            $processDuration->run();
            $duration = (float) $processDuration->getOutput();
            $maxFrames = 10;
            $timeInterval = $duration / ($maxFrames + 1);
            $frameTimes = [];

            for ($i = 1; $i <= $maxFrames; $i++) {
                $frameTime = $i * $timeInterval;
                $frameTimes[] = $frameTime;

                $commandFrames = [
                    'ffmpeg',
                    '-ss', $frameTime,
                    '-i', $inputPath,
                    '-frames:v', '1',
                    '-q:v', '2',
                    $framesOutputDir.'/frame_'.str_pad((string) $i, 3, '0', STR_PAD_LEFT).'.jpg',
                ];

                $processFrames = new Process($commandFrames);
                try {
                    $processFrames->mustRun();
                } catch (ProcessFailedException $exception) {
                    throw new ProcessFailedException($processFrames);
                }
            }

            file_put_contents($framesOutputDir.'/times.json', json_encode($frameTimes));
        }

        $command = [
            'ffmpeg',
            '-i', $inputPath,
            '-vf', $scale,
            '-c:a', 'copy',
            '-y',
            $outputPath,
        ];

        $process = new Process($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
