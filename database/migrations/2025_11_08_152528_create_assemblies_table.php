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
        Schema::create('assemblies', function (Blueprint $table) {
            $table->id();
            $table->string('nama_majelis', 255);
            $table->longText('deskripsi');
            $table->string('guru', 255);
            $table->text('alamat');
            $table->string('maps', 255);
            $table->string('gambar', 255)->nullable();
            $table->enum('status', ['Aktif', 'Tutup'])->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assemblies');
    }
};
