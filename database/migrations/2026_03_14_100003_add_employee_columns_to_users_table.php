<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code', 20)->nullable()->unique()->after('department_id');
            $table->string('mobile', 15)->nullable()->after('employee_code');
            $table->enum('emp_status', ['active', 'inactive'])->default('active')->after('mobile');
            $table->date('dob')->nullable()->after('emp_status');
            $table->date('doj')->nullable()->after('dob');
            $table->unsignedBigInteger('level1_manager_id')->nullable()->after('doj');
            $table->unsignedBigInteger('level2_manager_id')->nullable()->after('level1_manager_id');

            $table->foreign('level1_manager_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('level2_manager_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['level1_manager_id']);
            $table->dropForeign(['level2_manager_id']);
            $table->dropColumn(['employee_code', 'mobile', 'emp_status', 'dob', 'doj', 'level1_manager_id', 'level2_manager_id']);
        });
    }
};
