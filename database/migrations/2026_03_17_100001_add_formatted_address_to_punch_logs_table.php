<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('punch_logs', function (Blueprint $table) {
            $table->string('formatted_address', 500)->nullable()->after('location_label');
        });
    }

    public function down(): void
    {
        Schema::table('punch_logs', function (Blueprint $table) {
            $table->dropColumn('formatted_address');
        });
    }
};
