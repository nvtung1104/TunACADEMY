<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('live_sessions') && Schema::hasColumn('live_sessions', 'subject_id')) {
            Schema::table('live_sessions', function (Blueprint $table) {
                // Check if foreign key exists before dropping
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'live_sessions' 
                    AND COLUMN_NAME = 'subject_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['subject_id']);
                }

                $table->unsignedBigInteger('subject_id')->nullable()->change();
                
                // Only create foreign key if subjects table exists
                if (Schema::hasTable('subjects')) {
                    $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
                }

                if (Schema::hasColumn('live_sessions', 'scheduled_at')) {
                    $table->dateTime('scheduled_at')->nullable()->change();
                }

                // Flag: permanent room tied to a classroom (not a one-off scheduled session)
                if (!Schema::hasColumn('live_sessions', 'is_permanent')) {
                    $table->boolean('is_permanent')->default(false)->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('live_sessions')) {
            Schema::table('live_sessions', function (Blueprint $table) {
                // Check if foreign key exists before dropping
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'live_sessions' 
                    AND COLUMN_NAME = 'subject_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['subject_id']);
                }
                
                if (Schema::hasColumn('live_sessions', 'subject_id')) {
                    $table->unsignedBigInteger('subject_id')->nullable(false)->change();
                    
                    if (Schema::hasTable('subjects')) {
                        $table->foreign('subject_id')->references('id')->on('subjects');
                    }
                }
                
                if (Schema::hasColumn('live_sessions', 'scheduled_at')) {
                    $table->dateTime('scheduled_at')->nullable(false)->change();
                }
                
                if (Schema::hasColumn('live_sessions', 'is_permanent')) {
                    $table->dropColumn('is_permanent');
                }
            });
        }
    }
};
