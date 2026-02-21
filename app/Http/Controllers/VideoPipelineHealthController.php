<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class VideoPipelineHealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $disk = Storage::disk('public');
        $probePath = 'healthchecks/video-pipeline.txt';
        $ffmpegPath = env('FFMPEG_PATH', 'ffmpeg');
        $ffprobePath = env('FFPROBE_PATH', 'ffprobe');

        $ffmpeg = $this->runBinaryCheck([$ffmpegPath, '-version']);
        $ffprobe = $this->runBinaryCheck([$ffprobePath, '-version']);

        $storageWritable = false;
        try {
            $disk->put($probePath, now()->toIso8601String());
            $storageWritable = $disk->exists($probePath);
            $disk->delete($probePath);
        } catch (\Throwable) {
            $storageWritable = false;
        }

        $dbOk = true;
        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $dbOk = false;
        }

        return response()->json([
            'ok' => $ffmpeg['ok'] && $ffprobe['ok'] && $storageWritable && $dbOk,
            'ffmpeg' => $ffmpeg,
            'ffprobe' => $ffprobe,
            'storage' => [
                'disk' => 'public',
                'writable' => $storageWritable,
            ],
            'queue' => [
                'connection' => config('queue.default'),
            ],
            'database' => [
                'ok' => $dbOk,
            ],
        ]);
    }

    private function runBinaryCheck(array $command): array
    {
        $process = new Process($command);
        $process->run();

        return [
            'ok' => $process->isSuccessful(),
            'command' => implode(' ', $command),
            'output' => mb_substr(trim($process->getOutput()), 0, 180),
            'error' => mb_substr(trim($process->getErrorOutput()), 0, 180),
        ];
    }
}
