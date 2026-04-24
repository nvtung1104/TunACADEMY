<?php

namespace Database\Seeders;

use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            NotificationLog::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'channel' => 'in_app',
                    'type' => 'welcome',
                ],
                [
                    'payload' => ['message' => 'Welcome'],
                    'status' => 'success',
                    'error_message' => null,
                    'sent_at' => now(),
                    'retry_count' => 0,
                ]
            );
        }
    }
}