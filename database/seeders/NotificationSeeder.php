<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Notification::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'type' => 'welcome',
                    'title' => 'Chào mừng đến với TunAcademy',
                ],
                [
                    'message' => 'Tài khoản của bạn đã được tạo thành công.',
                    'data' => ['url' => '/dashboard'],
                    'read_at' => null,
                ]
            );
        }
    }
}