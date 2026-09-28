<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return redirect()->route('login')->with('error', 'Unauthorized access. Administrator privilege required.');
        }

        if (auth()->user()->is_disabled) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your admin account has been disabled.');
        }

        return $next($request);
    }
}
