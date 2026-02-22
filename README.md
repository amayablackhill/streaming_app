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

## Optional: TMDB seeder (real catalog data)
Use this when you want real movie metadata in local/dev.

1. Add `TMDB_API_KEY` to `.env`.
2. Run:
```bash
./vendor/bin/sail artisan db:seed --class=Database\\Seeders\\TmdbContentSeeder
```

Notes:
- It creates/updates `contents` (type `film`) from TMDB discover popular.
- Posters are downloaded to `storage/app/public/movies`.
- It is not part of default `DatabaseSeeder` to avoid CI/network dependency.

## TMDB sync command (API-safe)
For iterative imports without hammering the API:

```bash
./vendor/bin/sail artisan tmdb:sync --pages=1 --limit=12 --download-posters
```

Flags:
- `--pages`: how many discover pages to scan.
- `--limit`: max items to process.
- `--download-posters`: download poster files only when needed.
- `--refresh-existing`: force refresh existing records.

Rate/call controls:
- `TMDB_THROTTLE_MS` (default `250`) adds delay between outbound calls.
- Requests use retry + timeout and cache genres/details to reduce repeated calls.

## Demo seed command (local-first)
This command sets a clean, consistent demo catalog for portfolio screenshots and recruiter walkthroughs.

```bash
./vendor/bin/sail artisan app:demo-seed
```

Optional TMDB expansion (still controlled and rate-limited):
```bash
./vendor/bin/sail artisan app:demo-seed --with-tmdb --tmdb-pages=1 --tmdb-limit=12 --download-posters
```

Notes:
- Default behavior resets catalog tables (`contents`, `seasons`, `episodes`, `genres`) and inserts a curated baseline.
- Use `--append` if you want to keep existing catalog rows.
