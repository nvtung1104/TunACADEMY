<?php

namespace Database\Seeders;

use App\Models\LiveChat;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class LiveChatSeeder extends Seeder
{
    public function run(): void
    {
        $session = LiveSession::first();
        $teacher = User::where('email', 'teacher1@tunacademy.com')->first();

        LiveChat::updateOrCreate(
            [
                'session_id' => $session->id,
                'user_id' => $teacher->id,
                'message' => 'Chào cả lớp, hôm nay chúng ta ôn tập hàm số.',
            ],
            [
                'type' => 'text',
                'file_path' => null,
                'is_pinned' => true,
                'target_room' => 'main',
                'breakout_room_id' => null,
            ]
        );
    }
}