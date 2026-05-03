<?php
namespace App\Http\Controllers\Api\Live;

use App\Http\Controllers\Controller;
use App\Models\{LiveSession, LiveParticipant, WebrtcSignal, LiveChat};
use Illuminate\Http\Request;

class WebRTCController extends Controller
{
    public function iceServers()
    {
        return $this->success([
            'ice_servers' => [
                ['urls' => 'stun:stun.l.google.com:19302'],
                [
                    'urls'       => 'turn:' . env('TURN_HOST', 'localhost') . ':' . env('TURN_PORT', 3478),
                    'username'   => env('TURN_USERNAME', 'tunacademy'),
                    'credential' => env('TURN_PASSWORD', 'secret'),
                ],
            ],
        ]);
    }

    public function signal(Request $request)
    {
        $request->validate([
            'live_session_id' => 'required|exists:live_sessions,id',
            'to_user_id'      => 'required|exists:users,id',
            'type'            => 'required|in:offer,answer,ice-candidate',
            'payload'         => 'required|array',
        ]);

        WebrtcSignal::create([
            'session_id'   => $request->live_session_id,
            'from_user_id' => $request->user()->id,
            'to_user_id'   => $request->to_user_id,
            'signal_type'  => str_replace('-', '_', $request->type),
            'payload'      => $request->payload,
        ]);

        return $this->success(null, 'Signal gửi thành công');
    }

    public function joinRoom(Request $request, LiveSession $session)
    {
        abort_unless($session->isLive() || $request->user()->isAdmin(), 422, 'Phong chua mo');

        $userId = $request->user()->id;
        $isHost = $request->user()->isAdmin()
            || $session->teacher_id === $userId
            || optional($session->classroom)->homeroom_teacher_id === $userId;

        LiveParticipant::updateOrCreate(
            ['session_id' => $session->id, 'user_id' => $userId],
            ['joined_at' => now(), 'left_at' => null, 'role' => $isHost ? 'host' : 'student']
        );

        $iceData = $this->iceServers()->getData(true);
        return $this->success([
            'room_code'    => $session->room_code,
            'ice_servers'  => $iceData['data']['ice_servers'],
            'participants' => $session->participants()->whereNull('left_at')->with('user:id,name,avatar')->get(),
        ]);
    }

    public function leaveRoom(Request $request, LiveSession $session)
    {
        $session->participants()->where('user_id', $request->user()->id)->whereNull('left_at')->update(['left_at' => now()]);
        return $this->success(null, 'Roi phong thanh cong');
    }

    public function sessionInfo(LiveSession $session)
    {
        return $this->success([
            'session'      => $session->load(['classroom.grade', 'classroom.homeroomTeacher:id,name', 'subject:id,name,color', 'teacher:id,name']),
            'participants' => $session->participants()->whereNull('left_at')->with('user:id,name,avatar')->get(),
        ]);
    }

    public function pollSignals(Request $request, LiveSession $session)
    {
        $signals = WebrtcSignal::where('session_id', $session->id)
            ->where('to_user_id', $request->user()->id)
            ->whereNull('processed_at')
            ->orderBy('id')
            ->get();

        if ($signals->isNotEmpty()) {
            WebrtcSignal::whereIn('id', $signals->pluck('id'))->update(['processed_at' => now()]);
        }

        // Map signal_type (DB enum) back to frontend 'type' field; also restore hyphen in ice-candidate
        $mapped = $signals->map(fn($s) => [
            'from_user_id' => $s->from_user_id,
            'type'         => str_replace('_', '-', $s->signal_type),
            'payload'      => $s->payload,
        ]);

        return $this->success($mapped);
    }

    public function getMessages(Request $request, LiveSession $session)
    {
        $messages = $session->chats()
            ->with('user:id,name')
            ->when($request->last_id, fn($q) => $q->where('id', '>', $request->last_id))
            ->orderBy('id')
            ->limit(50)
            ->get();

        return $this->success($messages);
    }

    public function sendMessage(Request $request, LiveSession $session)
    {
        $data = $request->validate(['message' => 'required|string|max:2000']);

        $chat = $session->chats()->create([
            'user_id' => $request->user()->id,
            'message' => $data['message'],
            'type'    => 'text',
        ]);

        return $this->success($chat->load('user:id,name'), 'OK', 201);
    }
}
