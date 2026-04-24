<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role_id !== 2) {
            return response()->json(['message' => 'Unauthorized. Teacher access required.'], 403);
        }

        return $next($request);
    }
}
