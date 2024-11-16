<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->user() || ! auth()->user()->isStudent()) {
            return redirect()->route('filament.student.auth.login');
        }

        return $next($request);
    }
}