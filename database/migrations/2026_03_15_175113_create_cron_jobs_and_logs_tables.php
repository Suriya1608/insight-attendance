<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cron_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();           // machine-readable slug
            $table->string('name');                         // display name
            $table->string('command');                      // artisan command signature
            $table->text('description')->nullable();
            $table->string('schedule_display', 150);        // human-readable schedule
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->enum('last_run_status', ['success', 'failed'])->nullable();
            $table->unsignedInteger('last_run_duration_ms')->nullable();
            $table->engine = 'InnoDB';
            $table->timestamps();
        });

        Schema::create('cron_job_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cron_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('trigger_type', ['manual', 'scheduled'])->default('manual');
            $table->enum('status', ['running', 'success', 'failed'])->default('running');
            $table->longText('output')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->engine = 'InnoDB';
            $table->timestamps();

            $table->index(['cron_job_id', 'started_at']);
            $table->index('triggered_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cron_job_logs');
        Schema::dropIfExists('cron_jobs');
    }
};
