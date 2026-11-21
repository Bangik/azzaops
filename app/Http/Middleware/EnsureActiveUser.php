<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->is_active) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda nonaktif. Hubungi administrator.',
                    'errors' => null,
                ], 403);
            }

            abort(403, 'Akun Anda nonaktif. Hubungi administrator.');
        }

        return $next($request);
    }
}
