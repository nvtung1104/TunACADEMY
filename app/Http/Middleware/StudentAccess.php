<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role_id !== 3) {
            return response()->json(['message' => 'Unauthorized. Student access required.'], 403);
        }

        return $next($request);
    }
}
