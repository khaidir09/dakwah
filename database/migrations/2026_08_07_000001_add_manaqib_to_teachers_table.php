<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Memisahkan isi `/guru/{slug}` dan `/manaqib/{slug}`, yang selama ini merender
 * kolom `biografi` yang sama persis.
 *
 * Aditif: kolom baru nullable, tidak ada data yang disentuh. Tanpa `after()`
 * karena skema `teachers` di produksi punya riwayat penyimpangan dari migration,
 * sehingga kolom acuan belum tentu ada di posisi yang diharapkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('teachers', 'manaqib')) {
            return;
        }

        Schema::table('teachers', function (Blueprint $table) {
            $table->text('manaqib')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('teachers', 'manaqib')) {
            return;
        }

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('manaqib');
        });
    }
};
