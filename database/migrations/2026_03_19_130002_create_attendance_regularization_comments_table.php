<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_regularization_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_regularization_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->foreignId('parent_id')->nullable()->constrained('attendance_regularization_comments')->nullOnDelete();
            $table->timestamps();

            $table->foreign('attendance_regularization_id', 'arc_regularization_fk')
                ->references('id')
                ->on('attendance_regularizations')
                ->cascadeOnDelete();

            $table->index(['attendance_regularization_id', 'created_at'], 'attendance_regularization_comments_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_regularization_comments');
    }
};
