<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('room_code', 20)->unique()->nullable()->after('cover_image');
        });

        // Generate room_codes for existing classrooms
        DB::table('classrooms')->whereNull('room_code')->get()->each(function ($c) {
            DB::table('classrooms')->where('id', $c->id)->update([
                'room_code' => strtoupper(substr(md5($c->name . $c->id . uniqid()), 0, 8)),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn('room_code');
        });
    }
};
