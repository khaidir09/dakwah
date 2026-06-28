# Validasi Tanggal Acara Kontributor

**Status Dokumen:** Draft  
**Tanggal:** 2026-06-27  
**Author:** Muhammad Khaidir

---

## Latar Belakang

Kontributor dapat menambahkan acara majelis tanpa batasan tanggal. Akibatnya, acara dapat diinput mendekati hari pelaksanaan sehingga informasi tidak sempat dilihat oleh masyarakat luas sebelum acara berlangsung.

Fitur ini menambahkan validasi server-side pada form **tambah dan edit acara**: **tanggal acara harus minimal 7 hari ke depan** dari tanggal input. Aturan ini hanya berlaku untuk kontributor, bukan admin.

---

## Tujuan

Memastikan setiap acara yang disubmit kontributor dapat diketahui publik setidaknya 1 minggu sebelum pelaksanaan, sehingga informasi benar-benar bermanfaat.

---

## Perilaku Saat Ini

`ManageEventController@store()` memvalidasi field `date` hanya dengan rule `required|date` — tidak ada batas minimum tanggal. Admin (`Super Admin`) dan kontributor sama-sama tidak dibatasi.

---

## Expected Behavior

### Skenario 1 — Tanggal valid (≥ 7 hari ke depan)
- Kontributor mengisi tanggal acara ≥ 7 hari dari hari ini (WITA).
- Form diproses normal, acara tersimpan, redirect ke daftar acara dengan pesan sukses.

### Skenario 2 — Tanggal terlalu dekat (< 7 hari)
- Kontributor mengisi tanggal acara < 7 hari dari hari ini (WITA).
- Form **gagal tersimpan**, redirect back ke form dengan flash alert error di atas form:
  > *"Tanggal acara harus minimal 7 hari dari hari ini."*
- Input lain (nama, kategori, dll.) tetap terisi (`old()` values).

### Skenario 3 — Admin menambah acara
- Super Admin menggunakan route yang sama (`/kelola-acara-majelis`).
- Validasi 7 hari **tidak diterapkan**. Admin boleh input tanggal berapapun.

### Skenario 4 — Edit acara (tanggal terlalu dekat)
- Kontributor mengedit acara dan mengisi `date` < 7 hari dari hari ini.
- Form **gagal tersimpan**, redirect back ke form edit dengan flash alert error yang sama.

### Skenario 5 — Edit acara (tanggal valid)
- Kontributor mengedit acara dan mengisi `date` ≥ 7 hari dari hari ini.
- Update diproses normal.

---

## Role & Authorization

| Role | Terkena Validasi? |
|---|---|
| Kontributor (non-admin) | Ya |
| Super Admin | Tidak |

Deteksi: `Auth::user()->hasRole('Super Admin')` — pola yang sudah dipakai di baris 61 controller yang sama.

---

## Data Model

- **Tabel**: `events`
- **Kolom**: `date` bertipe `DATETIME`
- **Input form**: `<input type="datetime-local">` — nilai yang dikirim menyertakan waktu (jam:menit)
- **Perbandingan tanggal**: cukup membandingkan bagian **tanggal** saja (hari, bukan jam); waktu tidak relevan untuk aturan 7 hari

---

## Zona Waktu

Aplikasi dikonfigurasi dengan `'timezone' => 'Asia/Makassar'` (WITA, UTC+8) di `config/app.php`. `Carbon::today()` mengikuti timezone aplikasi secara otomatis — tidak perlu konfigurasi tambahan.

**Definisi "7 hari ke depan"**: Jika hari ini adalah `2026-06-27`, tanggal paling awal yang diizinkan adalah `2026-07-04`. Rule yang digunakan: `after_or_equal:` + `Carbon::today()->addDays(7)->toDateString()`.

---

## Implementasi

### File yang Berubah

| File | Perubahan |
|---|---|
| `app/Http/Controllers/User/ManageEventController.php` | Tambah rule kondisional pada `store()` dan `update()` |
| `resources/views/pages/user/kelola-acara/tambah-acara.blade.php` | Tampilkan flash error untuk field `date` |
| `resources/views/pages/user/kelola-acara/edit-acara.blade.php` | Tampilkan flash error untuk field `date` |

### Perubahan Controller

Rule yang sama diterapkan di **`store()`** dan **`update()`**. Ekstrak ke private method agar tidak duplikasi:

```php
use Carbon\Carbon;

private function dateRules(): array
{
    $rules = ['required', 'date'];

    if (! Auth::user()->hasRole('Super Admin')) {
        $rules[] = 'after_or_equal:' . Carbon::today()->addDays(7)->toDateString();
    }

    return $rules;
}
```

Kemudian gunakan di kedua method:

```php
// store() dan update() sama-sama:
$validatedData = $request->validate([
    'name'     => 'required|string|max:255',
    'image'    => 'nullable|image|max:2048',
    'date'     => $this->dateRules(),
    'access'   => 'required|in:Umum,Khusus',
    'category' => 'required|string|max:255',
], [
    'date.after_or_equal' => 'Tanggal acara harus minimal 7 hari dari hari ini.',
]);
```

### Perubahan View (kedua form)

Tambahkan flash alert di atas `<form>` pada `tambah-acara.blade.php` **dan** `edit-acara.blade.php`:

```blade
@if ($errors->has('date'))
    <div class="mb-4 p-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg" role="alert">
        {{ $errors->first('date') }}
    </div>
@endif
```

---

## Yang Tidak Termasuk Scope

- `app/Http/Controllers/EventController.php` (admin controller) — admin tidak dibatasi via controller ini.
- `app/Http/Controllers/EventController.php` (admin controller) — admin tidak dibatasi.
- Data acara yang sudah ada di database — tidak disentuh, tidak ada backfill.
- Validasi sisi klien (JavaScript/HTML `min` attribute) — tidak diperlukan; validasi server sudah cukup.
- Pengecualian per-kontributor — tidak ada, aturan berlaku seragam.
- Notifikasi ke kontributor saat ditolak validasi — ditangani otomatis oleh redirect back with errors.

---

## Acceptance Criteria

- [ ] **AC-1**: Kontributor yang mengisi `date` dengan nilai < 7 hari dari hari ini tidak dapat menyimpan acara; redirect kembali ke form dengan flash alert error.
- [ ] **AC-2**: Pesan error yang tampil adalah *"Tanggal acara harus minimal 7 hari dari hari ini."*
- [ ] **AC-3**: Input form lain (nama, kategori, akses, dll.) tetap terisi setelah gagal validasi (`old()` berfungsi).
- [ ] **AC-4**: Kontributor yang mengisi `date` ≥ 7 hari dari hari ini berhasil menyimpan acara seperti biasa.
- [ ] **AC-5**: Super Admin dapat menyimpan acara dengan tanggal berapapun tanpa pesan error.
- [ ] **AC-6**: Kontributor yang mengisi `date` < 7 hari saat edit acara tidak dapat menyimpan perubahan; redirect kembali ke form edit dengan flash alert error.
- [ ] **AC-6b**: Kontributor yang mengisi `date` ≥ 7 hari saat edit acara berhasil menyimpan perubahan.
- [ ] **AC-7**: Validasi bekerja dengan timezone WITA (Asia/Makassar), bukan UTC.

---

## Test Plan (`tests/Feature/EventDateValidationTest.php`)

File baru, menggunakan SQLite in-memory (sudah dikonfigurasi di `phpunit.xml`).

| Test | Skenario |
|---|---|
| `test_contributor_cannot_submit_event_less_than_7_days` | Input 6 hari ke depan → assert redirect back + error session |
| `test_contributor_cannot_submit_event_today` | Input hari ini → assert redirect back + error session |
| `test_contributor_can_submit_event_exactly_7_days_ahead` | Input tepat 7 hari ke depan → assert redirect sukses |
| `test_contributor_can_submit_event_more_than_7_days_ahead` | Input 14 hari ke depan → assert redirect sukses |
| `test_super_admin_can_submit_event_regardless_of_date` | Super Admin, input besok → assert redirect sukses |
| `test_contributor_cannot_update_event_less_than_7_days` | Kontributor edit acara, tanggal 6 hari ke depan → assert redirect back + error session |
| `test_contributor_can_update_event_exactly_7_days_ahead` | Kontributor edit acara, tanggal tepat 7 hari ke depan → assert redirect sukses |
| `test_super_admin_can_update_event_regardless_of_date` | Super Admin edit acara, tanggal besok → assert redirect sukses |

---

## Verifikasi End-to-End

1. Login sebagai kontributor yang memiliki majelis.
2. Buka `/kelola-acara-majelis/create`.
3. Isi semua field, set `date` = besok (< 7 hari). Klik **Simpan**.
4. Verifikasi: halaman kembali ke form, muncul alert merah dengan pesan *"Tanggal acara harus minimal 7 hari dari hari ini."*, field lain masih terisi.
5. Ubah `date` = 7 hari ke depan. Klik **Simpan**.
6. Verifikasi: redirect ke daftar acara dengan pesan sukses. Acara muncul di daftar dengan status pending.
7. Login sebagai Super Admin, ulangi langkah 2–3 dengan tanggal besok.
8. Verifikasi: acara berhasil disimpan tanpa error.
9. Kembali sebagai kontributor, buka halaman edit acara yang sudah ada.
10. Ubah `date` = besok. Klik **Simpan**.
11. Verifikasi: halaman kembali ke form edit, muncul alert merah yang sama.
12. Ubah `date` = 7 hari ke depan. Klik **Simpan**.
13. Verifikasi: redirect ke daftar acara dengan pesan sukses.

---

## Risiko & Catatan

- **Timezone offset**: `Carbon::today()` di server WITA sudah benar karena `APP_TIMEZONE=Asia/Makassar`. Jika server pindah ke UTC, rule tetap aman karena mengacu ke `Carbon::today()` bukan `now()->startOfDay()`.
- **Datetime vs Date**: Field `date` berisi nilai `datetime-local` (misal: `2026-07-04T19:00`). Rule `after_or_equal:2026-07-04` tetap benar karena Laravel membandingkan tanggal penuh — `2026-07-04T00:00` adalah titik batas, sehingga jam 19:00 di hari yang sama tetap lolos.
- **Tidak ada custom Rule class**: Rule `after_or_equal` bawaan Laravel sudah cukup; tidak perlu membuat `Rule` object atau custom validation class.
