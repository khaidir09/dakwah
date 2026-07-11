<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `schedules.teacher_id` sudah lama dipakai kode (Schedule::teacher()), tetapi tidak
     * pernah dibuat oleh migration mana pun — kolomnya hanya ada di database yang berjalan,
     * ditambahkan di luar migration. Akibatnya skema hasil migrate (mis. sqlite untuk test)
     * tidak punya kolom ini.
     *
     * Migration ini menutup celah tersebut dan aman dijalankan berkali-kali: pada database
     * yang sudah punya kolomnya, tidak ada perubahan.
     */
    public function up(): void
    {
        if (Schema::hasColumn('schedules', 'teacher_id')) {
            return;
        }

        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('teacher_id')->nullable()->after('assembly_id');
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        //
    }
};
