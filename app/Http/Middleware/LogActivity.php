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
            $userId    = $request->user()->id;
            $userEmail = $request->user()->email;
            $method    = $request->method();
            $path      = $request->path();
            $ip        = $request->ip();
            $agent     = $request->userAgent();
            $status    = $response->status();

            defer(function () use ($userId, $userEmail, $method, $path, $ip, $agent, $status) {
                Log::channel('activity')->info('User Activity', [
                    'user_id'    => $userId,
                    'user_email' => $userEmail,
                    'method'     => $method,
                    'path'       => $path,
                    'ip'         => $ip,
                    'user_agent' => $agent,
                    'status_code'=> $status,
                    'timestamp'  => now(),
                ]);
            });
        }

        return $response;
    }
}
