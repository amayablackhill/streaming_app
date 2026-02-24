<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $enabled = (bool) config("security.features.{$feature}", true);

        if (! $enabled) {
            abort(503, 'This operation is temporarily unavailable.');
        }

        /** @var Response $response */
        $response = $next($request);

        return $response;
    }
}
