<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('optional_holiday_selections', function (Blueprint $table) {
            $table->enum('status', ['active', 'cancelled'])->default('active')->after('selected_at');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('status');
            $table->dateTime('cancelled_at')->nullable()->after('cancelled_by');

            $table->foreign('cancelled_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('optional_holiday_selections', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['status', 'cancelled_by', 'cancelled_at']);
        });
    }
};
