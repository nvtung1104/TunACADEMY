<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Check if lessons table exists and has classroom_id column
        if (Schema::hasTable('lessons') && Schema::hasColumn('lessons', 'classroom_id')) {
            Schema::table('lessons', function (Blueprint $table) {
                // Check if foreign key exists before dropping
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'lessons' 
                    AND COLUMN_NAME = 'classroom_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['classroom_id']);
                }
                
                $table->unsignedBigInteger('classroom_id')->nullable()->change();
                
                // Only create foreign key if classrooms table exists
                if (Schema::hasTable('classrooms')) {
                    $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();
                }

                // Only add visibility column if it doesn't exist
                if (!Schema::hasColumn('lessons', 'visibility')) {
                    $table->enum('visibility', ['public', 'private', 'class', 'selected'])
                        ->default('private')
                        ->after('status');
                }
            });
        }

        // Check if assignments table exists and has classroom_id column
        if (Schema::hasTable('assignments') && Schema::hasColumn('assignments', 'classroom_id')) {
            Schema::table('assignments', function (Blueprint $table) {
                // Check if foreign key exists before dropping
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'assignments' 
                    AND COLUMN_NAME = 'classroom_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['classroom_id']);
                }
                
                $table->unsignedBigInteger('classroom_id')->nullable()->change();
                
                // Only create foreign key if classrooms table exists
                if (Schema::hasTable('classrooms')) {
                    $table->foreign('classroom_id')->references('id')->on('classrooms')->nullOnDelete();
                }

                // deadline nullable for flexible assignments
                if (Schema::hasColumn('assignments', 'deadline')) {
                    $table->dateTime('deadline')->nullable()->change();
                }

                // Only add visibility column if it doesn't exist
                if (!Schema::hasColumn('assignments', 'visibility')) {
                    $table->enum('visibility', ['public', 'private', 'class', 'selected'])
                        ->default('private')
                        ->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lessons')) {
            Schema::table('lessons', function (Blueprint $table) {
                // Check if foreign key exists before dropping
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'lessons' 
                    AND COLUMN_NAME = 'classroom_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['classroom_id']);
                }
                
                if (Schema::hasColumn('lessons', 'classroom_id')) {
                    $table->unsignedBigInteger('classroom_id')->nullable(false)->change();
                    
                    if (Schema::hasTable('classrooms')) {
                        $table->foreign('classroom_id')->references('id')->on('classrooms')->cascadeOnDelete();
                    }
                }
                
                if (Schema::hasColumn('lessons', 'visibility')) {
                    $table->dropColumn('visibility');
                }
            });
        }

        if (Schema::hasTable('assignments')) {
            Schema::table('assignments', function (Blueprint $table) {
                // Check if foreign key exists before dropping
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'assignments' 
                    AND COLUMN_NAME = 'classroom_id' 
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['classroom_id']);
                }
                
                if (Schema::hasColumn('assignments', 'classroom_id')) {
                    $table->unsignedBigInteger('classroom_id')->nullable(false)->change();
                    
                    if (Schema::hasTable('classrooms')) {
                        $table->foreign('classroom_id')->references('id')->on('classrooms')->cascadeOnDelete();
                    }
                }
                
                if (Schema::hasColumn('assignments', 'deadline')) {
                    $table->dateTime('deadline')->nullable(false)->change();
                }
                
                if (Schema::hasColumn('assignments', 'visibility')) {
                    $table->dropColumn('visibility');
                }
            });
        }
    }
};
