<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            // Penerima bagi hasil pustaka berbayar; pustaka tanpa persentase tidak masuk laporan.
            $table->string('payee_name')->nullable()->after('price');
            $table->unsignedTinyInteger('author_share_percent')->nullable()->after('payee_name');
        });
    }

    public function down(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            $table->dropColumn(['payee_name', 'author_share_percent']);
        });
    }
};
