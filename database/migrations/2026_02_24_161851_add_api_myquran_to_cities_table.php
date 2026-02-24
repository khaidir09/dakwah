<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Try to handle both cases: prefixed or not.
        // In testing environment (sqlite), often prefixes might be missing if config not loaded properly or defaults used.
        // However, the original migration uses config('laravolt.indonesia.table_prefix').
        // Since we can't easily know the config value here without loading app, we can check Schema.
        
        $table = null;
        if (Schema::hasTable('indonesia_cities')) {
            $table = 'indonesia_cities';
        } elseif (Schema::hasTable('cities')) {
            $table = 'cities';
        }

        if ($table) {
            Schema::table($table, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'api_myquran')) {
                    $table->string('api_myquran')->nullable()->after('name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = null;
        if (Schema::hasTable('indonesia_cities')) {
            $table = 'indonesia_cities';
        } elseif (Schema::hasTable('cities')) {
            $table = 'cities';
        }

        if ($table) {
            Schema::table($table, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'api_myquran')) {
                    $table->dropColumn('api_myquran');
                }
            });
        }
    }
};
