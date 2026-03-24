<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            // Set true for the "HR Recruiting" department to credit 1 optional Saturday leave/month
            $table->boolean('has_saturday_leave')->default(false)->after('saturday_rule');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('has_saturday_leave');
        });
    }
};
