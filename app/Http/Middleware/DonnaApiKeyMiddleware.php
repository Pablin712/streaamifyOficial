<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DonnaApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $key = config('services.donna.api_key');

        if (!$key || $request->header('X-Donna-Key') !== $key) {
            return response()->json([
                'success' => false,
                'allowed' => false,
                'reason'  => 'invalid_donna_key',
                'message' => 'Unauthorized Donna API request.',
            ], 401);
        }

        return $next($request);
    }
}
