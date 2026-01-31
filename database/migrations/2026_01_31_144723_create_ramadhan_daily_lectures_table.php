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
        Schema::create('ramadhan_daily_lectures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ramadhan_schedule_id')->constrained('ramadhan_schedules')->cascadeOnDelete();
            $table->integer('day'); // 1 to 30
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('custom_speaker_name')->nullable();
            $table->string('title')->nullable();
            $table->time('time')->default('04:30:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ramadhan_daily_lectures');
    }
};
