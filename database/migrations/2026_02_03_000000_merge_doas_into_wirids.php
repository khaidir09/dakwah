<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wirids', function (Blueprint $table) {
            $table->string('kategori')->default('wirid')->after('id')->index();
        });

        // Migrate Data
        $doas = DB::table('doas')->get();
        foreach ($doas as $doa) {
            DB::table('wirids')->insert([
                'nama' => $doa->nama,
                'deskripsi' => $doa->deskripsi,
                'arab' => $doa->arab,
                'arti' => $doa->arti,
                'jumlah' => $doa->jumlah,
                'likes' => $doa->likes,
                'waktu' => $doa->waktu,
                'kategori' => 'doa',
                'created_at' => $doa->created_at,
                'updated_at' => $doa->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('wirids', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
