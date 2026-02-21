<?php

namespace App\Console\Commands;

use App\Jobs\CleanupSourceJob;
use App\Jobs\GenerateThumbnailsJob;
use App\Jobs\ProbeVideoJob;
use App\Jobs\TranscodeToHlsJob;
use App\Models\Content;
use App\Models\Genre;
use App\Models\VideoAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class VideoPipelineE2ECommand extends Command
{
    protected $signature = 'video:e2e {--timeout=180}';

    protected $description = 'Run end-to-end check for demo video pipeline (generate clip -> queue -> HLS ready).';

    public function handle(): int
    {
        $uuid = (string) Str::uuid();
        $disk = Storage::disk('public');
        $sourcePath = 'videos/source/'.$uuid.'.mp4';
        $localPath = $disk->path($sourcePath);
        $disk->makeDirectory(dirname($sourcePath));

        if (!$this->generateDemoClip($localPath)) {
            return self::FAILURE;
        }

        $genreId = Genre::query()->value('id');
        if (!$genreId) {
            $genreId = Genre::create(['name' => 'Demo'])->id;
        }

        $content = Content::create([
            'title' => 'E2E Demo '.now()->format('YmdHis'),
            'description' => 'Automated E2E demo clip',
            'release_date' => now()->toDateString(),
            'director' => 'System',
            'genre_id' => $genreId,
            'rating' => 80,
            'duration' => 1,
            'type' => 'film',
            'picture' => null,
            'video' => basename($localPath),
        ]);

        $videoAsset = VideoAsset::create([
            'uuid' => $uuid,
            'content_id' => $content->id,
            'original_filename' => basename($localPath),
            'source_disk' => 'public',
            'source_path' => $sourcePath,
            'hls_disk' => 'public',
            'status' => VideoAsset::STATUS_PENDING,
        ]);

        Bus::chain([
            new ProbeVideoJob($videoAsset->id),
            new TranscodeToHlsJob($videoAsset->id),
            new GenerateThumbnailsJob($videoAsset->id),
            new CleanupSourceJob($videoAsset->id),
        ])->onQueue('video')->dispatch();

        $this->info('asset_id='.$videoAsset->id);
        $this->info('status=pending');

        $timeout = (int) $this->option('timeout');
        $start = time();
        do {
            sleep(3);
            $videoAsset->refresh();
            $this->line('status='.$videoAsset->status);
            if (in_array($videoAsset->status, [VideoAsset::STATUS_READY, VideoAsset::STATUS_FAILED], true)) {
                break;
            }
        } while ((time() - $start) < $timeout);

        $videoAsset->refresh();
        if ($videoAsset->status !== VideoAsset::STATUS_READY) {
            $this->error('E2E failed: status='.$videoAsset->status.' error='.($videoAsset->error_message ?? 'n/a'));
            return self::FAILURE;
        }

        $masterUrl = $disk->url($videoAsset->hls_master_path);
        $this->info('master_url='.$masterUrl);
        $this->info('thumb_path='.($videoAsset->thumbnails_path ?? 'n/a'));

        $masterResponse = Http::timeout(15)->get(url($masterUrl));
        $this->info('master_http='.$masterResponse->status());

        $segmentUrl = $this->extractFirstSegmentUrl($videoAsset->hls_master_path);
        if ($segmentUrl) {
            $segmentResponse = Http::timeout(15)->get(url($segmentUrl));
            $this->info('segment_url='.$segmentUrl);
            $this->info('segment_http='.$segmentResponse->status());
        }

        return self::SUCCESS;
    }

    private function generateDemoClip(string $targetPath): bool
    {
        $ffmpegPath = env('FFMPEG_PATH', 'ffmpeg');
        $process = new Process([
            $ffmpegPath,
            '-y',
            '-f', 'lavfi',
            '-i', 'testsrc=size=640x360:rate=30',
            '-f', 'lavfi',
            '-i', 'sine=frequency=1000',
            '-t', '12',
            '-c:v', 'libx264',
            '-pix_fmt', 'yuv420p',
            '-c:a', 'aac',
            '-shortest',
            $targetPath,
        ]);
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('ffmpeg demo generation failed: '.trim($process->getErrorOutput()));
            return false;
        }

        return true;
    }

    private function extractFirstSegmentUrl(?string $masterPath): ?string
    {
        if (!$masterPath || !Storage::disk('public')->exists($masterPath)) {
            return null;
        }

        $master = Storage::disk('public')->get($masterPath);
        $baseDir = trim(dirname($masterPath), '/');

        foreach (preg_split('/\r\n|\r|\n/', $master) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $variantPath = $baseDir.'/'.$line;
            if (!Storage::disk('public')->exists($variantPath)) {
                continue;
            }

            $variant = Storage::disk('public')->get($variantPath);
            $variantDir = trim(dirname($variantPath), '/');
            foreach (preg_split('/\r\n|\r|\n/', $variant) as $variantLine) {
                $variantLine = trim($variantLine);
                if ($variantLine === '' || str_starts_with($variantLine, '#')) {
                    continue;
                }

                return '/storage/'.$variantDir.'/'.$variantLine;
            }
        }

        return null;
    }
}
