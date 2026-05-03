<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Thêm parent_email vào users, xóa zalo fields
        Schema::table('users', function (Blueprint $table) {
            $table->string('parent_email')->nullable()->after('parent_phone')
                ->comment('Gmail phụ huynh nhận thông báo');
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'zalo_user_id')) {
                $table->dropColumn('zalo_user_id');
            }
            if (Schema::hasColumn('users', 'zalo_notification_enabled')) {
                $table->dropColumn('zalo_notification_enabled');
            }
        });

        // Xóa các bản ghi notification_logs có channel='zalo' trước khi đổi enum
        DB::table('notification_logs')->where('channel', 'zalo')->delete();

        // Cập nhật enum channel: bỏ 'zalo'
        DB::statement("ALTER TABLE notification_logs MODIFY COLUMN channel ENUM('email', 'push', 'in_app') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('parent_email');
            $table->string('zalo_user_id', 50)->nullable();
            $table->boolean('zalo_notification_enabled')->default(true);
        });

        DB::statement("ALTER TABLE notification_logs MODIFY COLUMN channel ENUM('email', 'zalo', 'push', 'in_app') NOT NULL");
    }
};
