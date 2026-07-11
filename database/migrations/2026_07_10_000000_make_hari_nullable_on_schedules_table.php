<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadwal dengan recurrence_type 'monthly_date' tidak punya hari (hanya tanggal),
     * sehingga kolom `hari` harus boleh NULL.
     *
     * `create_schedules_table` sudah mendeklarasikan `hari` sebagai nullable, tetapi
     * tabel di database yang berjalan ternyata NOT NULL varchar(100) utf8mb4_general_ci —
     * pernah diubah di luar migration. Migration ini menyelaraskannya, dan hanya relevan
     * untuk MySQL/MariaDB; pada driver lain skema sudah sesuai.
     */
    public function up(): void
    {
        if (! in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('schedules', function (Blueprint $table) {
            $table->string('hari', 100)->collation('utf8mb4_general_ci')->nullable()->change();
        });
    }

    /**
     * Tidak direvert: mengembalikan kolom menjadi NOT NULL akan gagal selama masih ada
     * jadwal 'monthly_date' yang `hari`-nya NULL, dan memaksakannya berarti mengubah data.
     */
    public function down(): void
    {
        //
    }
};
