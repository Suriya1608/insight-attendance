<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['draft', 'submitted', 'approved_l1', 'approved', 'rejected'])->default('draft');
            $table->foreignId('l1_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('l2_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('l1_remarks')->nullable();
            $table->text('l2_remarks')->nullable();
            $table->timestamp('l1_actioned_at')->nullable();
            $table->timestamp('l2_actioned_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date']);
            $table->index(['l1_manager_id', 'status']);
            $table->index(['l2_manager_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
