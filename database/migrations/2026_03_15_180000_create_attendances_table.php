<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'holiday', 'sunday', 'leave', 'half_day'])->default('present');
            $table->time('punch_in')->nullable();
            $table->time('punch_out')->nullable();
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->text('note')->nullable();
            $table->engine = 'InnoDB';
            $table->timestamps();

            $table->unique(['user_id', 'date'], 'attendances_user_date_unique');
            $table->index(['user_id', 'date'],   'attendances_user_date_index');
            $table->index(['date', 'status'],     'attendances_date_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
