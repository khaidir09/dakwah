<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('foto_bersama')->nullable()->after('foto');
            $table->string('foto_bersama_caption', 255)->nullable()->after('foto_bersama');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['foto_bersama', 'foto_bersama_caption']);
        });
    }
};
