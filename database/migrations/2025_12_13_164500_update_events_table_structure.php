<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['province', 'city', 'district', 'village']);

            // Add new columns
            $table->char('province_code', 2)->nullable()->after('date');
            $table->char('city_code', 4)->nullable()->after('province_code');
            $table->char('district_code', 7)->nullable()->after('city_code');
            $table->char('village_code', 10)->nullable()->after('district_code');

            $table->string('image')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('province', 20)->nullable();
            $table->string('city', 20)->nullable();
            $table->string('district', 20)->nullable();
            $table->string('village', 20)->nullable();

            $table->dropColumn(['province_code', 'city_code', 'district_code', 'village_code', 'image']);
        });
    }
};
