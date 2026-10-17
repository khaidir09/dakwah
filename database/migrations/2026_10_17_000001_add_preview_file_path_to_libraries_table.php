<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            // PDF pratinjau gratis (mis. Bab I) untuk pustaka berbayar; disimpan di disk privat.
            $table->string('preview_file_path')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('libraries', function (Blueprint $table) {
            $table->dropColumn('preview_file_path');
        });
    }
};
