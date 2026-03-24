<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Change saturday_rule from ENUM to VARCHAR to support new values
        DB::statement("ALTER TABLE departments MODIFY COLUMN saturday_rule VARCHAR(50) NOT NULL DEFAULT 'none'");

        Schema::table('departments', function (Blueprint $table) {
            $table->decimal('cl_per_month', 4, 1)->default(1)->after('has_saturday_leave');
            $table->tinyInteger('permissions_per_month')->default(2)->after('cl_per_month');
            $table->tinyInteger('hours_per_permission')->default(2)->after('permissions_per_month');
            $table->boolean('leave_rule_active')->default(true)->after('hours_per_permission');
            $table->text('leave_rule_notes')->nullable()->after('leave_rule_active');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['cl_per_month', 'permissions_per_month', 'hours_per_permission', 'leave_rule_active', 'leave_rule_notes']);
        });

        DB::statement("ALTER TABLE departments MODIFY COLUMN saturday_rule ENUM('none','2nd_saturday_off','all_saturdays_off','alternating_saturdays') NOT NULL DEFAULT 'none'");
    }
};
