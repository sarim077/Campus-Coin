<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in to access your student dashboard.');
        }

        if (auth()->user()->is_disabled) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has been disabled by an administrator.');
        }

        return $next($request);
    }
}
