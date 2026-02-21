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

3. Run migrations and seeders:
```bash
./vendor/bin/sail artisan migrate --seed
```

4. Start frontend dev server (optional):
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
