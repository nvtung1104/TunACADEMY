<?php

namespace Database\Seeders;

use App\Models\LiveParticipant;
use App\Models\LiveSession;
use App\Models\WebrtcSignal;
use Illuminate\Database\Seeder;

class WebrtcSignalSeeder extends Seeder
{
    public function run(): void
    {
        $session = LiveSession::first();
        $participants = LiveParticipant::where('session_id', $session->id)->get()->values();

        $from = $participants[0] ?? null;
        $to = $participants[1] ?? null;

        if ($from && $to) {
            WebrtcSignal::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'from_user_id' => $from->user_id,
                    'to_user_id' => $to->user_id,
                    'signal_type' => 'offer',
                ],
                [
                    'payload' => ['sdp' => 'fake-offer-sdp'],
                    'processed_at' => now(),
                ]
            );
        }
    }
}