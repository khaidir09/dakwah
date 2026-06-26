<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontribusi_xp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('contribution_type')->unique();
            $table->unsignedInteger('points');
            $table->string('label');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontribusi_xp_settings');
    }
};
