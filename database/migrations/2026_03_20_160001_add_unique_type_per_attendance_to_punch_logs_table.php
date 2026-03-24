<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove any pre-existing duplicate logs (keep the earliest per attendance+type)
        // so the unique constraint can be applied cleanly to existing data.
        DB::statement('
            DELETE pl FROM punch_logs pl
            INNER JOIN punch_logs pl2
                ON pl2.attendance_id = pl.attendance_id
               AND pl2.type         = pl.type
               AND pl2.id           < pl.id
        ');

        Schema::table('punch_logs', function (Blueprint $table) {
            // Add the unique index first — MySQL will use it as the FK backing index,
            // allowing the old plain index to be dropped safely afterwards.
            $table->unique(['attendance_id', 'type'], 'punch_logs_attendance_type_unique');
        });

        Schema::table('punch_logs', function (Blueprint $table) {
            // Now safe to drop: the unique index above satisfies the FK requirement.
            $table->dropIndex('punch_logs_attendance_index');
        });
    }

    public function down(): void
    {
        Schema::table('punch_logs', function (Blueprint $table) {
            $table->dropUnique('punch_logs_attendance_type_unique');
            $table->index('attendance_id', 'punch_logs_attendance_index');
        });
    }
};
