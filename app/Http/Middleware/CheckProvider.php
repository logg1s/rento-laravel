<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProvider
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || auth()->user()->role !== 'provider') {
            return response()->json(['message' => 'Unauthorized. Provider access required.'], 403);
        }

        return $next($request);
    }
}