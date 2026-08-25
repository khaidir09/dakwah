<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lanjutan dari 2026_07_12_000001_reconcile_teachers_schema: kolom `wafat_hijriah_year`
 * ada di database yang berjalan tanpa migration apa pun, sehingga `migrate:fresh`
 * (termasuk database test) menghasilkan tabel yang tidak memilikinya — padahal kolom itu
 * adalah pembeda manaqib vs guru hidup (ListBiography/ListGuru) dan dipakai API haul.
 *
 * Backfill mengisi tahun dari `wafat_hijriah` yang lama bila kolom itu masih ada;
 * tidak ada kolom yang dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('teachers', 'wafat_hijriah_year')) {
            return;
        }

        Schema::table('teachers', function (Blueprint $table) {
            $after = Schema::hasColumn('teachers', 'wafat_hijriah_month') ? 'wafat_hijriah_month' : 'wafat_masehi';

            $table->integer('wafat_hijriah_year')->nullable()->after($after);
        });

        if (! Schema::hasColumn('teachers', 'wafat_hijriah')) {
            return;
        }

        DB::table('teachers')
            ->whereNotNull('wafat_hijriah')
            ->orderBy('id')
            ->each(function ($teacher) {
                if (preg_match('/(\d{3,4})/', (string) $teacher->wafat_hijriah, $matches)) {
                    DB::table('teachers')
                        ->where('id', $teacher->id)
                        ->update(['wafat_hijriah_year' => (int) $matches[1]]);
                }
            });
    }

    public function down(): void
    {
        // Kolom sengaja dipertahankan: data manaqib bergantung padanya.
    }
};
