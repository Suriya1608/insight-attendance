<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('month');                        // 1–12
            $table->smallInteger('year');
            $table->tinyInteger('total_days');                   // calendar days in month
            $table->tinyInteger('working_days');                 // excl. sundays & national holidays
            $table->decimal('present_days', 5, 1)->default(0);  // 0.5 for half-day
            $table->decimal('lop_days', 5, 2)->default(0);
            $table->decimal('permission_hours', 6, 2)->default(0);
            $table->tinyInteger('optional_holidays_taken')->default(0);
            $table->decimal('salary', 10, 2);                   // monthly salary snapshot
            $table->decimal('per_day_salary', 12, 4);
            $table->decimal('lop_amount', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->foreignId('generated_by')->constrained('users');
            $table->timestamps();

            $table->unique(['employee_id', 'month', 'year']);
            $table->index(['month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
