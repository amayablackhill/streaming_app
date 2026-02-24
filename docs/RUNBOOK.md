# Runbook

## Local Start (WSL + Sail)
```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan app:demo-seed
./vendor/bin/sail artisan storage:link
```

## Local Troubleshooting
### App does not load
```bash
./vendor/bin/sail ps
./vendor/bin/sail logs -f laravel.test
```

### Queue issues
```bash
./vendor/bin/sail logs -f laravel.worker
./vendor/bin/sail artisan queue:failed
./vendor/bin/sail artisan queue:retry all
```

### Missing images / local uploads
```bash
./vendor/bin/sail artisan storage:link
./vendor/bin/sail artisan optimize:clear
```

## Production Checks (Railway)
### Service health
- `/admin/health`
- `/admin/health/api`
- `/admin/health/video-pipeline`

### Logs
```bash
railway logs -s web -e production --lines 200
```

### Migrations
```bash
railway ssh -s web "cd /var/www/html && php artisan migrate --force"
```

### Cache reset
```bash
railway ssh -s web "cd /var/www/html && php artisan optimize:clear"
```

### Admin role assignment
```bash
railway ssh -s web "cd /var/www/html && php artisan app:make-admin your@email.com"
```

## Deploy/Ops/Health Checklist

### Pre-deploy
1. Run full tests:
```bash
./vendor/bin/sail artisan test
```
2. Build assets (if frontend changes):
```bash
./vendor/bin/sail npm run build
```
3. Ensure no pending local migrations are missing from repo.

### Deploy
1. Push deploy:
```bash
railway up --detach
```
2. Run production migrations:
```bash
railway ssh -s web "cd /var/www/html && php artisan migrate --force"
```
3. Ensure storage link exists:
```bash
railway ssh -s web "cd /var/www/html && php artisan storage:link"
```

### Post-deploy health
1. Open:
   - `/admin/health`
   - `/admin/health/api`
   - `/admin/health/video-pipeline`
2. Check web logs:
```bash
railway logs -s web -e production --lines 200
```
3. Check queue behavior:
```bash
railway ssh -s web "cd /var/www/html && php artisan queue:failed"
```
4. Smoke test critical routes:
   - `/`
   - `/search?q=test`
   - `/admin/tmdb/search`

## Known Operational Gotchas
- Windows symlink can break `railway up` due to `public/storage`.
  - Workaround: remove symlink before upload, then recreate locally with `storage:link`.
- If TMDB import returns 500, check latest logs first; most errors are schema/env mismatches.

## Deploy Checklist
1. `artisan test` green
2. `railway up --detach`
3. verify web + worker running
4. smoke test: login, admin pages, TMDB search, video upload status
