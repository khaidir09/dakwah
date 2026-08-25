<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Isi slug untuk baris yang sudah ada.
 *
 * Idempoten (`whereNull('slug')`) dan diproses per 200 baris agar tidak mengunci
 * tabel lama-lama. Menulis lewat query builder, bukan Eloquent, supaya tidak
 * memicu event `saving` maupun menyentuh kolom `updated_at`.
 *
 * Sengaja tidak memakai App\Models — migrasi harus tetap benar meski model
 * berubah di kemudian hari. Aturan slug di sini wajib sama dengan
 * App\Models\Concerns\HasRouteSlug::makeRouteSlug().
 *
 * `down()` sengaja tidak mengosongkan slug: menghapus data pada rollback tidak
 * memberi manfaat apa pun dan hanya menambah risiko.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->backfill('assemblies', 'nama_majelis');
        $this->backfill('schedules', 'nama_jadwal');
    }

    public function down(): void
    {
        // Sengaja kosong — lihat catatan di atas.
    }

    private function backfill(string $table, string $sourceColumn): void
    {
        DB::table($table)
            ->select('id', $sourceColumn)
            ->whereNull('slug')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $sourceColumn) {
                foreach ($rows as $row) {
                    $slug = Str::slug((string) $row->{$sourceColumn});

                    if ($slug === '') {
                        continue;
                    }

                    DB::table($table)->where('id', $row->id)->update(['slug' => $slug]);
                }
            });
    }
};
