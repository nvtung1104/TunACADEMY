<?php

namespace Database\Seeders;

use App\Models\BreakoutRoom;
use App\Models\LiveSession;
use Illuminate\Database\Seeder;

class BreakoutRoomSeeder extends Seeder
{
    public function run(): void
    {
        $session = LiveSession::first();

        for ($i = 1; $i <= 2; $i++) {
            BreakoutRoom::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'room_index' => $i,
                ],
                [
                    'name' => 'Nhóm ' . $i,
                    'topic' => 'Thảo luận bài tập nhóm ' . $i,
                    'duration_minutes' => 10,
                    'started_at' => now(),
                    'ended_at' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}