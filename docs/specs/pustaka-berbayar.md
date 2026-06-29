# Spesifikasi: Pustaka Berbayar (Beli Per-Judul, Pembayaran Manual)

Status: Draft disetujui untuk implementasi
Tanggal: 2026-06-29
Penulis: hasil wawancara kebutuhan dengan pemilik produk

---

## 1. Ringkasan

Mengubah fitur **Pustaka** agar mendukung konten **berbayar per-judul** (sekali bayar, akses
selamanya). Pembayaran dilakukan **manual via WhatsApp admin**; setelah dana diterima, **Super
Admin** mengaktifkan akses pembeli dari panel admin. PDF pustaka berbayar **hanya bisa dibaca
online** (di-stream lewat rute terproteksi, tidak lewat URL publik, tanpa tombol unduh).

Pustaka **gratis tetap berperilaku seperti sekarang** (file di disk publik, bisa dibuka/diunduh).

Prioritas MVP: **mengamankan akses file berbayar** + alur permintaan/verifikasi penuh + riwayat
pembelian user + notifikasi setelah akses aktif.

---

## 2. Konteks & Perilaku Saat Ini

- Model `Library` (`app/Models/Library.php`) sudah memiliki kolom `price_type` enum `free|paid`
  (`database/migrations/2026_02_01_011425_create_libraries_table.php`), tetapi **belum dipakai untuk
  membatasi akses** — hanya jadi badge & filter di `app/Livewire/ListLibrary.php`.
- **Belum ada kolom harga.**
- File PDF disimpan di disk **`public`** pada `libraries/files/{uuid}.pdf`
  (`app/Http/Controllers/LibraryController.php@store`), diakses lewat `Storage::url($library->file_path)`
  di `resources/views/pages/user/library/detail.blade.php`. Artinya **URL bisa di-share & dibuka siapa
  saja tanpa login**; gating saat ini hanya menyembunyikan tombol di UI (`@auth`), bukan proteksi nyata.
- Rute publik: `pustaka-list` & `pustaka-detail` di `routes/web.php:71-72`
  (`app/Http/Controllers/User/LibraryController.php`).
- Rute admin: `Route::resource('/libraries', ...)` di `routes/web.php:220`.
- Fitur AI (Podcast AI & Chat Open Notebook) ada pada pustaka tertentu — **di luar scope** spek ini.

---

## 3. Keputusan Produk (hasil wawancara)

| Aspek                        | Keputusan                                                                                  |
| ---------------------------- | ------------------------------------------------------------------------------------------ |
| Model monetisasi             | Beli per-judul, **sekali bayar, akses selamanya**                                           |
| Mekanisme pembayaran         | **Manual via WhatsApp admin** (gateway ditunda — belum ada dokumen usaha resmi)            |
| Penetapan harga              | **Per judul**, admin input nominal (kolom `price` baru)                                     |
| Akses file setelah beli      | **Baca online saja** (stream terproteksi, tanpa unduh)                                      |
| Pencatatan permintaan        | **Ya** — klik "Beli" membuat record `pending`, admin verifikasi → `active`                  |
| Nomor WhatsApp               | **Satu nomor admin global** (dari config/env)                                               |
| Otorisasi verifikasi         | **Hanya Super Admin**                                                                       |
| Fitur AI pada pustaka bayar  | Di luar scope (belum ada pustaka berbayar yang pakai AI)                                    |
| PDF gratis lama              | **Tetap seperti sekarang** (disk publik, bisa diunduh)                                      |
| Ubah status setelah ada beli | Pembeli lama **tetap akses selamanya** (detail teknis dibahas saat implementasi)           |
| Cakupan test                 | **Luas / alur penuh**                                                                       |
| Notifikasi                   | Push **OneSignal** ke pembeli saat akses diaktifkan                                         |
| Riwayat pembelian            | Halaman "Pustaka Saya" untuk user                                                           |

**Catatan trade-off yang disepakati:** Mencegah _direct download_ (URL publik yang bisa di-share)
bisa dijamin. Mencegah user yang sudah berhak membaca untuk menyalin isi (screenshot, print-to-PDF,
dev tools) **tidak mungkin 100%**. Target realistis: file tidak lagi berada di URL publik; hanya
pembeli (atau admin) yang dapat membukanya lewat viewer; tombol unduh disembunyikan.

---

## 4. Data Model

### 4.1 Perubahan tabel `libraries` (migration baru, additive)

Tambah kolom:

```php
$table->unsignedInteger('price')->nullable()->after('price_type'); // nominal Rupiah; wajib bila price_type = paid
```

Backward-compatible: kolom nullable, tidak mengubah baris lama. Jangan ubah migration lama.

### 4.2 Tabel baru `library_purchases` (entitlement + jejak permintaan)

```php
Schema::create('library_purchases', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('library_id')->constrained()->cascadeOnDelete();
    $table->enum('status', ['pending', 'active', 'rejected'])->default('pending');
    $table->unsignedInteger('price'); // snapshot harga saat permintaan dibuat
    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete(); // admin yang memverifikasi
    $table->timestamp('verified_at')->nullable();
    $table->text('admin_note')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'library_id']);
});
```

- **Snapshot `price`**: harga disimpan saat permintaan, sehingga perubahan harga pustaka tidak
  memengaruhi catatan/akses pembeli lama.
- **Entitlement = baris berstatus `active`.** Akses selamanya: tidak ada kolom kedaluwarsa.
- Aturan unik diberlakukan di **level aplikasi** (bukan unique index DB), karena seorang user yang
  ditolak (`rejected`) boleh mengajukan ulang: tidak boleh ada >1 baris `pending` **atau** `active`
  untuk pasangan (user, library) yang sama.

### 4.3 Model & relasi

- `app/Models/LibraryPurchase.php` (baru): `$fillable` untuk `user_id, library_id, status, price,
  verified_by, verified_at, admin_note`; cast `verified_at => datetime`; relasi `user()`, `library()`,
  `verifier()`.
- `App\Models\Library`: tambah `price` ke `$fillable`; helper `isFree()`, `isPaid()`,
  `purchases()` (hasMany), dan `isAccessibleBy(?User $user): bool` (true bila gratis, atau user admin,
  atau user punya purchase `active`).
- `App\Models\User`: relasi `libraryPurchases()` (hasMany) dan helper
  `hasActiveLibraryPurchase(Library $library): bool`.

---

## 5. Proteksi File

### 5.1 Penyimpanan

- **Pustaka gratis**: tetap di disk `public` (`libraries/files/...`) — tidak diubah.
- **Pustaka berbayar**: PDF disimpan di disk **privat** (`local`) pada `libraries/paid/{uuid}.pdf`,
  **tidak** dapat diakses via `Storage::url()`.
- Saat admin membuat/mengupdate pustaka:
  - `price_type = paid` → simpan/migrasi file ke disk `local` (privat).
  - `price_type = free` → simpan ke disk `public` (perilaku lama).
  - Saat mengubah `price_type`, file dipindah antar-disk dan `file_path` diperbarui. (Cover image
    tetap publik di kedua kasus.)
- **Data lama**: jika ada pustaka `paid` yang filenya masih di disk publik, sediakan migrasi/command
  satu kali untuk memindahkannya ke disk privat. (Saat ini diasumsikan belum ada konten berbayar riil;
  bila ada, ini wajib dijalankan.)

### 5.2 Rute streaming terproteksi

Rute user baru (di grup `auth:sanctum` + `verified`):

```
GET  /pustaka/{library}/baca   → User\LibraryController@read   (name: pustaka-read)
```

Perilaku `read()`:

1. Tolak jika `! $library->isAccessibleBy(Auth::user())` → `abort(403)`.
2. Untuk pustaka gratis, boleh redirect ke URL publik lama **atau** tetap di-stream (pilih stream agar
   seragam; tidak wajib).
3. Untuk pustaka berbayar, **stream** file dari disk `local` dengan
   `Content-Disposition: inline`, header `Cache-Control: private, no-store`. Gunakan
   `Storage::disk('local')->response($path, $name, [...headers], 'inline')`.
4. Halaman pembaca menampilkan PDF inline (viewer browser / PDF.js embed). **Tombol unduh
   disembunyikan**; UI tidak menyediakan link unduh.

> Catatan: `abort(403)` mencegah akses langsung walau URL `/pustaka/{slug}/baca` di-share, karena
> pengecekan entitlement berjalan per-request.

---

## 6. Alur Pengguna (User Flow)

### 6.1 Halaman detail pustaka (`resources/views/pages/user/library/detail.blade.php`)

Logika tombol aksi (menggantikan blok `@auth ... Download/Baca PDF`):

- **Pustaka gratis** → perilaku lama: jika login tampilkan "Baca PDF", jika guest "Login untuk Baca".
- **Pustaka berbayar**:
  - **Guest** → tombol "Login untuk Membeli" → `route('login')`.
  - **Login, belum punya purchase** → tampilkan harga (format Rupiah) + tombol **"Beli (Rp X)"**.
  - **Login, ada purchase `pending`** → badge **"Menunggu verifikasi pembayaran"** (tombol Beli
    nonaktif) + tautan WhatsApp untuk konfirmasi ulang.
  - **Login, ada purchase `active`** → tombol **"Baca"** → `route('pustaka-read', $library)`.
  - **Admin** → selalu bisa "Baca".

### 6.2 Aksi "Beli"

Rute user baru (grup `auth:sanctum` + `verified`):

```
POST /pustaka/{library}/beli   → User\LibraryController@purchase   (name: pustaka-purchase)
```

Perilaku `purchase()`:

1. Validasi: `library->isPaid()`; jika gratis → `abort(404)`/redirect.
2. Cegah duplikat: jika sudah ada purchase `pending`/`active` untuk (user, library) → tidak buat baru,
   langsung lanjut ke langkah 4 dengan record yang ada.
3. Buat `LibraryPurchase` `status = pending`, `price = library->price` (snapshot).
4. Redirect ke **link WhatsApp** `https://wa.me/{nomor_admin}?text={pesan}` di mana pesan ter-prefill:
   judul pustaka, harga, nama user, dan ID permintaan — mengikuti pola tautan `wa.me` yang sudah
   dipakai di `resources/views/pages/user/post/detail.blade.php`.

### 6.3 Halaman "Pustaka Saya" (riwayat pembelian)

Rute user baru (grup `auth:sanctum` + `verified`):

```
GET /pustaka-saya   → User\LibraryController@myLibraries   (name: pustaka-saya)
```

Menampilkan daftar `LibraryPurchase` milik user dengan status (Menunggu / Aktif / Ditolak) dan tautan
"Baca" untuk yang `active`. Tambahkan entri menu di area user yang relevan.

---

## 7. Alur Admin

Mengikuti pola `AdminRewardClaimController` (`routes/web.php:233-235`) — daftar + aksi
approve/reject.

Rute admin baru (grup `is_admin`, prefix `admin`):

```
GET   /admin/library-purchases                      → Admin\LibraryPurchaseController@index    (name: admin.library-purchases.index)
PATCH /admin/library-purchases/{purchase}/activate  → Admin\LibraryPurchaseController@activate  (name: admin.library-purchases.activate)
PATCH /admin/library-purchases/{purchase}/reject    → Admin\LibraryPurchaseController@reject    (name: admin.library-purchases.reject)
```

- `index`: tabel permintaan dengan filter status (default tampilkan `pending` lebih dulu), kolom:
  user, pustaka, harga, tanggal, status, aksi.
- `activate`: set `status = active`, `verified_by = Auth::id()`, `verified_at = now()`; **kirim push
  OneSignal** ke user (lihat §8). Idempoten: aktivasi atas record non-`pending` tidak menggandakan
  notifikasi.
- `reject`: set `status = rejected`, isi `admin_note` opsional, `verified_by`/`verified_at`. User boleh
  mengajukan ulang setelah ditolak.
- Form **create/edit pustaka** (`resources/views/pages/libraries/*`,
  `app/Http/Controllers/LibraryController.php`): tambah input **harga** yang **wajib saat `price_type =
  paid`** (validasi `required_if:price_type,paid|nullable|integer|min:0`), dan tangani penyimpanan file
  ke disk privat untuk paid.

Otorisasi: seluruh rute di atas dilindungi middleware `is_admin` (`Super Admin`). Tidak ada role baru.

---

## 8. Notifikasi

Saat `activate`, panggil `OneSignalService::sendNotification(...)`
(`app/Services/OneSignalService.php`):

- `title`: "Akses Pustaka Aktif"
- `message`: "Pembelian \"{judul}\" telah aktif. Selamat membaca."
- `userIds`: `[$purchase->user_id]`
- `url`: `route('pustaka-detail', $purchase->library)`

Service sudah menangani kasus kredensial kosong (return `false` + log) — kegagalan push **tidak boleh
menggagalkan** aktivasi (bungkus agar non-fatal, jangan empty-catch tanpa log).

---

## 9. Konfigurasi

Tambah ke `config/services.php`:

```php
'whatsapp' => [
    'admin_number' => env('WHATSAPP_ADMIN_NUMBER'), // format internasional tanpa '+', mis. 62812xxxx
],
```

Dan dokumentasikan `WHATSAPP_ADMIN_NUMBER` di `.env.example` / bagian Environment Variables CLAUDE.md.

---

## 10. File / Interface Terkait

Akan dibuat:

- `database/migrations/xxxx_add_price_to_libraries_table.php`
- `database/migrations/xxxx_create_library_purchases_table.php`
- `app/Models/LibraryPurchase.php`
- `app/Http/Controllers/Admin/LibraryPurchaseController.php`
- `resources/views/pages/admin/library-purchases/index.blade.php` (atau lokasi admin sejenis)
- `resources/views/pages/user/library/my-libraries.blade.php`
- `resources/views/pages/user/library/read.blade.php` (viewer PDF inline) — bila tidak stream langsung
- `tests/Feature/...` (lihat §13)

Akan diubah:

- `app/Models/Library.php` (fillable `price`, helper akses)
- `app/Models/User.php` (relasi & helper purchase)
- `app/Http/Controllers/User/LibraryController.php` (`purchase`, `read`, `myLibraries`)
- `app/Http/Controllers/LibraryController.php` (input harga + simpan file paid ke disk privat)
- `resources/views/pages/user/library/detail.blade.php` (logika tombol beli/baca/status)
- `resources/views/pages/libraries/{create,edit}.blade.php` (input harga)
- `routes/web.php` (rute `pustaka-read`, `pustaka-purchase`, `pustaka-saya`, admin purchases)
- `config/services.php` (`whatsapp.admin_number`)
- `CLAUDE.md` (dokumentasi env & ringkasan fitur, bila perilaku publik berubah)

---

## 11. Di Luar Scope (Non-Goals)

- Payment gateway otomatis (Midtrans/Xendit) — ditunda sampai ada dokumen usaha resmi.
- Langganan / akses-semua, donasi, kode promo, refund, bundling.
- Gating berbayar untuk **Podcast AI** & **Chat AI (Open Notebook)**.
- Upload bukti transfer di dalam aplikasi (konfirmasi dilakukan via WhatsApp di luar app).
- DRM/anti-screenshot/watermark dinamis pada PDF.
- Mengubah perilaku akses pustaka **gratis** yang sudah ada.
- Role/permission baru selain `Super Admin`.

---

## 12. Edge Cases

1. **Klik "Beli" berulang** saat sudah `pending`/`active` → tidak membuat record baru; arahkan ke
   status/WhatsApp yang sesuai.
2. **Ajukan ulang setelah `rejected`** → diperbolehkan; buat record `pending` baru.
3. **Harga berubah** setelah pembeli ada → pembeli lama memakai snapshot `price`; akses tetap.
4. **Pustaka `paid` → `free`** → semua pembeli lama tetap punya akses; kini terbuka untuk semua.
5. **Pustaka `free` → `paid`** → pembaca lama (tanpa purchase) harus membeli; file dipindah ke disk
   privat.
6. **Akses langsung URL `/pustaka/{slug}/baca`** tanpa hak → `403`.
7. **Pustaka berbayar tanpa harga** → dicegah di validasi admin (`required_if`).
8. **Pustaka nonaktif (`is_active = false`)** → tidak tampil publik; akses baca tetap dicek
   entitlement.
9. **Admin** selalu boleh membaca tanpa membeli.
10. **OneSignal gagal/credential kosong** → aktivasi tetap sukses; kegagalan push hanya di-log.
11. **Penghapusan user/pustaka** → `library_purchases` ikut terhapus (cascade) sesuai FK.

---

## 13. Strategi Testing (cakupan luas / alur penuh)

Gunakan `RefreshDatabase` + `Storage::fake()` mengikuti pola `tests/Feature/LibraryTest.php`.

**Otorisasi & akses file (inti):**

- Pustaka gratis: publik tetap bisa lihat detail & baca (perilaku lama tidak berubah).
- Pustaka berbayar — user tanpa purchase: `GET pustaka-read` → `403`.
- Pustaka berbayar — user dengan purchase `pending`: `GET pustaka-read` → `403`.
- Pustaka berbayar — user dengan purchase `active`: `GET pustaka-read` → `200` + stream file.
- Admin: `GET pustaka-read` pustaka berbayar → `200` tanpa membeli.
- File pustaka berbayar **tidak** tersedia di disk `public` (tidak ada URL publik).

**Alur permintaan:**

- `POST pustaka-purchase` pustaka berbayar → membuat 1 record `pending` dengan `price` snapshot &
  redirect ke link WhatsApp berisi nomor admin dari config.
- `POST pustaka-purchase` saat sudah `pending`/`active` → tidak menambah record.
- `POST pustaka-purchase` pustaka gratis → ditolak.

**Alur admin:**

- Non-admin tidak bisa akses `admin.library-purchases.*` (`403`).
- `activate` → status `active`, `verified_by`/`verified_at` terisi, push OneSignal terpanggil (fake
  HTTP).
- `reject` → status `rejected`; user dapat mengajukan ulang.

**Admin form pustaka:**

- Buat pustaka `paid` tanpa harga → gagal validasi.
- Buat pustaka `paid` dengan harga → file tersimpan di disk privat, `price` tersimpan.

**Riwayat:**

- `GET pustaka-saya` menampilkan purchase milik user, dan tidak menampilkan milik user lain (anti-IDOR).

---

## 14. Acceptance Criteria

1. Admin dapat menandai pustaka sebagai `paid` dengan **harga** wajib; filenya tersimpan di disk privat
   dan **tidak** dapat diakses lewat URL publik.
2. User yang **belum membeli** tidak dapat membaca PDF pustaka berbayar (UI maupun akses langsung rute
   → `403`).
3. User dapat menekan **"Beli"**, sistem mencatat permintaan `pending`, lalu mengarahkan ke **WhatsApp
   admin** dengan pesan ter-prefill.
4. **Super Admin** dapat melihat daftar permintaan dan **mengaktifkan** atau **menolak**.
5. Setelah diaktifkan, user menerima **notifikasi OneSignal** dan dapat **membaca online** PDF (tanpa
   tombol unduh).
6. User memiliki halaman **"Pustaka Saya"** berisi riwayat pembelian beserta statusnya.
7. Pustaka **gratis** tetap berperilaku persis seperti sebelumnya.
8. Akses pembeli bersifat **selamanya** dan tidak terpengaruh perubahan harga/status pustaka.
9. Hanya **Super Admin** yang dapat memverifikasi pembayaran.
10. Seluruh test pada §13 lulus; `./vendor/bin/pint` & build aset lulus.

---

## 15. Verifikasi End-to-End (manual)

1. Login sebagai Super Admin → buat pustaka baru `price_type = paid`, harga `Rp25.000`, unggah PDF →
   simpan. Konfirmasi file tidak bisa dibuka via URL publik (`/storage/...` → 404/403).
2. Login sebagai user biasa (verified) → buka detail pustaka berbayar → tombol **"Beli (Rp25.000)"**.
3. Klik **Beli** → diarahkan ke WhatsApp admin dengan pesan berisi judul + harga + nama. Cek di DB:
   ada `library_purchases` `pending`.
4. Coba akses `/pustaka/{slug}/baca` langsung → **403**.
5. Sebagai Super Admin → buka **Permintaan Pustaka** → klik **Aktifkan**. User menerima push.
6. Sebagai user → buka detail → tombol berubah menjadi **"Baca"**; klik → PDF tampil inline, tanpa
   tombol unduh. Halaman **"Pustaka Saya"** menampilkan pustaka tersebut sebagai **Aktif**.
7. Sebagai user lain (belum beli) → akses `/pustaka/{slug}/baca` → **403**.
8. Buka pustaka **gratis** → tetap bisa dibuka/diunduh seperti sebelumnya.
