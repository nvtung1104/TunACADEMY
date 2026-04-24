<?php
namespace App\Http\Controllers\Api\Live;

use App\Http\Controllers\Controller;
use App\Models\{LiveSession, WebrtcSignal};
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
            'live_session_id' => $request->live_session_id,
            'from_user_id'    => $request->user()->id,
            'to_user_id'      => $request->to_user_id,
            'type'            => $request->type,
            'payload'         => $request->payload,
        ]);

        return $this->success(null, 'Signal gửi thành công');
    }

    public function joinRoom(Request $request, LiveSession $session)
    {
        abort_unless($session->isLive(), 422, 'Phong chua mo');
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
}
