<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            $logData = [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'method' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $response->status(),
                'timestamp' => now(),
            ];

            Log::channel('activity')->info('User Activity', $logData);
        }

        return $response;
    }
}
