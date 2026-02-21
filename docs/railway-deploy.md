# Railway Deploy Guide

This project is deployed on Railway with:
- `web` service (Dockerfile, nginx + php-fpm + queue worker for `video,default`)
- `worker` service (Dockerfile, queue worker for `default` queue only)
- `Postgres` service
- Volume mounted on `web` at `/var/www/html/storage`

## Why queue worker also runs in `web`

Railway volumes cannot be mounted to multiple services at the same time.  
Video/HLS jobs need the same storage mount that serves HLS files, so `web` also runs a queue worker (`video,default`) via supervisor.

`worker` stays available for non-media queue workloads (`default` queue).

## CLI setup

```bash
railway init -n netflix-main-portfolio
railway add --service web
railway add --service worker
railway add --database postgres
railway volume -s web add -m /var/www/html/storage
railway domain -s web
```

## Required variables

Set for `web`:

```bash
railway variable set -s web \
  SERVICE_ROLE=web \
  APP_ENV=production \
  APP_DEBUG=false \
  APP_KEY="<base64-key>" \
  APP_URL="https://<web-domain>" \
  LOG_CHANNEL=stderr \
  DB_CONNECTION=pgsql \
  DB_HOST='${{Postgres.PGHOST}}' \
  DB_PORT='${{Postgres.PGPORT}}' \
  DB_DATABASE='${{Postgres.PGDATABASE}}' \
  DB_USERNAME='${{Postgres.PGUSER}}' \
  DB_PASSWORD='${{Postgres.PGPASSWORD}}' \
  QUEUE_CONNECTION=database \
  FILESYSTEM_DISK=public \
  FFMPEG_PATH=/usr/bin/ffmpeg \
  FFPROBE_PATH=/usr/bin/ffprobe
```

Set for `worker`:

```bash
railway variable set -s worker \
  SERVICE_ROLE=worker \
  WORKER_QUEUES=default \
  APP_ENV=production \
  APP_DEBUG=false \
  APP_KEY="<base64-key>" \
  APP_URL="https://<web-domain>" \
  LOG_CHANNEL=stderr \
  DB_CONNECTION=pgsql \
  DB_HOST='${{Postgres.PGHOST}}' \
  DB_PORT='${{Postgres.PGPORT}}' \
  DB_DATABASE='${{Postgres.PGDATABASE}}' \
  DB_USERNAME='${{Postgres.PGUSER}}' \
  DB_PASSWORD='${{Postgres.PGPASSWORD}}' \
  QUEUE_CONNECTION=database \
  FILESYSTEM_DISK=public \
  FFMPEG_PATH=/usr/bin/ffmpeg \
  FFPROBE_PATH=/usr/bin/ffprobe
```

## Deploy

```bash
railway up --service web --detach -m "Deploy web"
railway up --service worker --detach -m "Deploy worker"
```

Preferred (test gate before deploy):

```powershell
./scripts/deploy-railway.ps1 -WebMessage "Deploy web" -WorkerMessage "Deploy worker"
```

## Validate

```bash
railway deployment list -s web --limit 1 --json
railway deployment list -s worker --limit 1 --json
railway logs --service web --deployment --lines 100
railway logs --service worker --deployment --lines 100
```
