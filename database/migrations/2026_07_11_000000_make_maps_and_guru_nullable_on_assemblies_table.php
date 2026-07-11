<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom `maps` dan `guru` dulunya NOT NULL, tetapi form majelis
     * memperlakukan keduanya sebagai opsional dan controller modern
     * (admin/kontributor) tidak pernah mengisi `guru`. Input `maps`
     * yang dikosongkan dikonversi menjadi NULL oleh middleware
     * ConvertEmptyStringsToNull sehingga INSERT gagal dengan
     * "Column 'maps' cannot be null" dan memunculkan halaman error 500.
     * Migrasi ini melonggarkan kedua kolom agar penyimpanan tetap berhasil.
     */
    public function up(): void
    {
        Schema::table('assemblies', function (Blueprint $table) {
            $table->string('maps', 255)->nullable()->change();
            $table->string('guru', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('assemblies', function (Blueprint $table) {
            $table->string('maps', 255)->nullable(false)->change();
            $table->string('guru', 255)->nullable(false)->change();
        });
    }
};
