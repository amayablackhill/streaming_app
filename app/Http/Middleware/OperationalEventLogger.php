<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OperationalEventLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        try {
            /** @var Response $response */
            $response = $next($request);
        } catch (Throwable $exception) {
            $this->logger()->error('http.exception', $this->context($request, 500, $startedAt) + [
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $status = (int) $response->getStatusCode();

        if ($status === 429) {
            $this->logger()->warning('http.throttled', $this->context($request, $status, $startedAt));
        } elseif ($status >= 500) {
            $this->logger()->error('http.server_error', $this->context($request, $status, $startedAt));
        }

        return $response;
    }

    private function context(Request $request, int $status, float $startedAt): array
    {
        return [
            'status' => $status,
            'method' => $request->method(),
            'path' => $request->path(),
            'route_name' => optional($request->route())->getName(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'duration_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'user_agent' => substr((string) $request->userAgent(), 0, 300),
            'request_id' => $request->headers->get('X-Request-Id'),
        ];
    }

    private function logger()
    {
        $channel = (string) config('logging.security_channel', 'stack');

        return Log::channel($channel);
    }
}
