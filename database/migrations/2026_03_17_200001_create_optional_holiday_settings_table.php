<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optional_holiday_settings', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year')->unsigned();
            $table->tinyInteger('max_allowed')->unsigned()->default(2);
            $table->string('description', 500)->nullable();
            $table->boolean('status')->default(true);
            $table->engine = 'InnoDB';
            $table->timestamps();

            $table->unique('year', 'opt_holiday_settings_year_unique');
            $table->index('year', 'opt_holiday_settings_year_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('optional_holiday_settings');
    }
};
