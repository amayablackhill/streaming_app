<?php

namespace App\Console\Commands;

use App\Models\Content;
use App\Models\CuratedList;
use App\Models\CuratedListItem;
use App\Services\Tmdb\Exceptions\TmdbException;
use App\Services\Tmdb\Exceptions\TmdbNotConfiguredException;
use App\Services\Tmdb\TmdbImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class CuratedImportCommand extends Command
{
    protected $signature = 'curated:import
        {path : Local CSV or JSON file path}
        {--slug= : Curated list slug override}
        {--name= : Curated list name override}
        {--description= : Curated list description override}
        {--default-type=movie : Default TMDB type (movie|tv)}
        {--dry-run : Parse and resolve without persisting}';

    protected $description = 'Import curated list items from local CSV/JSON with idempotent upserts';

    public function handle(TmdbImportService $tmdbImportService): int
    {
        $path = $this->resolvePath((string) $this->argument('path'));

        if (!$path || !File::exists($path)) {
            $this->error('File not found. Provide a valid local CSV or JSON path.');

            return self::FAILURE;
        }

        $payload = $this->parseFile($path);
        $items = $payload['items'];
        $meta = $payload['meta'];

        if ($items === []) {
            $this->warn('No rows found in file.');

            return self::SUCCESS;
        }

        $slug = (string) ($this->option('slug') ?: ($meta['slug'] ?? Str::slug(pathinfo($path, PATHINFO_FILENAME))));
        $name = (string) ($this->option('name') ?: ($meta['name'] ?? Str::title(str_replace('-', ' ', $slug))));
        $description = (string) ($this->option('description') ?: ($meta['description'] ?? ''));
        $defaultType = $this->normalizeTmdbType((string) $this->option('default-type'));
        $dryRun = (bool) $this->option('dry-run');

        if ($slug === '') {
            $this->error('Unable to resolve list slug.');

            return self::FAILURE;
        }

        $list = null;
        if (!$dryRun) {
            $list = CuratedList::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description !== '' ? $description : null]
            );
        }

        $upserted = 0;
        $created = 0;
        $updated = 0;
        $unresolved = [];
        $ambiguous = [];

        foreach ($items as $index => $row) {
            $line = $index + 1;
            $rank = isset($row['rank']) && is_numeric($row['rank']) ? (int) $row['rank'] : $line;

            $resolution = $this->resolveContent($row, $tmdbImportService, $defaultType);

            if ($resolution['status'] === 'unresolved') {
                $unresolved[] = [
                    'line' => $line,
                    'title' => (string) ($row['title'] ?? ''),
                    'reason' => (string) $resolution['reason'],
                ];
                continue;
            }

            if ($resolution['status'] === 'ambiguous') {
                $ambiguous[] = [
                    'line' => $line,
                    'title' => (string) ($row['title'] ?? ''),
                    'candidates' => (array) $resolution['candidates'],
                ];
                continue;
            }

            /** @var Content $content */
            $content = $resolution['content'];
            $upserted++;

            if (!$dryRun && $list) {
                $item = CuratedListItem::query()->updateOrCreate(
                    [
                        'curated_list_id' => $list->id,
                        'content_id' => $content->id,
                    ],
                    [
                        'rank' => $rank,
                    ]
                );

                if ($item->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }
        }

        $this->info('Curated import finished.');
        $this->line("List: {$slug}");
        $this->line('Dry run: '.($dryRun ? 'yes' : 'no'));
        $this->line("Rows processed: ".count($items));
        $this->line("Rows resolved: {$upserted}");
        if (!$dryRun) {
            $this->line("Rows created: {$created}");
            $this->line("Rows updated: {$updated}");
        }
        $this->line('Unresolved entries: '.count($unresolved));
        $this->line('Ambiguous entries: '.count($ambiguous));

        if ($unresolved !== []) {
            $this->warn('Unresolved rows:');
            foreach ($unresolved as $entry) {
                $this->line("- line {$entry['line']} ({$entry['title']}): {$entry['reason']}");
            }
        }

        if ($ambiguous !== []) {
            $this->warn('Ambiguous rows:');
            foreach ($ambiguous as $entry) {
                $this->line("- line {$entry['line']} ({$entry['title']}): ".implode(' | ', $entry['candidates']));
            }
        }

        return self::SUCCESS;
    }

    private function resolvePath(string $path): ?string
    {
        $candidate = trim($path);
        if ($candidate === '') {
            return null;
        }

        if (File::exists($candidate)) {
            return $candidate;
        }

        $projectRelative = base_path($candidate);
        if (File::exists($projectRelative)) {
            return $projectRelative;
        }

        return null;
    }

    /**
     * @return array{meta: array<string, mixed>, items: array<int, array<string, mixed>>}
     */
    private function parseFile(string $path): array
    {
        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));
        $contents = (string) File::get($path);

        if ($extension === 'json') {
            return $this->parseJson($contents);
        }

        if ($extension === 'csv') {
            return $this->parseCsv($contents);
        }

        throw new \RuntimeException('Unsupported file type. Use .csv or .json');
    }

    /**
     * @return array{meta: array<string, mixed>, items: array<int, array<string, mixed>>}
     */
    private function parseJson(string $contents): array
    {
        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Invalid JSON file.');
        }

        if (array_is_list($decoded)) {
            return ['meta' => [], 'items' => array_values($decoded)];
        }

        $meta = is_array($decoded['list'] ?? null) ? $decoded['list'] : [];
        $items = $decoded['items'] ?? [];

        if (!is_array($items) || !array_is_list($items)) {
            throw new \RuntimeException('JSON must contain an "items" array.');
        }

        return ['meta' => $meta, 'items' => array_values($items)];
    }

    /**
     * @return array{meta: array<string, mixed>, items: array<int, array<string, mixed>>}
     */
    private function parseCsv(string $contents): array
    {
        $lines = preg_split('/\r\n|\n|\r/', trim($contents)) ?: [];
        if ($lines === []) {
            return ['meta' => [], 'items' => []];
        }

        $headers = str_getcsv(array_shift($lines));
        $headers = array_map(
            fn ($header) => Str::snake(trim((string) $header)),
            $headers
        );

        $items = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $values = str_getcsv($line);
            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = $values[$index] ?? null;
            }

            $items[] = $row;
        }

        return ['meta' => [], 'items' => $items];
    }

    /**
     * @param array<string, mixed> $row
     * @return array{status: 'resolved', content: Content}|array{status:'unresolved', reason:string}|array{status:'ambiguous', candidates:array<int, string>}
     */
    private function resolveContent(array $row, TmdbImportService $tmdbImportService, string $defaultType): array
    {
        $contentId = isset($row['content_id']) && is_numeric($row['content_id'])
            ? (int) $row['content_id']
            : null;
        if ($contentId) {
            $content = Content::query()->find($contentId);
            if ($content) {
                return ['status' => 'resolved', 'content' => $content];
            }

            return ['status' => 'unresolved', 'reason' => "content_id {$contentId} not found locally"];
        }

        $tmdbType = $this->normalizeTmdbType((string) ($row['tmdb_type'] ?? $row['type'] ?? $defaultType));
        $tmdbId = isset($row['tmdb_id']) && is_numeric($row['tmdb_id'])
            ? (int) $row['tmdb_id']
            : null;

        if ($tmdbId && $tmdbId > 0) {
            try {
                $content = $tmdbImportService->importByTmdb($tmdbType, $tmdbId);

                return ['status' => 'resolved', 'content' => $content];
            } catch (TmdbNotConfiguredException $exception) {
                return ['status' => 'unresolved', 'reason' => $exception->getMessage()];
            } catch (TmdbException $exception) {
                return ['status' => 'unresolved', 'reason' => $exception->getMessage()];
            }
        }

        $title = trim((string) ($row['title'] ?? $row['name'] ?? ''));
        if ($title === '') {
            return ['status' => 'unresolved', 'reason' => 'missing title and tmdb_id'];
        }

        try {
            $results = collect($tmdbImportService->search($title, $tmdbType, 1));
        } catch (TmdbNotConfiguredException $exception) {
            return ['status' => 'unresolved', 'reason' => $exception->getMessage()];
        } catch (TmdbException $exception) {
            return ['status' => 'unresolved', 'reason' => $exception->getMessage()];
        } catch (Throwable $exception) {
            return ['status' => 'unresolved', 'reason' => $exception->getMessage()];
        }

        $year = isset($row['year']) && is_numeric($row['year']) ? (string) (int) $row['year'] : null;
        if ($year) {
            $results = $results->filter(function (array $item) use ($year): bool {
                $releaseDate = (string) ($item['release_date'] ?? '');

                return Str::startsWith($releaseDate, $year);
            })->values();
        }

        if ($results->count() === 0) {
            return ['status' => 'unresolved', 'reason' => 'no TMDB match found'];
        }

        if ($results->count() > 1) {
            $candidates = $results
                ->take(3)
                ->map(function (array $item): string {
                    $candidateTitle = (string) ($item['title'] ?? 'untitled');
                    $candidateDate = (string) ($item['release_date'] ?? '');

                    return trim("{$candidateTitle} {$candidateDate}");
                })
                ->all();

            return ['status' => 'ambiguous', 'candidates' => $candidates];
        }

        $match = $results->first();
        if (!is_array($match) || !isset($match['tmdb_id'])) {
            return ['status' => 'unresolved', 'reason' => 'invalid TMDB match payload'];
        }

        try {
            $content = $tmdbImportService->importByTmdb($tmdbType, (int) $match['tmdb_id']);

            return ['status' => 'resolved', 'content' => $content];
        } catch (TmdbNotConfiguredException $exception) {
            return ['status' => 'unresolved', 'reason' => $exception->getMessage()];
        } catch (TmdbException $exception) {
            return ['status' => 'unresolved', 'reason' => $exception->getMessage()];
        }
    }

    private function normalizeTmdbType(string $value): string
    {
        $type = Str::lower(trim($value));

        return match ($type) {
            'movie', 'film' => 'movie',
            'tv', 'series', 'serie' => 'tv',
            default => 'movie',
        };
    }
}

