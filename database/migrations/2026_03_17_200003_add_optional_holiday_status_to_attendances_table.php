<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend the attendance status enum to include 'optional_holiday'
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','holiday','optional_holiday','sunday','leave','half_day') NOT NULL DEFAULT 'present'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent','holiday','sunday','leave','half_day') NOT NULL DEFAULT 'present'");
    }
};
