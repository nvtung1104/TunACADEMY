<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if user is already authenticated
        if ($request->user()) {
            return $next($request);
        }

        // Get token from header
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized. API token required.',
            ], 401);
        }

        // Validate token against user's sanctum tokens
        $user = \App\Models\User::whereHas('tokens', function ($query) use ($token) {
            $query->where('token', hash('sha256', $token));
        })->first();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized. Invalid API token.',
            ], 401);
        }

        // Set the authenticated user
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
