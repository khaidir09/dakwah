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
        Schema::table('teachers', function (Blueprint $table) {
            $table->tinyInteger('wafat_hijriah_day')->nullable()->after('wafat_hijriah');
            $table->tinyInteger('wafat_hijriah_month')->nullable()->after('wafat_hijriah_day');
        });

        // Migrate existing data
        $teachers = DB::table('teachers')->whereNotNull('wafat_hijriah')->get();

        $months = [
            'muharram' => 1,
            'safar' => 2,
            'rabiul awal' => 3,
            'rabiul akhir' => 4,
            'jumadil awal' => 5,
            'jumadil akhir' => 6,
            'rajab' => 7,
            'syakban' => 8,
            'ramadhan' => 9,
            'syawal' => 10,
            'zulkaidah' => 11,
            'zulhijah' => 12,
        ];

        foreach ($teachers as $teacher) {
            $hijriStr = strtolower($teacher->wafat_hijriah);
            // Regex to match "17 Syakban" or "17 Syakban 1447"
            if (preg_match('/(\d{1,2})\s+([a-z\s]+)/', $hijriStr, $matches)) {
                $day = (int)$matches[1];
                $monthStr = trim($matches[2]);

                // Try to find the month in the map
                $month = null;
                foreach ($months as $name => $number) {
                    if (str_contains($monthStr, $name)) {
                        $month = $number;
                        break;
                    }
                }

                if ($month && $day > 0 && $day <= 30) {
                    DB::table('teachers')->where('id', $teacher->id)->update([
                        'wafat_hijriah_day' => $day,
                        'wafat_hijriah_month' => $month,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['wafat_hijriah_day', 'wafat_hijriah_month']);
        });
    }
};
