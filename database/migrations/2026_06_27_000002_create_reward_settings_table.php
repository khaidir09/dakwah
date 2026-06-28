<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('amount')->default(50000); // nominal rupiah reward
            $table->unsignedInteger('min_xp')->default(501);   // threshold XP minimal (selaras Khadam Syaikhuna)
            $table->boolean('is_active')->default(true);       // toggle program
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_settings');
    }
};
