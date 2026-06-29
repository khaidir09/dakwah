<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            // Nominal Rupiah; wajib diisi saat price_type = paid, null untuk pustaka gratis.
            $table->unsignedInteger('price')->nullable()->after('price_type');
        });
    }

    public function down(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
