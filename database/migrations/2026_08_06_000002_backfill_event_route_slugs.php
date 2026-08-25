<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Isi slug acara yang sudah ada. Kembaran 2026_08_05_000002 untuk tabel `events`.
 *
 * Idempoten (`whereNull('slug')`), diproses per 200 baris, dan menulis lewat query
 * builder agar tidak memicu event `saving` maupun menyentuh `updated_at`.
 *
 * Sengaja tidak memakai App\Models — migrasi harus tetap benar meski model berubah.
 * Aturan slug di sini wajib sama dengan App\Models\Concerns\HasRouteSlug::makeRouteSlug().
 *
 * `down()` sengaja tidak mengosongkan slug: menghapus data pada rollback tidak
 * memberi manfaat apa pun dan hanya menambah risiko.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('events')
            ->select('id', 'name')
            ->whereNull('slug')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $slug = Str::slug((string) $row->name);

                    if ($slug === '') {
                        continue;
                    }

                    DB::table('events')->where('id', $row->id)->update(['slug' => $slug]);
                }
            });
    }

    public function down(): void
    {
        // Sengaja kosong — lihat catatan di atas.
    }
};
