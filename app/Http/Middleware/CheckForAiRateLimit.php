<?php

namespace App\Http\Middleware;

use App\Models\AiChatRateLimit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckForAiRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $next($request);
        }

        $userId = $request->user()->id;
        $today = now()->startOfDay();

        // Get or create rate limit record
        $rateLimit = AiChatRateLimit::firstOrCreate(
            [
                'user_id' => $userId,
                'date' => $today,
            ],
            [
                'message_count' => 0,
                'last_message_at' => null,
            ]
        );

        // Define rate limits (adjust as needed)
        $maxMessagesPerDay = 100;
        $minSecondsBetweenMessages = 1;

        // Check daily limit
        if ($rateLimit->message_count >= $maxMessagesPerDay) {
            return response()->json([
                'message' => 'Daily AI chat limit exceeded. Please try again tomorrow.',
                'remaining' => 0,
            ], 429);
        }

        // Check time between messages
        if ($rateLimit->last_message_at && $rateLimit->last_message_at->diffInSeconds(now()) < $minSecondsBetweenMessages) {
            return response()->json([
                'message' => 'Please wait before sending another message.',
                'retry_after' => $minSecondsBetweenMessages,
            ], 429);
        }

        $request->merge([
            'ai_rate_limit' => $rateLimit,
            'ai_messages_remaining' => $maxMessagesPerDay - $rateLimit->message_count,
        ]);

        return $next($request);
    }
}
