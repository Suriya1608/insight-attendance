<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // 'leave' or 'permission'
            $table->enum('request_type', ['leave', 'permission']);

            // Only for leave requests: CL / LOP / saturday_leave
            $table->enum('leave_type', ['CL', 'LOP', 'saturday_leave'])->nullable();

            // Date of leave or permission
            $table->date('request_date');

            // Only for permission requests (hours: 0.5 – 2)
            $table->decimal('permission_hours', 4, 2)->nullable();

            $table->text('reason');
            $table->string('attachment')->nullable();

            // pending → approved_l1 → approved
            //         → rejected
            $table->enum('status', ['pending', 'approved_l1', 'approved', 'rejected'])
                  ->default('pending');

            // Snapshot of managers at submission time
            $table->unsignedBigInteger('l1_manager_id')->nullable();
            $table->unsignedBigInteger('l2_manager_id')->nullable();

            // L1 action
            $table->text('l1_remarks')->nullable();
            $table->timestamp('l1_actioned_at')->nullable();

            // L2 action
            $table->text('l2_remarks')->nullable();
            $table->timestamp('l2_actioned_at')->nullable();

            // Flagged true when CL was exhausted and auto-converted to LOP
            $table->boolean('auto_lop')->default(false);

            $table->timestamps();

            $table->foreign('l1_manager_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('l2_manager_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['user_id', 'request_date']);
            $table->index(['l1_manager_id', 'status']);
            $table->index(['l2_manager_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
