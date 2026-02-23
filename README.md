# Netflix Portfolio App

Laravel monolith (Blade + Alpine) hardened as a streaming portfolio project.

## Local setup (Sail)
1. Install dependencies:
```bash
docker run --rm -u "$(id -u):$(id -g)" -v ./:/var/www/html -w /var/www/html laravelsail/php81-composer:latest composer install --ignore-platform-reqs
```

2. Start web + db + worker:
```bash
./vendor/bin/sail up -d
```

3. Run migrations + base seed (users/roles):
```bash
./vendor/bin/sail artisan migrate --seed
```

4. Load deterministic demo catalog data:
```bash
./vendor/bin/sail artisan app:demo-seed
```

5. Start frontend dev server (optional):
```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

## Queue worker
- Database queue is enabled by default (`QUEUE_CONNECTION=database`).
- Dedicated worker container runs:
```bash
php artisan queue:work --queue=video,default --tries=3 --timeout=3600
```

Useful checks:
```bash
./vendor/bin/sail ps
./vendor/bin/sail logs -f laravel.worker
./vendor/bin/sail artisan queue:failed
```

## Production Docker (clean baseline)

Build image:
```bash
docker build -t netflix-main:prod .
```

Run production stack locally:
```bash
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec web php artisan migrate --force
docker compose -f docker-compose.prod.yml ps
docker compose -f docker-compose.prod.yml logs -f web
docker compose -f docker-compose.prod.yml logs -f worker
```

Notes:
- Web container runs `nginx + php-fpm` through supervisor.
- Worker container runs `php artisan queue:work --queue=video,default --tries=3 --timeout=3600`.
- `FFMPEG_PATH` and `FFPROBE_PATH` are preconfigured to `/usr/bin/ffmpeg` and `/usr/bin/ffprobe`.

## Railway

This repository includes a Railway-ready Docker setup.

- `web` service uses `SERVICE_ROLE=web` (nginx + php-fpm + queue worker for `video,default`).
- `worker` service uses `SERVICE_ROLE=worker` and `WORKER_QUEUES=default`.
- `Postgres` is provisioned as a separate Railway database service.
- Storage volume is mounted on `web` at `/var/www/html/storage`.

Detailed steps and CLI commands:
- `docs/railway-deploy.md`

Pre-deploy gate (runs tests first, then deploys):
```powershell
./scripts/deploy-railway.ps1 -WebMessage "Deploy web" -WorkerMessage "Deploy worker"
```

## Demo URL
- `https://web-production-f4ce.up.railway.app`

## Demo steps
1. Open `https://web-production-f4ce.up.railway.app/login`.
2. Sign in with an admin demo user and go to `https://web-production-f4ce.up.railway.app/admin/addContent`.
3. Upload a short MP4 clip (<= 20s, <= 25MB) as content video.
4. Open `/admin/video-assets/{id}` and wait until status becomes `ready`.
5. Verify HLS playback and direct access to `master.m3u8` + `.ts` segments.

## TMDB import-only integration
TMDB is used only for admin import and periodic metadata refresh.
Public catalog navigation never calls TMDB at runtime.

### 1) Configure token
Set in `.env`:

```bash
TMDB_TOKEN=your_tmdb_v4_read_token
```

If `TMDB_TOKEN` is empty:
- app keeps working with local data
- TMDB admin import UI is shown as disabled
- `tmdb:sync` command exits gracefully

### 2) Admin import flow
1. Open `/admin/tmdb/search`
2. Search by title and choose type (`movie` or `tv`)
3. Click `Import` on a result
4. App creates/updates local content by unique key `(tmdb_type, tmdb_id)`
5. For `tv` imports, seasons are upserted immediately and episodes are queued in background jobs (idempotent per season/episode).

Manual TV episodes sync from admin:
- Open a series detail as admin and use `Import episodes` or `Import all seasons`.
- Jobs run on `default` queue and can be retried safely.

### 3) Manual sync command
Refresh stale TMDB-linked records (`tmdb_last_synced_at <= 30 days`):

```bash
./vendor/bin/sail artisan tmdb:sync --limit=50
```

### 4) Caching strategy
- `search`: 12 hours
- `details`: 7 days
- `videos`: 7 days

Images use TMDB CDN paths (`poster_path`, `backdrop_path`) directly.
No poster/backdrop files are downloaded during import.

## Curated import (CSV/JSON)
Import curated rails/lists from local files with idempotent upserts:

```bash
./vendor/bin/sail artisan curated:import storage/app/curated/home.csv
```

Useful options:
```bash
./vendor/bin/sail artisan curated:import storage/app/curated/home.csv --dry-run
./vendor/bin/sail artisan curated:import storage/app/curated/home.json --slug=home-curated --name="Home Curated"
./vendor/bin/sail artisan curated:import storage/app/curated/home.csv --default-type=movie
```

Supported formats:
- CSV: headers in snake_case.
- JSON: array of items, or object with `{ "list": {...}, "items": [...] }`.

### CSV example
```csv
rank,title,year,tmdb_type,tmdb_id,content_id
1,La Haine,1995,movie,406,
2,Perfect Days,2023,movie,976893,
3,,,,,12
```

Resolution priority per row:
1. `content_id` (local content)
2. `tmdb_id` + `tmdb_type` (`movie|tv`)
3. `title` (+ optional `year`) via TMDB search

### JSON example (with list metadata)
```json
{
  "list": {
    "slug": "home-curated",
    "name": "Home Curated",
    "description": "Editorial picks for homepage rails"
  },
  "items": [
    { "rank": 1, "tmdb_type": "movie", "tmdb_id": 406 },
    { "rank": 2, "title": "Perfect Days", "year": 2023, "tmdb_type": "movie" },
    { "rank": 3, "content_id": 12 }
  ]
}
```

Output includes summary, unresolved rows, and ambiguous matches.
`TMDB_TOKEN` is only required when resolving TMDB rows (`tmdb_id` or `title` lookup).

## Demo seed command (local-first)
This command sets a clean, consistent demo catalog for portfolio screenshots and recruiter walkthroughs.

```bash
./vendor/bin/sail artisan app:demo-seed
```

Append-only mode (keeps current catalog rows):
```bash
./vendor/bin/sail artisan app:demo-seed --append
```

Notes:
- Default behavior resets catalog tables (`contents`, `seasons`, `episodes`, `genres`) and inserts a curated baseline.
- Use `--append` if you want to keep existing catalog rows.
