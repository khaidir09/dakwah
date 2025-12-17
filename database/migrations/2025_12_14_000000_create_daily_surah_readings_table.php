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
        Schema::create('daily_surah_readings', function (Blueprint $table) {
            $table->id();
            $table->string('day'); // 'monday', 'tuesday', etc.
            $table->string('prayer'); // 'subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'
            $table->string('surah_name');
            $table->string('surah_verse')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_surah_readings');
    }
};
