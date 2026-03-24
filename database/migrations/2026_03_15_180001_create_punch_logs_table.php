<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('punch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->timestamp('punched_at');
            $table->string('ip_address', 45)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_label', 255)->nullable();
            $table->engine = 'InnoDB';
            $table->timestamps();

            $table->index(['user_id', 'punched_at'], 'punch_logs_user_time_index');
            $table->index('attendance_id',            'punch_logs_attendance_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('punch_logs');
    }
};
