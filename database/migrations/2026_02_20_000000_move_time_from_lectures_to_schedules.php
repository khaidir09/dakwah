<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ramadhan_schedules', function (Blueprint $table) {
            $table->time('time')->default('04:30:00')->after('gregorian_start_date');
        });

        // Migrate data
        $schedules = DB::table('ramadhan_schedules')->get();
        foreach ($schedules as $schedule) {
            $firstLecture = DB::table('ramadhan_daily_lectures')
                ->where('ramadhan_schedule_id', $schedule->id)
                ->whereNotNull('time')
                ->first();

            if ($firstLecture) {
                DB::table('ramadhan_schedules')
                    ->where('id', $schedule->id)
                    ->update(['time' => $firstLecture->time]);
            }
        }

        Schema::table('ramadhan_daily_lectures', function (Blueprint $table) {
            $table->dropColumn('time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ramadhan_daily_lectures', function (Blueprint $table) {
            $table->time('time')->default('04:30:00');
        });

        // Migrate data back
        $schedules = DB::table('ramadhan_schedules')->get();
        foreach ($schedules as $schedule) {
             DB::table('ramadhan_daily_lectures')
                ->where('ramadhan_schedule_id', $schedule->id)
                ->update(['time' => $schedule->time]);
        }

        Schema::table('ramadhan_schedules', function (Blueprint $table) {
            $table->dropColumn('time');
        });
    }
};
