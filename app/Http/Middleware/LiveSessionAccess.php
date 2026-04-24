<?php

namespace App\Http\Middleware;

use App\Models\LiveSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LiveSessionAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->route('session_id') ?? $request->route('live_session');

        if (!$sessionId) {
            return $next($request);
        }

        $liveSession = LiveSession::find($sessionId);

        if (!$liveSession) {
            return response()->json(['message' => 'Live session not found.'], 404);
        }

        $user = $request->user();

        // Check if user is host of this session
        $isHost = $liveSession->user_id === $user->id;

        // Check if user is participant in this session
        $isParticipant = $liveSession->participants()->where('user_id', $user->id)->exists();

        // Check if user is teacher in the classroom
        $isTeacher = $liveSession->classroom->teachers()->where('user_id', $user->id)->exists();

        if (!$isHost && !$isParticipant && !$isTeacher) {
            return response()->json(['message' => 'You do not have access to this live session.'], 403);
        }

        $request->merge(['liveSession' => $liveSession]);

        return $next($request);
    }
}
