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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('location', 255);
            $table->dateTime('date');
            // province, city, district, village
            $table->string('province', 20);
            $table->string('city', 20);
            $table->string('district', 20);
            $table->string('village', 20);
            // terbuka untuk umum atau undangan
            $table->enum('access', ['Umum', 'Khusus']);
            // kategori
            $table->string('category', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
