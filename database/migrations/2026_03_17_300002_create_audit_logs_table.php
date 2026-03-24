<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();          // the user record being acted upon
            $table->string('module_name', 100);                         // e.g. "Optional Holiday", "Leave Request"
            $table->unsignedBigInteger('record_id')->nullable();        // PK of the affected record
            $table->enum('action_type', ['create', 'update', 'delete', 'cancel']);
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();     // auth user who triggered action
            $table->dateTime('performed_at');
            $table->string('ip_address', 45)->nullable();

            // Foreign keys (nullable so logs survive user deletion)
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();

            // Indexes for fast filtering
            $table->index(['module_name', 'action_type']);
            $table->index('performed_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
