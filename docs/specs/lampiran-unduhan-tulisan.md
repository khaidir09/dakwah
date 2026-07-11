# Spesifikasi: Lampiran Unduhan pada Tulisan (Posts)

Status: Draft (disetujui melalui wawancara, belum diimplementasikan)
Tanggal: 2026-07-11
Penulis spesifikasi: hasil wawancara kebutuhan

## 1. Ringkasan & Kebutuhan Bisnis

Penulis (`Penulis` / `Super Admin`) ingin dapat melampirkan **satu dokumen** (PDF atau
gambar) pada sebuah tulisan. Pada halaman detail tulisan publik, dokumen ini dapat
**diunduh oleh pengguna**, dengan syarat **harus login** terlebih dahulu.

Tujuan bisnis:

- Menyediakan materi tambahan (mis. ringkasan kajian, dokumen resmi) yang dapat diunduh.
- Mendorong akuisisi pengguna: gate login mengubah pengunjung anonim menjadi akun
  terdaftar ketika mereka ingin mengunduh materi.

Fitur ini **meniru pola yang sudah ada** pada `ScientificArticle`
(route `artikel/{slug}/download`), diterapkan ke `Post`. Tidak ada abstraksi baru yang
diciptakan; kita mengikuti konvensi kolom + route download + gate `auth` yang sudah terbukti.

## 2. Keputusan yang Sudah Dikunci (hasil wawancara)

| Aspek | Keputusan |
| --- | --- |
| Jumlah lampiran | **Satu file per tulisan** (kolom tunggal di tabel `posts`) |
| Tipe file | **PDF, JPG/JPEG, PNG** |
| Ukuran maksimum | **10 MB** (`max:10240` KB) |
| Otorisasi unduh | **Semua user yang login** (mengikuti pola artikel; tanpa syarat `verified`) |
| Penyimpanan | **Disk lokal privat** — file tidak dapat diakses via URL publik, hanya lewat route download yang mengecek login |
| Tampilan guest | **Tampilkan tombol + ajakan login**; klik → diarahkan ke login, kembali ke halaman detail setelah login |
| Download counter | **Ya** — kolom `downloads_count`, di-increment tiap unduhan berhasil |
| Siklus file | **Hapus file lama dari disk** saat diganti, saat lampiran dihapus, dan saat tulisan dihapus; ada checkbox "hapus lampiran" pada form edit |
| Label lampiran | **Field label opsional** (`attachment_label`); simpan juga nama file asli untuk penamaan unduhan yang rapi |
| Testing | **Manual saja** (tanpa automated test) — lihat §11 |

## 3. Perilaku Saat Ini (baseline)

- `Post` tidak memiliki kolom lampiran. Kolom saat ini: `id, user_id, title, slug, content,
  cover_image, status, published_at, source, timestamps` (lihat migrasi
  `2026_02_13_094857_create_posts_table.php` + `2026_02_13_225148_add_source_to_posts_table.php`).
- Route detail publik: `GET /tulisan/{slug}` → `App\Http\Controllers\User\PostController@detail`
  (`routes/web.php:74`). Hanya menampilkan post `status = published`.
- Form create/edit tulisan (`resources/views/pages/post/create.blade.php`,
  `edit.blade.php`) dilayani oleh `App\Http\Controllers\PostController` (dual-route:
  `kelola-tulisan.*` untuk penulis, `posts.*` untuk admin). Form sudah `enctype="multipart/form-data"`
  dan sudah menangani upload `cover_image` (satu file `.webp`, disk `public`).
- **Preseden download ber-gate** yang menjadi acuan:
  `App\Http\Controllers\User\ArticleController@download` — `abort_if(!auth()->check(), 403, ...)`,
  cek `Storage::exists`, lalu `Storage::download($article->file_path)`. Route-nya memakai
  `->middleware('auth')` (`routes/web.php:76-78`). Tombol unduh di
  `resources/views/pages/user/article/detail.blade.php:106-112`.

## 4. Perilaku yang Diharapkan (expected behavior)

### 4.1 Penulis membuat / mengedit tulisan
- Form create & edit menampilkan input file **Lampiran (PDF/Gambar)** + input teks
  **Label Lampiran (opsional)**.
- Saat menyimpan: file diunggah ke disk privat, path + nama file asli + label disimpan.
- Pada form edit: bila sudah ada lampiran, tampilkan nama file saat ini + checkbox
  **"Hapus lampiran"**. Mengunggah file baru **mengganti** file lama (file lama dihapus).
  Mencentang "Hapus lampiran" tanpa mengunggah file baru **menghapus** lampiran.

### 4.2 Pengunjung di halaman detail tulisan
- Jika post memiliki lampiran, tampilkan blok "Dokumen Terkait" berisi label/nama file.
- **User login**: tombol **Unduh** aktif → menuju route download.
- **Guest (belum login)**: tombol tetap terlihat, tetapi mengarah ke halaman login
  (`route('login')`) dengan mekanisme redirect-back sehingga setelah login pengguna
  kembali ke halaman detail. Sertakan teks ajakan mis. "Login untuk mengunduh".

### 4.3 Proses unduh
- Route download memvalidasi login (middleware `auth` + guard `abort_if` di controller).
- Jika file tidak ada di disk → `404`.
- Unduhan berhasil → `downloads_count` di-increment, file dikirim dengan nama asli
  (`attachment_filename`) via `Storage::download($path, $namaAsli)`.

## 5. Role & Otorisasi

| Aksi | Siapa |
| --- | --- |
| Upload/ganti/hapus lampiran | `Super Admin` atau `Penulis` **pemilik** tulisan (sama persis dengan aturan edit tulisan yang ada di `PostController::update`) |
| Melihat blok lampiran di detail | Publik (semua orang) |
| Mengunduh file | **Semua user yang sudah login** (tanpa syarat `verified`, tanpa syarat kepemilikan) |

- Route download memakai `->middleware('auth')` + `abort_if(!auth()->check(), 403, ...)`
  (defensif ganda, mengikuti pola `ArticleController`).
- Otorisasi upload **tidak** menambah aturan baru — memanfaatkan pengecekan role/kepemilikan
  yang sudah ada di `PostController::store/update`.

## 6. Data Model

Tambahkan **migrasi baru** (jangan ubah migrasi lama). Nama saran:
`xxxx_xx_xx_add_attachment_to_posts_table.php`.

Kolom baru pada tabel `posts`:

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `attachment_path` | `string` nullable | Path relatif di disk privat, mis. `post-attachments/{uuid}.pdf` |
| `attachment_filename` | `string` nullable | Nama file asli saat diunggah (untuk penamaan unduhan) |
| `attachment_label` | `string` nullable | Label tampilan opsional dari penulis |
| `downloads_count` | `unsignedInteger` default `0` | Jumlah unduhan berhasil |

- `Post` menggunakan `$guarded = ['id']`, jadi kolom baru otomatis mass-assignable — tidak
  perlu ubah `$fillable`. Tambahkan (opsional) accessor helper `hasAttachment()` jika membantu
  view, tetapi **tidak wajib**.
- Migrasi bersifat backward-compatible (semua kolom nullable / punya default). `down()`
  melakukan `dropColumn` keempat kolom.

## 7. Penyimpanan File

- Disk: **`local`** (privat) — sama dengan default disk yang dipakai `ScientificArticle`
  (`Storage::download`/`Storage::exists` tanpa argumen disk memakai `filesystems.default`).
  Konfirmasi `config/filesystems.php` `default` = `local` sebelum implementasi; jika berbeda,
  gunakan `Storage::disk('local')` secara eksplisit di seluruh alur upload/download/hapus agar
  konsisten.
- Folder: `post-attachments/`. Nama file: `Str::uuid()` + ekstensi asli (dipertahankan agar
  MIME/serving benar; PDF tidak dikonversi).
- **Penting**: berbeda dari `cover_image` yang memakai disk `public`. Lampiran **tidak boleh**
  di disk `public`, karena akan bisa diunduh tanpa login (gate terlewati). Ini bagian keamanan inti.

## 8. API / UI — File & Interface Terkait

### 8.1 Route (routes/web.php)
Tambah **satu** route publik (dengan gate auth), diletakkan berdekatan dengan
`tulisan.detail`:

```
Route::get('/tulisan/{slug}/download', [User\PostController::class, 'download'])
    ->name('tulisan.download')
    ->middleware('auth');
```

### 8.2 Controller
- `app/Http/Controllers/User/PostController.php` — **tambah** method `download($slug)`:
  meniru `ArticleController@download`, dengan tambahan `increment('downloads_count')` dan
  penamaan file asli. Hanya post `published()` yang bisa diunduh.
- `app/Http/Controllers/PostController.php` (`store` & `update`) — **tambah**:
  - Aturan validasi lampiran (§9).
  - Simpan file ke disk privat + isi `attachment_path`, `attachment_filename`,
    `attachment_label`.
  - `update`: tangani penggantian file (hapus lama) dan checkbox `remove_attachment`.
  - `destroy`: hapus file lampiran dari disk saat post dihapus (analog penghapusan `cover_image`).

### 8.3 View
- `resources/views/pages/post/create.blade.php` — tambah input `file` (name
  `attachment`, `accept="application/pdf,image/*"`) + input teks `attachment_label`.
- `resources/views/pages/post/edit.blade.php` — idem, plus tampilan nama file saat ini +
  checkbox `remove_attachment`.
- `resources/views/pages/user/post/detail.blade.php` — blok "Dokumen Terkait":
  bercabang `@auth` (tombol unduh ke `tulisan.download`) vs `@guest` (tombol ke `login`
  dengan redirect-back). Gunakan gaya tombol yang sama dengan blok download artikel.

### 8.4 Interface/kontrak yang dipertahankan
- Struktur route & controller dual-route `PostController` tetap (jangan pisah controller —
  ini pengecualian yang sudah didokumentasikan di CLAUDE.md).
- Nama route publik baru: `tulisan.download`.

## 9. Validasi

Tambahkan ke aturan `validate()` di `store` dan `update`:

```
'attachment'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
'attachment_label' => 'nullable|string|max:255',
'remove_attachment'=> 'nullable|boolean', // hanya relevan di update
```

- `max:10240` = 10 MB (satuan KB di Laravel).
- Catatan operasional: pastikan `upload_max_filesize` dan `post_max_size` di PHP ≥ 10 MB,
  jika tidak upload akan gagal sebelum mencapai validasi Laravel.

## 10. Edge Cases

1. **Guest klik unduh** → diarahkan ke login, setelah login kembali ke halaman detail
   (bukan langsung memicu unduhan otomatis — cukup kembali ke halaman detail).
2. **File hilang di disk** (record ada, file tidak) → `404` "File tidak ditemukan".
3. **Post `draft`/belum publish** → route download hanya melayani `published()`; draft
   mengembalikan `404` walau user login. Preview lampiran untuk draft = di luar scope.
4. **Ganti lampiran** → file lama dihapus sebelum menyimpan yang baru. Jika penghapusan
   file lama gagal (mis. file sudah tidak ada), jangan gagalkan penyimpanan — lanjutkan.
5. **Checkbox hapus + unggah file baru bersamaan** → prioritaskan file baru (ganti), abaikan
   checkbox.
6. **Label diisi tanpa file** → label diabaikan bila tidak ada lampiran (label tanpa file
   tidak bermakna). Simpan label hanya bila ada `attachment_path`.
7. **Hapus tulisan** → hapus `attachment_path` (dan `cover_image` seperti sekarang) dari disk.
8. **MIME menyesatkan** (ekstensi .pdf tapi bukan PDF) → validasi `mimes` Laravel memeriksa
   MIME asli; cukup andalkan itu.
9. **downloads_count concurrency** → gunakan `increment()` (atomic di level DB), aman untuk
   unduhan paralel.

## 11. Testing (Manual)

Tidak ada automated test (keputusan wawancara). Checklist verifikasi manual di §14.
Jika di kemudian hari ingin ditambah automated test, ikuti gaya `tests/Feature/PostTest.php`
dan `Storage::fake('local')`.

## 12. Compatibility

- **Backward-compatible**: semua kolom baru nullable/berdefault; tulisan lama tanpa lampiran
  tetap berfungsi (blok "Dokumen Terkait" tidak muncul).
- Tidak mengubah migrasi lama, tidak menghapus kolom/tabel/data.
- Tidak mengubah dependency; hanya memakai `Storage`, `Str`, validasi bawaan Laravel.
- Tidak menyentuh alur `cover_image` yang ada (disk `public` tetap untuk cover).

## 13. Risiko & Trade-off

| Risiko / Trade-off | Mitigasi |
| --- | --- |
| Disk privat menambah beban server (streaming via PHP, bukan file statis) | Dapat diterima untuk volume saat ini; ini konsekuensi wajib dari gate login. Sama seperti artikel ilmiah. |
| Batas upload PHP < 10 MB menyebabkan upload gagal senyap | Dokumentasikan kebutuhan `upload_max_filesize`/`post_max_size`; verifikasi di server. |
| File yatim jika proses hapus terputus | Cleanup pada replace/remove/destroy; low impact. |
| Guest tidak auto-download setelah login (hanya kembali ke halaman) | Trade-off kesederhanaan; cukup baik untuk tujuan akuisisi. |
| Single-file membatasi kasus multi-lampiran | Diterima sekarang; bila perlu banyak file, migrasi ke tabel `post_attachments` di masa depan. |

## 14. Acceptance Criteria

1. Migrasi menambahkan `attachment_path`, `attachment_filename`, `attachment_label`,
   `downloads_count` ke `posts` dan dapat di-`migrate`/`rollback` tanpa error.
2. Penulis/Admin dapat mengunggah PDF/JPG/PNG ≤ 10 MB pada create & edit; file tersimpan di
   disk privat `post-attachments/`, **tidak** di `public`.
3. Halaman detail tulisan menampilkan blok "Dokumen Terkait" hanya jika ada lampiran.
4. Guest melihat tombol unduh yang mengarah ke login; setelah login kembali ke halaman detail.
5. User login dapat mengunduh; file terunduh dengan nama file asli.
6. `downloads_count` bertambah 1 setiap unduhan berhasil.
7. Mengganti lampiran menghapus file lama; mencentang "Hapus lampiran" menghapus lampiran &
   filenya; menghapus tulisan menghapus filenya.
8. File yang hilang di disk menghasilkan `404`, bukan error 500.
9. Lampiran pada tulisan `draft` tidak dapat diunduh (`404`).
10. Tidak ada perubahan di luar scope; `./vendor/bin/pint` bersih; validasi menolak file
    > 10 MB / tipe tidak valid.

## 15. Verifikasi End-to-End (manual)

1. `php artisan migrate` → kolom baru ada.
2. Login sebagai `Penulis`, buat tulisan baru berisi lampiran PDF + label "Materi Kajian",
   status `published`. Verifikasi file muncul di `storage/app/post-attachments/`, **bukan**
   di `storage/app/public/`.
3. Logout. Buka `/tulisan/{slug}` sebagai guest → blok "Dokumen Terkait" & tombol tampil;
   klik → diarahkan ke `/login`.
4. Coba akses `/tulisan/{slug}/download` langsung sebagai guest → diarahkan ke login / `403`.
5. Login sebagai user biasa (non-penulis), buka detail → klik Unduh → file terunduh bernama
   asli. Ulangi 2×, cek `downloads_count` bertambah di DB.
6. Sebagai penulis, edit tulisan: unggah PDF baru → file lama hilang dari disk, file baru ada.
7. Edit lagi: centang "Hapus lampiran" → simpan → kolom lampiran `null`, file terhapus, blok
   hilang dari detail.
8. Upload file 12 MB → ditolak validasi. Upload `.docx` → ditolak validasi.
9. Hapus record file di disk secara manual, lalu unduh → `404`.
10. Hapus tulisan yang punya lampiran → file lampiran terhapus dari disk.
11. `./vendor/bin/pint` → tidak ada pelanggaran style pada file yang diubah.

## 16. Di Luar Scope (Non-Goals)

- Lampiran lebih dari satu file per tulisan / tabel `post_attachments`.
- Preview/viewer in-app (mis. PDF.js) — cukup unduh. (Viewer PDF.js adalah fitur pustaka, terpisah.)
- Auto-download otomatis setelah guest login (hanya kembali ke halaman detail).
- Pembatasan berbayar / kuota unduhan / expiry link.
- Perubahan pada alur `cover_image` atau pada `ScientificArticle`.
- Automated test (disepakati manual saja).
- Statistik unduhan per-user / audit trail siapa mengunduh.

## 17. Referensi Kode

- `app/Models/Post.php`
- `database/migrations/2026_02_13_094857_create_posts_table.php`
- `app/Http/Controllers/PostController.php` (store/update/destroy — dual-route)
- `app/Http/Controllers/User/PostController.php` (detail; +download)
- `app/Http/Controllers/User/ArticleController.php` (preseden download ber-gate)
- `resources/views/pages/user/article/detail.blade.php:106-112` (preseden tombol unduh)
- `resources/views/pages/post/create.blade.php`, `edit.blade.php`
- `resources/views/pages/user/post/detail.blade.php`
- `routes/web.php:74-78`
