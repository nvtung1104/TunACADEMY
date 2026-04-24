<?php
namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Http\Resources\Live\LiveSessionResource;
use App\Models\{LiveSession, LiveParticipant};
use Illuminate\Http\Request;

class LiveController extends Controller
{
    public function index(Request $request)
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        $sessions = LiveSession::with(['subject', 'teacher'])
            ->whereIn('classroom_id', $classroomIds)
            ->whereIn('status', ['scheduled', 'live'])
            ->latest('scheduled_at')->paginate(20);
        return LiveSessionResource::collection($sessions);
    }

    public function show(Request $request, LiveSession $session)
    {
        $this->checkAccess($request, $session);
        return $this->success(new LiveSessionResource($session->load(['classroom', 'subject', 'teacher'])));
    }

    public function join(Request $request, LiveSession $session)
    {
        $this->checkAccess($request, $session);
        abort_unless($session->isLive(), 422, 'Buổi học chưa bắt đầu');
        abort_if($session->participants()->whereNull('left_at')->count() >= $session->max_participants, 422, 'Phòng học đã đầy');

        LiveParticipant::updateOrCreate(
            ['live_session_id' => $session->id, 'user_id' => $request->user()->id],
            ['joined_at' => now(), 'left_at' => null]
        );
        return $this->success(['room_code' => $session->room_code], 'Tham gia thành công');
    }

    public function leave(Request $request, LiveSession $session)
    {
        LiveParticipant::where(['live_session_id' => $session->id, 'user_id' => $request->user()->id])
            ->whereNull('left_at')->update(['left_at' => now()]);
        return $this->success(null, 'Rời phòng thành công');
    }

    private function checkAccess(Request $request, LiveSession $session): void
    {
        $classroomIds = $request->user()->classrooms()->pluck('classrooms.id');
        abort_unless($classroomIds->contains($session->classroom_id), 403, 'Không có quyền truy cập');
    }
}
