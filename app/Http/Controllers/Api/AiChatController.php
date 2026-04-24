<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{AiChatSession, AiChatLog, AiChatRateLimit};
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    private const DAILY_LIMIT = 50;

    public function history(Request $request)
    {
        $sessions = $request->user()->aiChatSessions()->with('logs')->latest()->paginate(20);
        return $this->success($sessions);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message'    => 'required|string|max:2000',
            'session_id' => 'nullable|exists:ai_chat_sessions,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $rateLimit = AiChatRateLimit::firstOrCreate(
            ['student_id' => $request->user()->id, 'date' => today()],
            ['message_count' => 0, 'token_count' => 0]
        );
        abort_if($rateLimit->message_count >= self::DAILY_LIMIT, 429, 'Ban da dat gioi han ' . self::DAILY_LIMIT . ' tin nhan hom nay');

        $session = $request->session_id
            ? AiChatSession::findOrFail($request->session_id)
            : AiChatSession::create([
                'student_id' => $request->user()->id,
                'subject_id' => $request->subject_id,
                'title'      => mb_substr($request->message, 0, 50),
            ]);

        AiChatLog::create(['session_id' => $session->id, 'role' => 'user', 'content' => $request->message]);

        // TODO: tich hop Claude API
        $aiResponse = 'Chuc nang AI dang duoc phat trien.';

        AiChatLog::create(['session_id' => $session->id, 'role' => 'assistant', 'content' => $aiResponse]);
        $rateLimit->increment('message_count');

        return $this->success(['session_id' => $session->id, 'reply' => $aiResponse]);
    }

    public function clearHistory(Request $request)
    {
        $request->user()->aiChatSessions()->delete();
        return $this->success(null, 'Da xoa lich su chat');
    }
}
