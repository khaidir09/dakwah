<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poster_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('monthly_quota')->default(5); // generate sukses per user per bulan
            $table->boolean('is_active')->default(true);          // sakelar mati fitur tanpa deploy
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poster_settings');
    }
};
