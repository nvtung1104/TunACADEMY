<?php

namespace App\Http\Middleware;

use App\Models\LiveSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebRTCSignal
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Validate WebRTC signal payload
        if ($request->isMethod('post')) {
            $sessionId = $request->input('session_id');
            $fromUserId = $request->input('from_user_id');
            $signalType = $request->input('type'); // offer, answer, candidate

            // Verify session exists
            $session = LiveSession::find($sessionId);
            if (!$session) {
                return response()->json(['message' => 'Session not found.'], 404);
            }

            // Verify signal type
            if (!in_array($signalType, ['offer', 'answer', 'candidate'])) {
                return response()->json(['message' => 'Invalid signal type.'], 400);
            }

            // Verify from_user_id matches authenticated user
            if ($fromUserId !== $request->user()->id) {
                return response()->json(['message' => 'Signal from_user_id does not match authenticated user.'], 403);
            }

            // Verify user is part of the session
            $isParticipant = $session->participants()->where('user_id', $request->user()->id)->exists();
            $isHost = $session->user_id === $request->user()->id;

            if (!$isParticipant && !$isHost) {
                return response()->json(['message' => 'User is not part of this session.'], 403);
            }

            // Rate limiting for signals (prevent spam)
            $cacheKey = "webrtc_signal_{$sessionId}_{$request->user()->id}";
            if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                return response()->json(['message' => 'Too many signals. Please wait.'], 429);
            }

            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addSeconds(1));
        }

        return $next($request);
    }
}
