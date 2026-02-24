# Cineclub Streaming Portfolio

Production-ready Laravel monolith focused on **editorial catalog UX** + **real video pipeline engineering**.

- Public playback model: trailers and demo clips only (legal/low-cost portfolio scope).
- Core differentiator: asynchronous FFmpeg pipeline to HLS (`m3u8 + segments`) with status tracking.

## Live Demo
- App: `https://cineclubarchive.up.railway.app`
- Admin login (demo):
  - Email: `test@gmail.com`
  - Password: `123`

## What This Project Demonstrates
- Laravel architecture hardening from FP prototype to deployable app.
- Queue-based video processing (no blocking HTTP requests).
- TMDB import-only integration with cache and graceful fallback.
- Role-based admin access (Spatie Permission).
- Dockerized local/dev/prod workflows.
- CI-friendly test suite.

## Feature Scope
### Public
- Home editorial catalog with featured hero + rails.
- Films and series listings.
- Search (`/search?q=`) with pagination.
- Film/series detail views with metadata and trailer/demo playback.

### Admin
- CRUD for films/series.
- Season/episode management.
- TMDB search/import (`movie` + `tv`).
- Video asset monitoring and health endpoints.
- Alternative artwork control per content:
  - TMDB path (`/abc.jpg`), external URL (`https://...`), or local upload.
  - Reset artwork back to TMDB values.

## Architecture (At a Glance)
- `Controllers`: route orchestration + authorization.
- `Form Requests`: validation and input constraints.
- `Services/Tmdb/*`: external API integration and mapping.
- `Jobs`: async media pipeline and TMDB episode imports.
- `Models`: content metadata, accessors (`poster_url`, `backdrop_url`, etc.).
- `Views`: Blade + Alpine, Cineclub design tokens.

Video pipeline chain:
1. `ProbeVideoJob`
2. `TranscodeToHlsJob`
3. `GenerateThumbnailsJob`
4. `CleanupSourceJob`

## Tech Stack
- Backend: Laravel (PHP 8.3), MySQL/Postgres.
- Frontend: Blade + Alpine + Tailwind.
- Auth: Laravel Breeze.
- Roles/Permissions: Spatie Laravel Permission.
- Video: FFmpeg + FFprobe.
- Queue: Database driver.
- Deploy: Railway (web + worker + postgres + volume).

## Local Quickstart (WSL + Sail)
> Recommended: run from WSL (Ubuntu) for best Docker/Sail compatibility.

1. Install dependencies
```bash
composer install
./vendor/bin/sail npm install
```

2. Start services
```bash
./vendor/bin/sail up -d
```

3. Bootstrap database
```bash
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan app:demo-seed
./vendor/bin/sail artisan storage:link
```

4. Frontend assets
```bash
./vendor/bin/sail npm run dev
# or
./vendor/bin/sail npm run build
```

5. Open app
- `http://localhost`

## Demo Data / Admin Access
- Promote any user to admin:
```bash
./vendor/bin/sail artisan app:make-admin your@email.com
```

## Tests & Quality
Run full suite:
```bash
./vendor/bin/sail artisan test
```

Run focused tests:
```bash
./vendor/bin/sail artisan test tests/Feature/AdminContentArtworkUpdateTest.php
```

## Environment Variables
Required (core):
- `APP_KEY`
- `APP_URL`
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `QUEUE_CONNECTION=database`
- `FILESYSTEM_DISK=public`

Video pipeline:
- `FFMPEG_PATH` (default `/usr/bin/ffmpeg`)
- `FFPROBE_PATH` (default `/usr/bin/ffprobe`)

TMDB (optional):
- `TMDB_TOKEN`
- If missing, TMDB UI/actions are disabled gracefully and app still works with local data.

## TMDB Import Model (Import-Only)
TMDB is **not** used during public navigation runtime.

Used only for:
- Admin search/import (`/admin/tmdb/search`)
- Optional sync command:
```bash
./vendor/bin/sail artisan tmdb:sync --limit=50
```

Cache policy:
- Search: 12h
- Details: 7d
- Videos: 7d

## Deploy (Railway)
Current production setup:
- `web` service: nginx + php-fpm + queue worker
- `postgres` service
- attached volume at `/var/www/html/storage`

Deploy command:
```bash
railway up --detach
```

Important runtime checks:
- `php artisan migrate --force`
- `php artisan storage:link`
- queue worker running: `queue:work --queue=video,default --tries=3 --timeout=3600`

### Deploy/Ops Checklist
- [ ] `php artisan test` passing locally
- [ ] `railway up --detach` completed successfully
- [ ] DB migrations applied in production (`php artisan migrate --force`)
- [ ] storage symlink exists (`php artisan storage:link`)
- [ ] queue worker active for `video,default`
- [ ] `/admin/health/api` returns `ok=true`
- [ ] `/admin/health/video-pipeline` shows FFmpeg/storage writable
- [ ] smoke test done (`/`, `/search?q=test`, `/admin/tmdb/search`)

## Operational Runbook (Quick)
- Health pages:
  - `/admin/health`
  - `/admin/health/api`
  - `/admin/health/video-pipeline`
- Queue diagnostics:
```bash
./vendor/bin/sail artisan queue:failed
./vendor/bin/sail artisan queue:retry all
```

## Tradeoffs / Decisions
- Monolith over SPA: faster stabilization and lower deployment complexity.
- Database queue first: simpler ops; Redis is optional.
- TMDB import-only: avoids runtime API dependency and rate/cost volatility.
- Public content strategy (trailers/demo clips): legal-safe, low storage cost.

## Portfolio Assets
Screenshots placeholder paths:
- `docs/screenshots/home.png`
- `docs/screenshots/detail-film.png`
- `docs/screenshots/detail-series.png`
- `docs/screenshots/search.png`
- `docs/screenshots/admin-dashboard.png`
- `docs/screenshots/admin-tmdb.png`
- `docs/screenshots/video-pipeline.png`

Capture workflow:
1. Run local app in production-like mode (`npm run build`).
2. Capture desktop screenshots (1440px wide) for the routes above.
3. Save images into `docs/screenshots/` with the exact names listed.
4. Keep screenshots updated when UI changes significantly.

## Credits
- Metadata and artwork paths: TMDB API.
- Icons/fonts and UI assets used under their respective licenses.

## Legal Note
This portfolio does not distribute full copyrighted films/series.
Primary public playback uses trailers and short demo clips uploaded by admin.
