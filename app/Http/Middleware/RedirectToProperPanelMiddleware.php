<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectToProperPanelMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        if (!$request->user()) {
            return $next($request);
        }

        // Jika user sudah login, periksa role
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // User login tapi bukan admin → blokir
        abort(403);
    }
}
