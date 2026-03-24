<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timesheet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->foreignId('parent_id')->nullable()->constrained('timesheet_comments')->nullOnDelete();
            $table->timestamps();

            $table->index(['timesheet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_comments');
    }
};
