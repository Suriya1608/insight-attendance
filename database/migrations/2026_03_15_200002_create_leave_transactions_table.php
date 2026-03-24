<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('leave_type', ['CL', 'permission', 'saturday_leave']);
            $table->enum('transaction_type', ['credit', 'debit', 'lapse']);
            $table->decimal('amount', 8, 2);
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->date('date');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'leave_type', 'year', 'month']);
            $table->index(['leave_type', 'transaction_type', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_transactions');
    }
};
