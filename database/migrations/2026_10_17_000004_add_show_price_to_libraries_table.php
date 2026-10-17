<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            // Bila false, harga tidak tampil di halaman maupun pesan WhatsApp otomatis; admin menyampaikannya saat membalas chat.
            $table->boolean('show_price')->default(true)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            $table->dropColumn('show_price');
        });
    }
};
