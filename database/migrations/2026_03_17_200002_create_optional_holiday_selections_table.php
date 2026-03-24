<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optional_holiday_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('holiday_id')->constrained()->cascadeOnDelete();
            $table->smallInteger('year')->unsigned();
            $table->timestamp('selected_at')->useCurrent();
            $table->engine = 'InnoDB';
            $table->timestamps();

            $table->unique(['user_id', 'holiday_id'], 'opt_sel_user_holiday_unique');
            $table->index(['user_id', 'year'],         'opt_sel_user_year_index');
            $table->index(['holiday_id'],               'opt_sel_holiday_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('optional_holiday_selections');
    }
};
