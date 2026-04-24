<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Database\Seeder;

class UserDeviceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            UserDevice::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'platform' => 'web',
                ],
                [
                    'device_token' => 'device-token-' . $user->id,
                    'device_name' => 'Chrome on Windows',
                    'last_active_at' => now(),
                ]
            );
        }
    }
}