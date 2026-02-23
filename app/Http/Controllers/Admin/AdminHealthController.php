<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AdminHealthController extends Controller
{
    public function index(): View
    {
        return view('admin.health');
    }

    public function api(): JsonResponse
    {
        $appCheck = [
            'ok' => true,
            'env' => app()->environment(),
            'debug' => (bool) config('app.debug'),
        ];

        $dbOk = $this->checkDatabase();
        $cacheOk = $this->checkCache();
        $queueCheck = $this->checkQueue();
        $storageOk = $this->checkStorage();
        $tmdbConfigured = trim((string) config('services.tmdb.token', '')) !== '';

        return response()->json([
            'ok' => $appCheck['ok'] && $dbOk && $cacheOk && $queueCheck['ok'] && $storageOk,
            'app' => $appCheck,
            'db' => ['ok' => $dbOk],
            'cache' => [
                'ok' => $cacheOk,
                'store' => config('cache.default'),
            ],
            'queue' => $queueCheck,
            'storage' => [
                'ok' => $storageOk,
                'disk' => 'public',
            ],
            'tmdb' => [
                'configured' => $tmdbConfigured,
                'language' => (string) config('services.tmdb.language', 'en-US'),
            ],
        ]);
    }

    private function checkDatabase(): bool
    {
        try {
            DB::select('select 1 as ok');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function checkCache(): bool
    {
        $key = 'healthchecks:admin:cache:' . now()->timestamp;

        try {
            Cache::put($key, 'ok', 30);
            $value = Cache::get($key);
            Cache::forget($key);

            return $value === 'ok';
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array{ok:bool,connection:string,notes:string}
     */
    private function checkQueue(): array
    {
        $connection = (string) config('queue.default', 'sync');
        $ok = $connection !== '';
        $notes = 'Queue connection configured.';

        if ($connection === 'database') {
            $hasJobsTable = Schema::hasTable('jobs');
            $ok = $ok && $hasJobsTable;
            $notes = $hasJobsTable
                ? 'Database queue ready.'
                : 'Database queue selected but jobs table missing.';
        }

        return [
            'ok' => $ok,
            'connection' => $connection,
            'notes' => $notes,
        ];
    }

    private function checkStorage(): bool
    {
        $disk = Storage::disk('public');
        $probePath = 'healthchecks/admin-health.txt';

        try {
            $disk->put($probePath, now()->toIso8601String());
            $exists = $disk->exists($probePath);
            $disk->delete($probePath);

            return $exists;
        } catch (Throwable) {
            return false;
        }
    }
}
