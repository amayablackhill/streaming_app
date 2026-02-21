param(
    [string]$WebMessage = "Deploy web",
    [string]$WorkerMessage = "Deploy worker"
)

$ErrorActionPreference = "Stop"

Write-Host "[1/3] Running test suite..."
docker compose exec -T laravel.test php artisan test --no-interaction

Write-Host "[2/3] Deploying web service..."
railway up --service web --detach -m $WebMessage

Write-Host "[3/3] Deploying worker service..."
railway up --service worker --detach -m $WorkerMessage

Write-Host "Deploy completed."
