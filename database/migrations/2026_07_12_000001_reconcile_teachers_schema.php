<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Skema `teachers` di database yang berjalan sudah menyimpang dari migration: kolom wilayah
 * (province_code..village_code) ada di sana tanpa migration, `foto` sudah nullable, dan
 * `domisili` sudah tidak ada. Akibatnya `migrate:fresh` (termasuk database test) menghasilkan
 * tabel yang berbeda dari produksi, dan penyimpanan data guru gagal.
 *
 * Migration ini menyamakan keduanya tanpa menghapus kolom apa pun. Setiap perubahan dijaga
 * pengecekan agar aman dijalankan pada database yang sudah terlanjur benar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (! Schema::hasColumn('teachers', 'province_code')) {
                $table->string('province_code', 20)->nullable()->after('foto');
            }
            if (! Schema::hasColumn('teachers', 'city_code')) {
                $table->string('city_code', 20)->nullable()->after('province_code');
            }
            if (! Schema::hasColumn('teachers', 'district_code')) {
                $table->string('district_code', 20)->nullable()->after('city_code');
            }
            if (! Schema::hasColumn('teachers', 'village_code')) {
                $table->string('village_code', 20)->nullable()->after('district_code');
            }
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->string('foto', 100)->nullable()->change();

            // Kolom dipertahankan (tidak di-drop) — hanya dilonggarkan agar tidak memblokir insert.
            if (Schema::hasColumn('teachers', 'domisili')) {
                $table->string('domisili', 100)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // Sengaja tidak mengembalikan kolom wilayah maupun constraint NOT NULL:
        // membalikkannya akan merusak data yang sudah bergantung padanya.
    }
};
