<?php

namespace Database\Seeders;

use App\Models\AiChatLog;
use App\Models\AiChatSession;
use Illuminate\Database\Seeder;

class AiChatLogSeeder extends Seeder
{
    public function run(): void
    {
        $sessions = AiChatSession::all();

        foreach ($sessions as $session) {
            AiChatLog::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'role' => 'user',
                    'content' => 'Giải thích giúp em hàm số bậc nhất là gì?',
                ],
                [
                    'tokens_used' => 50,
                    'response_time_ms' => 1200,
                    'flagged' => false,
                ]
            );

            AiChatLog::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'role' => 'assistant',
                    'content' => 'Hàm số bậc nhất có dạng y = ax + b với a khác 0.',
                ],
                [
                    'tokens_used' => 100,
                    'response_time_ms' => 1400,
                    'flagged' => false,
                ]
            );
        }
    }
}