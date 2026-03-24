<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY status ENUM('present', 'absent', 'holiday', 'optional_holiday', 'sunday', 'leave', 'half_day', 'pending_regularization') NOT NULL DEFAULT 'present'");
    }

    public function down(): void
    {
        DB::statement("UPDATE attendances SET status = 'present' WHERE status = 'pending_regularization'");
        DB::statement("ALTER TABLE attendances MODIFY status ENUM('present', 'absent', 'holiday', 'optional_holiday', 'sunday', 'leave', 'half_day') NOT NULL DEFAULT 'present'");
    }
};
