# Architecture

## Scope
Cineclub is a Laravel monolith (Blade + Alpine) designed for portfolio-grade deployability and low operational complexity.

## System Overview
- App type: Monolith
- Runtime: PHP 8.3, Laravel
- Frontend: Blade + Alpine + Tailwind
- Queue: Database driver
- Video stack: FFmpeg + FFprobe + HLS outputs
- External metadata: TMDB (import-only)
- Deploy target: Railway (web + postgres + volume)

## Request Flow
1. Web route -> Controller
2. FormRequest validation + authorization (policies/roles)
3. Domain/service call (TMDB import, media handling)
4. Eloquent persistence
5. Blade render / redirect

## Video Pipeline
Upload never blocks HTTP. A chained queue flow handles processing:
1. `ProbeVideoJob`
2. `TranscodeToHlsJob`
3. `GenerateThumbnailsJob`
4. `CleanupSourceJob`

Status model (`video_assets`): `pending|processing|ready|failed`.

## TMDB Integration
TMDB is not used in public page runtime.
Used only by:
- Admin import UI (`/admin/tmdb/search`)
- Optional sync command (`tmdb:sync`)

Caching:
- Search: 12h
- Details: 7d
- Videos: 7d

## Storage Model
- Local dev: `public` disk + `storage:link`
- Prod: Railway volume mounted on `/var/www/html/storage`
- HLS + thumbnails persist on volume

## Security Model
- Auth: Breeze
- AuthZ: Spatie Permission + policies
- Admin features behind admin role
- Upload limits and ffprobe checks enforced in requests/jobs

## Key Directories
- `app/Http/Controllers`: route orchestration
- `app/Http/Requests`: validation
- `app/Services/Tmdb`: TMDB client/import service
- `app/Jobs`: async processing
- `resources/views`: UI
- `docs`: operational and portfolio docs
