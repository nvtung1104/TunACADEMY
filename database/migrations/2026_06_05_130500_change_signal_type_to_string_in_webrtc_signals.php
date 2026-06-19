<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('webrtc_signals', function (Blueprint $table) {
            $table->string('signal_type', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('webrtc_signals', function (Blueprint $table) {
            $table->enum('signal_type', ['offer', 'answer', 'ice_candidate', 'leave'])->change();
        });
    }
};
