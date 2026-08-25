<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slug kosmetik untuk URL "/{id}-{slug}".
 *
 * Sengaja TANPA unique index: resolusi URL tetap lewat ID, sehingga dua entitas
 * bernama sama boleh berbagi slug yang sama. Tanpa index sama sekali karena tidak
 * ada query yang mencari berdasarkan kolom ini.
 *
 * Tidak memakai ->after(): urutan kolom tidak relevan, dan skema produksi pernah
 * menyimpang dari migration sehingga nama kolom acuan tidak dapat diandalkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assemblies', function (Blueprint $table) {
            $table->string('slug')->nullable();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->string('slug')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('assemblies', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
