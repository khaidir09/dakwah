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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jadwal', 255);
            $table->longText('deskripsi');
            $table->foreignId('assembly_id')->constrained()->onDelete('cascade');
            $table->dateTime('waktu');
            $table->enum('status', ['Aktif', 'Selesai', 'Batal', 'Libur Ramadhan'])->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
