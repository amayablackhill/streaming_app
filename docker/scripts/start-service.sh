#!/usr/bin/env bash
set -euo pipefail

ROLE="${SERVICE_ROLE:-web}"

if [ "$ROLE" = "worker" ]; then
  QUEUES="${WORKER_QUEUES:-default}"
  exec php artisan queue:work --queue="${QUEUES}" --tries=3 --timeout=3600
fi

exec /usr/local/bin/start-web
