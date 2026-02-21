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
