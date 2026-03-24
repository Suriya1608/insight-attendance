<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->after('name');
            $table->string('description', 255)->nullable()->after('code');
            $table->enum('saturday_rule', ['none', '2nd_saturday_off', 'all_saturdays_off', 'alternating_saturdays'])
                  ->default('none')
                  ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['code', 'description', 'saturday_rule']);
        });
    }
};
