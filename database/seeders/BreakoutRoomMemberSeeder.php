<?php

namespace Database\Seeders;

use App\Models\BreakoutRoom;
use App\Models\BreakoutRoomMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class BreakoutRoomMemberSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = BreakoutRoom::orderBy('room_index')->get()->values();
        $students = User::whereHas('role', fn($q) => $q->where('name', 'student'))->get()->values();

        foreach ($students as $index => $student) {
            $room = $rooms[$index % max($rooms->count(), 1)] ?? null;
            if (!$room) continue;

            BreakoutRoomMember::updateOrCreate(
                [
                    'breakout_room_id' => $room->id,
                    'student_id' => $student->id,
                ],
                [
                    'assigned_at' => now(),
                    'joined_at' => now(),
                    'left_at' => null,
                ]
            );
        }
    }
}