<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->canAccessAdminPanel()) {
            abort(403, 'Admin access is restricted.');
        }

        return $next($request);
    }
}
