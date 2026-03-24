<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('leave_type', ['CL', 'permission', 'saturday_leave']);
            $table->smallInteger('year');
            // null for CL (annual row); 1–12 for permission & saturday_leave (monthly rows)
            $table->tinyInteger('month')->nullable();
            $table->decimal('credited', 8, 2)->default(0);
            $table->decimal('used',     8, 2)->default(0);
            $table->decimal('lapsed',   8, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'leave_type', 'year', 'month']);
            $table->index(['user_id', 'leave_type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};
