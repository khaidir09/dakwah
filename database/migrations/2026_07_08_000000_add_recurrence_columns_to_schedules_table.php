<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('recurrence_type')->default('weekly')->after('hari');
            $table->string('calendar_system')->default('gregorian')->after('recurrence_type');
            $table->string('week_of_month')->nullable()->after('calendar_system');
            $table->string('week_of_month_secondary')->nullable()->after('week_of_month');
            $table->unsignedTinyInteger('day_of_month')->nullable()->after('week_of_month_secondary');
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn([
                'recurrence_type',
                'calendar_system',
                'week_of_month',
                'week_of_month_secondary',
                'day_of_month',
            ]);
        });
    }
};
