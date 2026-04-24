<?php
namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()
            ->when($request->boolean('unread_only'), fn($q) => $q->whereNull('read_at'))
            ->latest()->paginate(20);
        return $this->success($notifications);
    }

    public function markAsRead(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->markAsRead();
        return $this->success(null, 'Da doc thong bao');
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return $this->success(null, 'Da doc tat ca thong bao');
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->delete();
        return $this->success(null, 'Xoa thong bao thanh cong');
    }
}
