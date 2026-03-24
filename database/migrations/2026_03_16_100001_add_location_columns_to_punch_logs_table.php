<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('punch_logs', function (Blueprint $table) {
            $table->string('suburb',  100)->nullable()->after('location_label');
            $table->string('city',    100)->nullable()->after('suburb');
            $table->string('state',   100)->nullable()->after('city');
            $table->string('country', 100)->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('punch_logs', function (Blueprint $table) {
            $table->dropColumn(['suburb', 'city', 'state', 'country']);
        });
    }
};
