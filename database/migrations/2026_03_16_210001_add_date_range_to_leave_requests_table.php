<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->date('from_date')->nullable()->after('request_date');
            $table->date('to_date')->nullable()->after('from_date');
            $table->unsignedTinyInteger('total_days')->default(1)->after('to_date');
            $table->unsignedTinyInteger('cl_days')->default(0)->after('total_days');
            $table->unsignedTinyInteger('lop_days')->default(0)->after('cl_days');
        });

        // Back-fill existing rows: single-date requests
        DB::statement("
            UPDATE leave_requests SET
                from_date  = request_date,
                to_date    = request_date,
                total_days = 1,
                cl_days    = CASE WHEN leave_type = 'CL'  THEN 1 ELSE 0 END,
                lop_days   = CASE WHEN leave_type = 'LOP' THEN 1 ELSE 0 END
        ");
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['from_date', 'to_date', 'total_days', 'cl_days', 'lop_days']);
        });
    }
};
