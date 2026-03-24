<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_regularizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('request_type', 40);
            $table->time('original_punch_in')->nullable();
            $table->time('original_punch_out')->nullable();
            $table->time('requested_punch_in')->nullable();
            $table->time('requested_punch_out')->nullable();
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('l1_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('l2_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('l1_comment')->nullable();
            $table->text('l2_comment')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('l1_actioned_at')->nullable();
            $table->timestamp('l2_actioned_at')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['status', 'date']);
            $table->index(['l1_manager_id', 'status']);
            $table->index(['l2_manager_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_regularizations');
    }
};
