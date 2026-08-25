<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slug kosmetik untuk URL "/event/{id}-{slug}", mengikuti pola yang sama dengan
 * 2026_08_05_000001 pada `assemblies` dan `schedules`.
 *
 * Sengaja tanpa index: resolusi URL tetap lewat ID dan tidak ada query yang
 * mencari berdasarkan kolom ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
