<?php

namespace Tests\Unit;

use App\Http\Middleware\OperationalEventLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class OperationalEventLoggerTest extends TestCase
{
    public function test_it_logs_throttled_responses(): void
    {
        config()->set('logging.security_channel', 'stack');
        Log::shouldReceive('channel')->once()->with('stack')->andReturnSelf();
        Log::shouldReceive('warning')->once();

        $middleware = new OperationalEventLogger();
        $request = Request::create('/admin/tmdb/search', 'GET');

        $response = $middleware->handle(
            $request,
            fn () => new Response('too many requests', 429)
        );

        $this->assertSame(429, $response->getStatusCode());
    }

    public function test_it_logs_server_errors(): void
    {
        config()->set('logging.security_channel', 'stack');
        Log::shouldReceive('channel')->once()->with('stack')->andReturnSelf();
        Log::shouldReceive('error')->once();

        $middleware = new OperationalEventLogger();
        $request = Request::create('/admin/health', 'GET');

        $response = $middleware->handle(
            $request,
            fn () => new Response('error', 500)
        );

        $this->assertSame(500, $response->getStatusCode());
    }
}
