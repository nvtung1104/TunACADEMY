<?php
namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreLiveSessionRequest;
use App\Http\Resources\Live\LiveSessionResource;
use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiveController extends Controller
{
    public function index(Request $request)
    {
        $sessions = LiveSession::with(['classroom', 'subject'])
            ->where('teacher_id', $request->user()->id)
            ->latest('scheduled_at')->paginate(20);
        return LiveSessionResource::collection($sessions);
    }

    public function store(StoreLiveSessionRequest $request)
    {
        $session = LiveSession::create([
            ...$request->validated(),
            'teacher_id' => $request->user()->id,
            'room_code'  => strtoupper(Str::random(6)),
        ]);
        return $this->success(new LiveSessionResource($session->load(['classroom', 'subject'])), 'Tạo buổi học thành công', 201);
    }

    public function show(Request $request, LiveSession $session)
    {
        $this->gate($request, $session);
        return $this->success(new LiveSessionResource($session->load(['classroom', 'subject', 'participants.user'])));
    }

    public function update(StoreLiveSessionRequest $request, LiveSession $session)
    {
        $this->gate($request, $session);
        $session->update($request->validated());
        return $this->success(new LiveSessionResource($session->fresh()), 'Cập nhật thành công');
    }

    public function destroy(Request $request, LiveSession $session)
    {
        $this->gate($request, $session);
        $session->delete();
        return $this->success(null, 'Xóa buổi học thành công');
    }

    public function start(Request $request, LiveSession $session)
    {
        $this->gate($request, $session);
        $session->update(['status' => 'live', 'started_at' => now()]);
        return $this->success(new LiveSessionResource($session->fresh()), 'Buổi học đã bắt đầu');
    }

    public function end(Request $request, LiveSession $session)
    {
        $this->gate($request, $session);
        $session->update(['status' => 'ended', 'ended_at' => now()]);
        $session->participants()->whereNull('left_at')->update(['left_at' => now()]);
        return $this->success(new LiveSessionResource($session->fresh()), 'Kết thúc buổi học');
    }

    private function gate(Request $request, LiveSession $session): void
    {
        abort_unless($session->teacher_id === $request->user()->id, 403, 'Không có quyền');
    }
}
