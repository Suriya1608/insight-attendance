<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE timesheets MODIFY status ENUM('draft', 'submitted', 'approved_l1', 'pending_l1', 'pending_l2', 'approved', 'rejected') NOT NULL DEFAULT 'draft'");
        }

        DB::table('timesheets')->where('status', 'submitted')->update(['status' => 'pending_l1']);
        DB::table('timesheets')->where('status', 'approved_l1')->update(['status' => 'pending_l2']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE timesheets MODIFY status ENUM('draft', 'pending_l1', 'pending_l2', 'approved', 'rejected') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        DB::table('timesheets')->where('status', 'pending_l1')->update(['status' => 'submitted']);
        DB::table('timesheets')->where('status', 'pending_l2')->update(['status' => 'approved_l1']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE timesheets MODIFY status ENUM('draft', 'submitted', 'approved_l1', 'approved', 'rejected') NOT NULL DEFAULT 'draft'");
        }
    }
};
