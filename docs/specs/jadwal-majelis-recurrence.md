# Jadwal Majelis Berkala (Recurrence Non-Mingguan)

**Status Dokumen:** Draft
**Tanggal:** 2026-07-08
**Author:** Muhammad Khaidir

---

## Latar Belakang

Model jadwal saat ini mengasumsikan **semua jadwal berulang mingguan**. Sebuah baris `schedules` hanya menyimpan satu `hari` (string Gregorian "Senin"…"Minggu") + `waktu` (jam), dan dianggap terjadi setiap minggu pada hari itu.

Kenyataannya banyak majelis tidak mingguan: ada yang **sekali sebulan** (mis. "Minggu pekan pertama"), **dua kali sebulan** (mis. "pekan 1 & 3"), pada **tanggal tetap** (mis. "tiap tanggal 15"), atau mengikuti **kalender Hijriah** (mis. "setiap 1–7 Hijriah, malam Jumat"). Jadwal seperti ini tidak bisa direpresentasikan sekarang, dan kalau dipaksakan sebagai jadwal mingguan akan salah muncul di widget "Jadwal Hari Ini".

Fitur ini memperkaya representasi recurrence jadwal **tanpa mengubah perilaku jadwal mingguan yang sudah ada**, lalu menampilkan jadwal non-mingguan pada **seksi "Jadwal Berkala" yang terpisah**.

---

## Tujuan

- Memungkinkan jadwal majelis direkam dengan pola: **mingguan** (existing), **bulanan (weekday+pekan)**, **bulanan (tanggal tetap)**, **dua kali sebulan (dua pekan)**, dan **berbasis Hijriah (pekan pertama, tanggal 1–7)**.
- Menampilkan jadwal berkala secara jelas dan benar kepada jamaah tanpa mengganggu logika "Jadwal Hari Ini".
- Menjaga **backward compatibility** penuh terhadap data & alur mingguan yang ada.

### Non-Tujuan (Iterasi 1)

Lihat bagian [Di Luar Scope](#di-luar-scope).

---

## Ruang Lingkup Keputusan (hasil wawancara)

| Keputusan | Pilihan yang disepakati |
|---|---|
| Pola yang didukung | Mingguan, Bulanan Gregorian (weekday+pekan **dan** tanggal tetap), 2× sebulan (dua pekan bebas), Hijriah (tanggal 1–7 pada weekday) |
| Tampilan "Jadwal Hari Ini" | Jadwal berkala **tidak** masuk widget "Hari Ini"; tampil di **seksi berkala terpisah** |
| Lokasi seksi berkala | **Halaman daftar jadwal (publik)** + **halaman detail majelis** (tidak di homepage) |
| Definisi "pekan pertama Hijriah" | Tanggal **1–7 Hijriah** pada weekday terpilih |
| Kalkulasi tanggal Hijriah | **Teks aturan saja** — tidak menghitung tanggal Masehi occurrence otomatis (iterasi 1) |
| Pekan ke-5 | Didukung sebagai opsi **"pekan terakhir"** (bukan angka 5) |
| Yang boleh input pola baru | **Admin (tanpa moderasi)** + **Kontributor (moderasi pending→approved)** |
| Pemilik majelis via kelola-majelis | Tetap **mingguan saja**; pola baru dikelola lewat dasbor kontribusi + admin |
| Data model | **Kolom baru di tabel `schedules`**, default `weekly` |
| Testing | **Feature test** (alur end-to-end + moderasi + tampilan) |

---

## Perilaku Saat Ini

- **Tabel `schedules`** (lihat migrasi `2025_11_08_201801_create_schedules_table.php` + kolom tambahan): `nama_jadwal`, `deskripsi`, `assembly_id`, `teacher_id`, `waktu` (dateTime), `hari` (string weekday), `access` (`Umum|Ikhwan|Akhwat`), `status` (`Aktif|Selesai|Batal|Libur Ramadhan`), kolom kontribusi (`contributor_user_id`, `contribution_status`, `rejection_reason`, `moderated_at`).
- **Recurrence implisit = mingguan.** Tidak ada kolom yang menyatakan pola pengulangan.
- **Widget "Jadwal Hari Ini"** (`app/Livewire/HomeJadwalMajelis.php`) memetakan `Carbon::now()->dayOfWeek` → nama hari Indonesia, lalu `Schedule::where('hari', $hariIni)`. Seluruh widget bergantung pada string `hari`.
- **Daftar publik** (`JadwalMajelisController@index` → `resources/views/pages/jadwal-majelis/index.blade.php`) menampilkan `Schedule::with('assembly')->get()`.
- **Kontribusi jadwal** (`app/Http/Controllers/User/KontribusiJadwalController.php`) — kontributor membuat/mengedit jadwal miliknya; `contribution_status='pending'`; menggunakan view `resources/views/pages/kontributor/jadwal/{create,edit,_form}.blade.php`. Field tervalidasi: `nama_jadwal, assembly_id, teacher_id, waktu, deskripsi, hari, access`. `hari` **wajib**.
- **Moderasi** (`app/Http/Controllers/Admin/ModerasiController@moderasiJadwal` → `KhidmahService`) mengubah `contribution_status` menjadi `approved`/`rejected`.
- **Visibilitas publik** difilter lewat `Schedule::scopePubliclyVisible()` (`contribution_status` null atau `approved`).
- **Timezone aplikasi** = `Asia/Makassar` (WITA) — dasar perhitungan "hari ini".
- **`HijriService`** hanya menyediakan string tanggal Hijriah hari ini (mis. "17 Syakban 1447 H") + `isRamadhan()`. **Tidak** ada angka hari Hijriah, batas bulan Hijriah, atau konversi Gregorian↔Hijriah.

---

## Expected Behavior

### Konsep: klasifikasi mingguan vs berkala

Sebuah jadwal disebut **mingguan** bila `recurrence_type = 'weekly'`, dan **berkala** untuk nilai lainnya.

- **Widget "Jadwal Hari Ini"** hanya memproses jadwal **mingguan** (`recurrence_type = 'weekly'`). Jadwal berkala dikecualikan sepenuhnya.
- **Seksi "Jadwal Berkala"** menampilkan jadwal **berkala** beserta **deskripsi aturan human-readable** (lihat [Ringkasan Aturan](#ringkasan-aturan-human-readable)). Tidak ada perhitungan tanggal Masehi occurrence pada iterasi 1.

### Skenario

**S1 — Jadwal mingguan (tidak berubah).** Admin/pemilik majelis membuat jadwal `recurrence_type='weekly'` (default). Muncul di "Jadwal Hari Ini" saat `hari` = hari ini (WITA), sama seperti sekarang. Tidak muncul di seksi berkala.

**S2 — Bulanan (weekday + pekan).** `recurrence_type='monthly_weekday'`, `week_of_month ∈ {1,2,3,4,last}`, `hari` terisi. Contoh: "Minggu, pekan pertama tiap bulan". Muncul hanya di seksi berkala dengan teks aturan. Tidak muncul di "Jadwal Hari Ini".

**S3 — Bulanan (tanggal tetap).** `recurrence_type='monthly_date'`, `day_of_month ∈ 1..31`, `hari = null`. Contoh: "Tiap tanggal 15". Muncul di seksi berkala.

**S4 — Dua kali sebulan (dua pekan).** `recurrence_type='semimonthly'`, `week_of_month` + `week_of_month_secondary` (dua nilai berbeda dari {1,2,3,4,last}), `hari` terisi. Contoh: "Jumat, pekan 1 & 3". Muncul di seksi berkala.

**S5 — Hijriah pekan pertama.** `recurrence_type='hijri_first_week'`, `calendar_system='hijri'`, `hari` terisi. Maknanya: weekday terpilih yang jatuh pada **tanggal 1–7 bulan Hijriah**. Contoh: "Malam Jumat, pekan pertama Hijriah". Muncul di seksi berkala dengan teks aturan; tidak ada konversi tanggal.

**S6 — Kontributor mengirim jadwal berkala.** Kontributor memilih tipe recurrence di form kontribusi → tersimpan `contribution_status='pending'` → tidak tampil publik hingga di-approve admin → setelah approve, tampil di seksi berkala.

**S7 — Validasi tergantung tipe.** Field wajib menyesuaikan `recurrence_type` (lihat [Aturan Validasi](#aturan-validasi)). Mis. `monthly_date` tidak butuh `hari`, tetapi butuh `day_of_month`.

---

## Role & Authorization

| Aktor | Boleh set pola baru? | Moderasi |
|---|---|---|
| Super Admin (`/admin/*`) | Ya | Tidak (langsung aktif) |
| Kontributor (dasbor kontribusi) | Ya | Ya (`pending → approved/rejected`) |
| Pemilik majelis (kelola-majelis) | **Tidak** — tetap mingguan | — |
| Jamaah publik | Tidak (hanya melihat yang `approved`/tanpa status) | — |

- Otorisasi mengikuti middleware yang ada: `IsAdmin` (`Super Admin`) untuk admin; `auth:sanctum + verified` + kepemilikan (`Assembly::where('user_id', Auth::id())`) untuk kontributor.
- **Form `kelola-majelis` (pemilik majelis) tidak menampilkan pilihan recurrence** dan tetap menyimpan `recurrence_type='weekly'` secara implisit (default kolom).

---

## Data Model

Tambahkan kolom baru ke tabel `schedules` melalui **satu migration baru** (backward-compatible, semua nullable / ber-default). **Tidak mengubah migrasi lama.**

| Kolom | Tipe | Default | Keterangan |
|---|---|---|---|
| `recurrence_type` | `string` | `'weekly'` | `weekly` \| `monthly_weekday` \| `monthly_date` \| `semimonthly` \| `hijri_first_week` |
| `calendar_system` | `string` | `'gregorian'` | `gregorian` \| `hijri` |
| `week_of_month` | `string` nullable | `null` | `'1'..'4'` atau `'last'`. Dipakai `monthly_weekday`, `semimonthly`, dan (opsional) `hijri_first_week` bila ingin membatasi — untuk `hijri_first_week` bermakna implisit "pekan 1" |
| `week_of_month_secondary` | `string` nullable | `null` | Pekan kedua untuk `semimonthly` |
| `day_of_month` | `unsignedTinyInteger` nullable | `null` | `1..31`, hanya untuk `monthly_date` |

Catatan desain:
- Kolom `hari` (existing) **dipertahankan & dipakai ulang** sebagai weekday untuk `weekly`, `monthly_weekday`, `semimonthly`, `hijri_first_week`. Untuk `monthly_date`, `hari` = `null`.
- Enum recurrence disimpan sebagai `string` (bukan DB enum) agar mudah menambah tipe di masa depan tanpa migrasi ubah-enum.
- **Kompatibilitas:** semua baris existing otomatis `recurrence_type='weekly'`, `calendar_system='gregorian'`, kolom lain `null` → perilaku widget "Hari Ini" tidak berubah.

### Konstanta / Enum aplikasi

Definisikan konstanta pada `App\Models\Schedule` (mis. `RECURRENCE_TYPES`, `WEEKS_OF_MONTH`) sebagai satu sumber kebenaran untuk validasi & tampilan. Tambahkan cast bila perlu (`day_of_month` → integer).

### Ringkasan Aturan (human-readable)

Tambahkan accessor pada `Schedule` (mis. `getRecurrenceLabelAttribute()`) yang mengubah kolom-kolom di atas menjadi teks Indonesia, contoh:
- `weekly` + `hari=Senin` → "Setiap Senin"
- `monthly_weekday` + `week=1` + `hari=Minggu` → "Minggu, pekan pertama tiap bulan"
- `monthly_weekday` + `week=last` + `hari=Jumat` → "Jumat, pekan terakhir tiap bulan"
- `monthly_date` + `day=15` → "Tiap tanggal 15"
- `semimonthly` + `week=1,3` + `hari=Jumat` → "Jumat, pekan ke-1 & ke-3 tiap bulan"
- `hijri_first_week` + `hari=Kamis` → "Kamis, pekan pertama tiap bulan Hijriah (1–7)"

---

## Aturan Validasi

Validasi server-side (di `KontribusiJadwalController` untuk kontributor & controller admin terkait). Aturan conditional berdasarkan `recurrence_type`:

| `recurrence_type` | Field wajib | Field harus kosong/diabaikan |
|---|---|---|
| `weekly` | `hari` | week/day fields |
| `monthly_weekday` | `hari`, `week_of_month` | `day_of_month` |
| `monthly_date` | `day_of_month` (1–31) | `hari`, week fields |
| `semimonthly` | `hari`, `week_of_month`, `week_of_month_secondary` (≠ primary) | `day_of_month` |
| `hijri_first_week` | `hari` (+ `calendar_system='hijri'` di-set otomatis) | `day_of_month` |

- `recurrence_type` wajib `in:weekly,monthly_weekday,monthly_date,semimonthly,hijri_first_week`.
- `week_of_month` / `week_of_month_secondary` wajib `in:1,2,3,4,last`.
- Gunakan `Rule::requiredIf` / `required_if` agar backward compatible dengan pemanggilan tanpa `recurrence_type` (anggap `weekly`).
- Tetap gunakan `clean()` (mews/purifier) untuk field HTML (`deskripsi`) sesuai konvensi keamanan repo.

---

## API / UI

Tidak ada REST/JSON API baru — semua lewat form + Blade + Livewire yang ada.

### UI Input (form)

- **Form kontribusi** `resources/views/pages/kontributor/jadwal/_form.blade.php` (dipakai create & edit): tambahkan selector **"Tipe Jadwal / Pengulangan"**. Field kondisional (`hari`, `week_of_month`, `week_of_month_secondary`, `day_of_month`) ditampilkan/disembunyikan mengikuti pilihan tipe (Alpine.js, konsisten dgn stack TALL). Pertahankan `old()` values & tampilan error yang ada.
- **Form admin** (bila admin mengelola jadwal via area admin) mendapat selector serupa. Form **kelola-majelis (pemilik majelis) tidak diubah** — tetap mingguan.

### UI Tampilan (publik)

- **Halaman daftar jadwal** `resources/views/pages/jadwal-majelis/index.blade.php` (`JadwalMajelisController@index`): tambahkan **seksi/tab "Jadwal Berkala"** yang mengambil jadwal `recurrence_type != 'weekly'` (dan `publiclyVisible()`), menampilkan **teks aturan** via accessor. Jadwal mingguan tetap tampil seperti sebelumnya.
- **Halaman detail majelis** `resources/views/pages/user/majelis/detail.blade.php`: jadwal berkala majelis tersebut ditampilkan tercampur/berlabel dengan teks aturannya.
- **Widget "Jadwal Hari Ini"** `app/Livewire/HomeJadwalMajelis.php`: tambahkan filter `->where('recurrence_type', 'weekly')` pada query agar jadwal berkala tidak bocor ke widget "Hari Ini". (Perubahan minimal, satu klausa where.)

---

## File / Interface Terkait

| File | Perubahan |
|---|---|
| `database/migrations/<baru>_add_recurrence_columns_to_schedules_table.php` | **Baru** — tambah 5 kolom |
| `app/Models/Schedule.php` | Konstanta recurrence, cast, accessor `recurrence_label`, scope helper (mis. `scopeWeekly`, `scopeBerkala`) |
| `app/Http/Controllers/User/KontribusiJadwalController.php` | Validasi conditional + simpan kolom baru (`store`, `update`) |
| Controller admin jadwal terkait (mis. `JadwalMajelisController` / area admin) | Validasi + simpan kolom baru |
| `app/Livewire/HomeJadwalMajelis.php` | Filter `recurrence_type='weekly'` |
| `resources/views/pages/kontributor/jadwal/_form.blade.php` | Selector tipe + field kondisional |
| `resources/views/pages/jadwal-majelis/index.blade.php` | Seksi "Jadwal Berkala" |
| `resources/views/pages/user/majelis/detail.blade.php` | Tampilkan aturan berkala |
| `app/Services/HijriService.php` | **Tidak diubah** (iterasi 1) |
| `app/Http/Controllers/Admin/ModerasiController.php` | **Tidak diubah** — alur moderasi generik sudah cukup |

---

## Di Luar Scope

- **Menghitung tanggal Masehi occurrence** untuk pola apa pun (Gregorian maupun Hijriah). Iterasi 1 hanya menampilkan **teks aturan**.
- **Konversi Gregorian↔Hijriah** atau perluasan `HijriService`.
- **Memunculkan jadwal berkala di widget "Jadwal Hari Ini"** atau di homepage.
- **Notifikasi/pengingat** occurrence berkala (OneSignal) — tidak berubah.
- **Kalender/ICS export**, "occurrence berikutnya", atau agregasi occurrence lintas bulan.
- **Migrasi data** jadwal mingguan lama menjadi pola lain (semua tetap `weekly`).
- **Pola tak-ditentukan** lain (mis. kuartalan, tahunan, interval hari kustom).
- Perubahan pada **form kelola-majelis pemilik** (tetap mingguan).
- Perubahan pada **`ScheduleNote`** (catatan per-sesi tidak terpengaruh recurrence).

---

## Edge Cases

1. **Pekan ke-5 tak ada.** Ditangani dengan opsi `'last'` (pekan terakhir), bukan angka 5 → selalu valid. Angka 5 tidak ditawarkan.
2. **`semimonthly` dua pekan sama.** Validasi menolak `week_of_month_secondary == week_of_month`.
3. **`monthly_date` = 29/30/31.** Disimpan apa adanya; karena iterasi 1 tidak menghitung occurrence, tidak ada isu "bulan pendek". (Catatan untuk iterasi berikut.)
4. **Data lama tanpa kolom baru.** Default `weekly` + `gregorian` → tidak berubah perilaku.
5. **Pemanggilan store/update tanpa `recurrence_type`** (mis. form pemilik majelis lama). Diperlakukan sebagai `weekly`; validasi conditional tidak mewajibkan kolom baru.
6. **Pencarian.** `HomeJadwalMajelis` & `JadwalMajelis` melakukan `where('hari','like',…)`. Untuk `monthly_date` `hari=null` → baris tidak cocok pada pencarian berbasis hari; ini dapat diterima (pencarian tetap bisa via nama/guru/majelis). Pastikan tidak error karena null.
7. **`access` (Umum/Ikhwan/Akhwat)** tetap berlaku untuk semua tipe.
8. **Timezone.** Klasifikasi weekly tetap memakai WITA (`Asia/Makassar`) seperti sekarang; berkala tidak bergantung timezone karena hanya teks.

---

## Compatibility

- **Backward-compatible penuh:** kolom baru ber-default, migrasi lama tidak disentuh, data existing = `weekly`.
- **Alur & UI mingguan tidak berubah** untuk pemilik majelis maupun widget "Hari Ini".
- **Rollback:** migration menyediakan `down()` yang men-drop 5 kolom baru; tidak ada data mingguan yang hilang.
- **Test SQLite in-memory:** kolom `string`/`unsignedTinyInteger` didukung; tidak memakai DB enum.

---

## Testing (Feature Test)

Lokasi: `tests/Feature/` (mis. `ScheduleRecurrenceTest.php`). Gunakan pola test yang ada (`ManagedMajelisTest`, `ScheduleNoteTest`).

Kasus minimum:
1. **Migrasi & default** — membuat `Schedule` tanpa field recurrence menghasilkan `recurrence_type='weekly'`, `calendar_system='gregorian'`.
2. **Weekly tetap muncul di "Jadwal Hari Ini"** — jadwal `weekly` dengan `hari` = hari ini tampil; jadwal berkala **tidak** tampil di widget (assert via Livewire `HomeJadwalMajelis`).
3. **Kontributor kirim `monthly_weekday`** — tersimpan `pending`, tidak tampil publik; setelah `approved` tampil di seksi berkala dengan label aturan benar.
4. **Validasi conditional** — `monthly_date` tanpa `day_of_month` gagal; `semimonthly` dgn dua pekan sama gagal; `monthly_weekday` tanpa `week_of_month` gagal.
5. **`hijri_first_week`** — tersimpan dgn `calendar_system='hijri'`, `hari` wajib, label = "…pekan pertama tiap bulan Hijriah (1–7)".
6. **Accessor `recurrence_label`** — output teks sesuai tiap tipe.
7. **Otorisasi** — pemilik majelis via kelola-majelis tidak dapat menyetel tipe non-weekly (input diabaikan / tetap `weekly`).

Catatan: PHP CLI mesin ini tanpa `pdo_sqlite` — jalankan test sesuai catatan memori proyek (lihat `php-cli-no-sqlite`).

---

## Acceptance Criteria

- [ ] Migration baru menambah 5 kolom; `php artisan migrate` & `migrate:rollback` sukses; data existing tak berubah.
- [ ] `Schedule` memiliki konstanta tipe, cast, dan accessor `recurrence_label` yang menghasilkan teks Indonesia benar untuk 5 tipe.
- [ ] Kontributor dapat membuat & mengedit jadwal dengan kelima tipe via dasbor kontribusi; masuk moderasi `pending` dan tampil setelah `approved`.
- [ ] Admin dapat membuat jadwal dengan kelima tipe tanpa moderasi.
- [ ] Form pemilik majelis (kelola-majelis) tidak berubah dan tetap menyimpan `weekly`.
- [ ] Widget "Jadwal Hari Ini" hanya menampilkan jadwal `weekly`; jadwal berkala tidak bocor.
- [ ] Halaman daftar jadwal publik & halaman detail majelis menampilkan seksi/label "Jadwal Berkala" dengan teks aturan.
- [ ] Validasi conditional menolak kombinasi field yang tidak sah (lihat Testing #4).
- [ ] Feature test terkait lulus; `./vendor/bin/pint` bersih.

---

## Verifikasi End-to-End

1. `php artisan migrate` → pastikan kolom baru ada (`recurrence_type` default `weekly`).
2. Login sebagai **kontributor**, buka dasbor kontribusi → tambah jadwal `monthly_weekday` ("Minggu, pekan pertama") → submit → status `pending`, tidak tampil di daftar publik.
3. Login sebagai **admin** → moderasi jadwal tsb → **setujui** → jadwal tampil di **seksi "Jadwal Berkala"** pada daftar publik & detail majelis dengan teks "Minggu, pekan pertama tiap bulan".
4. Buka **homepage** pada hari yang sama dengan weekday jadwal berkala → pastikan jadwal berkala **tidak** muncul di "Jadwal Hari Ini"; buat satu jadwal `weekly` untuk hari ini → pastikan **muncul**.
5. Coba submit `monthly_date` tanpa `day_of_month` dan `semimonthly` dua pekan sama → pastikan **gagal** dengan pesan validasi, `old()` tetap terisi.
6. Buat `hijri_first_week` → pastikan tersimpan `calendar_system='hijri'` dan label menyebut "pekan pertama tiap bulan Hijriah (1–7)".
7. Jalankan Feature test → semua lulus. Jalankan `./vendor/bin/pint`.

---

## Risiko & Trade-off

| Risiko / Trade-off | Dampak | Mitigasi |
|---|---|---|
| **Teks aturan tanpa tanggal occurrence** | Jamaah tidak melihat "tanggal Masehi berikutnya" untuk jadwal berkala | Diterima untuk iterasi 1; occurrence otomatis dijadwalkan iterasi lanjutan (butuh library konversi Hijriah utk pola Hijriah) |
| **`HijriService` terbatas** | Tidak bisa validasi/verifikasi tanggal Hijriah nyata | Pola Hijriah disimpan sebagai aturan deklaratif; tidak ada ketergantungan konversi di iterasi 1 |
| **Field kondisional di form** | Kompleksitas UI (show/hide) & potensi data tidak konsisten | Validasi server-side conditional sebagai sumber kebenaran; Alpine hanya UX |
| **Pencarian berbasis `hari` untuk `monthly_date`** | Baris `hari=null` tak muncul di pencarian per-hari | Pencarian tetap via nama/guru/majelis; pastikan null-safe |
| **Dua tempat validasi (kontributor & admin)** | Duplikasi aturan | Pertimbangkan Form Request bersama / konstanta di model sebagai satu sumber kebenaran |
| **Enum sebagai string** | Nilai tak valid bisa masuk jika validasi terlewat | Validasi `in:` ketat + konstanta model |
| **Timezone tunggal (WITA) untuk wilayah WIB** | Batas "hari ini" bisa meleset ≤1 jam di Kalteng | Sudah menjadi perilaku existing; di luar scope fitur ini |

---

## Pertanyaan Terbuka (untuk iterasi berikut)

- Apakah perlu menampilkan **"occurrence berikutnya"** (tanggal Masehi) untuk pola Gregorian (dapat dihitung dgn Carbon tanpa dependency baru)?
- Untuk pola Hijriah, library/dependency konversi mana yang disetujui bila occurrence otomatis diperlukan?
- Apakah jadwal berkala perlu masuk **notifikasi/pengingat** (OneSignal) menjelang occurrence?
