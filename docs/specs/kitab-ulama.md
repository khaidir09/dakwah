# Spesifikasi: Kitab Ulama (Katalog Karya Ulama Terhubung ke Data Guru)

**Status Dokumen:** Disetujui & diimplementasikan (lihat §17 untuk penyesuaian saat implementasi)  
**Tanggal:** 2026-10-11  
**Author:** Muhammad Khaidir

---

## 1. Ringkasan

Menambahkan katalog **Kitab Ulama**, yaitu informasi karya kitab/buku para ulama, terutama ulama Kalimantan dalam silsilah Syekh Muhammad Arsyad Al-Banjari dan Abah Guru Sekumpul. Setiap kitab dihubungkan ke satu atau lebih data **guru** (`Teacher`) dengan **peran** (pengarang, pensyarah, penerjemah, pentahqiq). Status guru yang **masih hidup atau sudah wafat** diturunkan dari data wafat yang sudah ada.

Katalog ini berisi **metadata**, bukan file bacaan. Jika naskah digital kitab tersedia di Pustaka, kitab dapat **ditautkan secara opsional** ke entri `Library` yang sudah ada. Pengguna kemudian membaca atau membelinya lewat alur Pustaka yang sudah berjalan.

Fase 1 hanya untuk **Admin**. Jalur kontributor menjadi pekerjaan lanjutan.

---

## 2. Latar Belakang & Tujuan Bisnis

- Syaikhuna ingin menjadi sumber data terpusat dan terpercaya bagi kehidupan religius masyarakat Banjar. Karya tulis ulama, seperti *Sabilal Muhtadin*, *Tuhfatur Raghibin*, dan risalah-risalah ulama lokal, adalah bagian penting dari warisan keilmuan itu. Saat ini karya-karya tersebut belum tercatat di platform.
- Halaman per kitab yang terindeks (sitemap) menjadi pintu masuk SEO baru untuk akuisisi pengguna.
- Profil guru menjadi lebih kaya karena menampilkan bagian "Karya".

**Tujuan:**
1. Admin dapat mencatat kitab beserta relasinya ke satu atau lebih guru, lengkap dengan peran masing-masing.
2. Publik dapat menelusuri kitab di `/kitab`, membuka detail di `/kitab/{slug}`, dan melihat karya di profil guru.
3. Beranda menampilkan kitab unggulan.
4. Kitab yang naskahnya ada di Pustaka dapat diarahkan ke halaman Pustaka.

---

## 3. Perilaku Saat Ini

| Area | Kondisi |
|---|---|
| `Teacher` (`app/Models/Teacher.php`, tabel `teachers`) | Route key `slug`. Status hidup/wafat **tidak punya kolom khusus**; hanya ada `wafat_masehi` (date), `wafat_hijriah` (string), `wafat_hijriah_day/month/year`. Punya scope `publiclyVisible()` dan `isVisibleTo()` untuk alur kontribusi. |
| `Library` (`app/Models/Library.php`) | Entri PDF dengan `category` teks bebas, `is_active`, harga gratis/berbayar, dan podcast. **Tidak terhubung ke guru.** |
| `Specialization` (`app/Models/Specialization.php`, tabel `specializations`) | Tabel berisi `name` dan `description`, di-seed oleh `SpesialisasiSeeder` (TAFSIR, HADITS, AKIDAH, FIQIH, TASAWUF, AKHLAK, SIRAH NABAWIYAH, TARIKH ISLAM). Model kosong dan **belum dipakai di mana pun**. |
| `Livewire\Partials\TeacherSelect` | Picker guru tunggal. Pencarian **menyaring `wafat_masehi = null`**, sehingga guru yang sudah wafat tidak bisa dipilih. Pencarian juga belum memakai `publiclyVisible()`. |
| `GuruController::destroy()` (admin) | **Masih kosong (stub)**: admin saat ini tidak bisa menghapus guru. |
| Sitemap (`SitemapController`) | Memuat guru (`publiclyVisible`) dan pustaka. Kitab belum ada. |
| Entitas kitab/karya | **Belum ada.** |

---

## 4. Keputusan Produk (Hasil Wawancara)

| # | Keputusan |
|---|---|
| D-1 | Kitab adalah **entitas baru** (`Book`), dengan tautan **opsional** ke `Library` (`library_id` nullable). |
| D-2 | Relasi ke guru **many-to-many dengan peran**: `pengarang`, `pensyarah`, `penerjemah`, `pentahqiq`. |
| D-3 | Setiap guru terkait **wajib ada** di data guru (tidak ada nama penulis teks bebas). Minimal satu relasi guru per kitab. |
| D-4 | Fase 1 **hanya Admin** (`Super Admin`) yang boleh membuat, mengubah, dan menghapus. Belum ada alur kontributor. |
| D-5 | Atribut: judul (transliterasi), judul Arab, slug, deskripsi, bidang ilmu, bahasa, sampul, penerbit, status naskah, tautan eksternal. **Tahun penulisan tidak termasuk.** |
| D-6 | UI publik: bagian **"Karya"** di profil guru, halaman daftar **`/kitab`**, halaman detail **`/kitab/{slug}`** (masuk sitemap), dan **widget beranda**. |
| D-7 | Status wafat **diturunkan** dari data yang ada: wafat jika `wafat_masehi` **atau** `wafat_hijriah_year` terisi. Tidak ada kolom baru. Status ini dipakai sebagai label di samping nama dan sebagai filter di `/kitab`. |
| D-8 | Sistem **tidak membatasi** asal daerah guru. Kurasi menjadi tanggung jawab admin. |
| D-9 | Bidang ilmu memakai tabel **`specializations`** yang sudah ada (satu bidang per kitab, nullable). |
| D-10 | Sampul diproses dengan trait **`HandlesImageUploads`** (large + thumb) di folder `books/`. |
| D-11 | Guru yang masih terkait kitab **tidak boleh dihapus**. Proteksinya di level DB lewat FK `restrictOnDelete` pada pivot. |
| D-12 | Kitab punya **`is_active`** (draf/tayang). Hanya kitab aktif yang tampil ke publik. |
| D-13 | Widget beranda menampilkan kitab **`is_featured`** yang aktif, maksimal 6, urut terbaru. Widget disembunyikan jika tidak ada isinya. |
| D-14 | Penamaan: model `Book`, tabel `books` dan `book_teacher`, URL publik `/kitab`, label UI **"Kitab Ulama"**, admin di `/admin/kitab`. |
| D-15 | Publik **hanya melihat guru yang `publiclyVisible()`** pada kitab. Picker admin juga hanya menawarkan guru yang publik. |

---

## 5. Role & Authorization

| Aksi | Publik/Jamaah | Kontributor | Penulis | Super Admin |
|---|---|---|---|---|
| Lihat `/kitab`, `/kitab/{slug}` (kitab aktif) | ✅ | ✅ | ✅ | ✅ |
| Lihat kitab non-aktif (draf) via URL langsung | ❌ 404 | ❌ 404 | ❌ 404 | ✅ (pratinjau) |
| CRUD `/admin/kitab` | ❌ | ❌ | ❌ | ✅ (middleware `IsAdmin`) |

- Route admin wajib berada di grup `/admin` yang sudah memakai middleware `IsAdmin`. Jangan menambah pengecekan role alternatif.
- Publik tidak memiliki endpoint tulis.

---

## 6. Data Model

### 6.1 Migration baru: `create_books_table`

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `title` | string(255) | Judul transliterasi Latin, wajib. Contoh: "Sabilal Muhtadin". |
| `title_arabic` | string(255) nullable | Contoh: "سبيل المهتدين". Ditampilkan dengan `dir="rtl" lang="ar"`. |
| `slug` | string unique | Dibuat dari `title`. Bentrok slug diselesaikan dengan sufiks `-1`, `-2` (pola `Teacher::generateSlug`). |
| `description` | longText nullable | HTML dari WYSIWYG admin, **wajib melewati `clean()`** sebelum disimpan. |
| `specialization_id` | FK nullable → `specializations.id` | `nullOnDelete`. |
| `language` | string(30) nullable | Nilai tetap: `arab`, `arab_melayu`, `banjar`, `indonesia`, `lainnya`. Divalidasi dengan `Rule::in`. Label tampilan disimpan di satu tempat (konstanta model). |
| `cover_image` | string nullable | Path **large** (`books/large/{uuid}.webp`); thumb diambil lewat accessor `str_replace('large', 'thumb', …)`. |
| `publisher` | string(255) nullable | Penerbit atau keterangan cetakan. |
| `publication_status` | string(20) nullable | `manuskrip`, `tercetak`, `hilang`. |
| `external_url` | string(500) nullable | Tautan beli/baca eksternal; divalidasi `url`, hanya `http`/`https`. |
| `library_id` | FK nullable → `libraries.id` | `nullOnDelete`: jika entri pustaka dihapus, tautannya ikut hilang tanpa error. |
| `is_active` | boolean default false | Draf secara default. |
| `is_featured` | boolean default false | Untuk widget beranda. |
| `timestamps` | | |

Index: `is_active`, `(is_active, is_featured)`.

### 6.2 Migration baru: `create_book_teacher_table`

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | bigint PK | |
| `book_id` | FK → `books.id` | `cascadeOnDelete`: menghapus kitab ikut menghapus relasinya. |
| `teacher_id` | FK → `teachers.id` | **`restrictOnDelete`** (D-11). |
| `role` | string(20) | `pengarang`, `pensyarah`, `penerjemah`, `pentahqiq`. |
| `sort_order` | unsignedSmallInteger default 0 | Urutan tampil guru pada kitab. |
| `timestamps` | | |

Unique: `(book_id, teacher_id, role)`. Satu guru boleh punya dua peran berbeda pada kitab yang sama, tetapi peran yang sama tidak boleh dobel.

> Catatan SQLite (test): pastikan FK aktif (`DB_FOREIGN_KEYS`/default Laravel untuk SQLite sudah `true`) agar test restrict-on-delete bermakna.

### 6.3 Model & relasi

- **`App\Models\Book`** (baru)
  - `getRouteKeyName(): 'slug'`
  - `teachers(): BelongsToMany` → `Teacher` lewat `book_teacher`, `withPivot('role', 'sort_order')`, `withTimestamps()`, `orderByPivot('sort_order')`
  - `specialization(): BelongsTo`
  - `library(): BelongsTo`
  - `scopeActive($q)` → `where('is_active', true)`
  - `scopeFeatured($q)`
  - `getCoverThumbUrlAttribute()` / `getCoverUrlAttribute()`
  - `generateSlug($title, $ignoreId = null)`: duplikasi kecil dari `Teacher::generateSlug`. Logikanya **tidak** dipindah ke trait bersama karena itu refactor di luar scope.
  - Konstanta label: `LANGUAGES`, `PUBLICATION_STATUSES`, `ROLES`.
  - `isVisibleTo(?User $user): bool`: aktif → semua orang; non-aktif → hanya `Super Admin`.
- **`App\Models\Teacher`** (diubah, additive)
  - `books(): BelongsToMany` (kebalikan relasi di atas)
  - `isWafat(): bool` → `filled($this->wafat_masehi) || filled($this->wafat_hijriah_year)`
  - `scopeWafat($q)` / `scopeMasihHidup($q)` dengan logika yang **persis sama** dengan `isWafat()`
- **`App\Models\Specialization`** (diubah, additive): `books(): HasMany`.
- **`App\Models\Library`** (diubah, additive): `books(): HasMany`. Dipakai untuk menampilkan "Kitab terkait" di halaman Pustaka; lihat §10, ini opsional.

---

## 7. Expected Behavior

### 7.1 Admin — `/admin/kitab` (`BookController`, resource)

- **Index**: komponen Livewire tabel dengan pola `app/Livewire/Library.php`, berisi judul, judul Arab, guru (nama + peran), bidang, status aktif, unggulan, dan aksi. Ada pencarian judul serta filter aktif/draf.
- **Create/Edit**: form berisi semua atribut §6.1 plus **daftar guru berulang**. Setiap baris berisi picker guru dan dropdown peran; baris bisa ditambah/dihapus (Alpine). Picker memakai `TeacherSelect` dengan parameter baru (lihat §9.2) agar guru yang sudah wafat bisa dipilih dan hanya guru publik yang ditawarkan.
- **Validasi**
  - `title` required|max:255
  - `teachers` required|array|min:1
  - `teachers.*.teacher_id` required|exists:teachers,id dan guru tersebut harus `publiclyVisible` (closure rule atau `Rule::exists` dengan `where`)
  - `teachers.*.role` required|in:ROLES
  - Kombinasi `(teacher_id, role)` tidak boleh dobel di dalam satu request (cegah pelanggaran unique sebelum query)
  - `specialization_id` nullable|exists; `language`/`publication_status` nullable|in
  - `cover_image` nullable|image|max sesuai pola upload yang ada
  - `external_url` nullable|url:http,https|max:500
  - `library_id` nullable|exists:libraries,id
- **Simpan**: dalam `DB::transaction` — simpan `Book`, lalu `teachers()->sync()` dengan `role` dan `sort_order` = indeks baris. Karena satu guru bisa punya beberapa peran, `sync` biasa (keyed by teacher_id) **tidak cukup**: hapus pivot milik kitab lalu insert ulang di transaksi yang sama.
- **Ganti sampul**: upload baru → hapus file lama lewat `deleteImageWithThumbnail()` **setelah** penyimpanan berhasil.
- **Destroy**: hapus kitab beserta pivot (cascade) dan file sampul. Tidak menyentuh guru maupun pustaka.
- **Sidebar admin** (`resources/views/components/app/sidebar.blade.php`): tambah tautan "Kitab Ulama" di dekat menu Guru/Pustaka.

### 7.2 Publik — `/kitab` (daftar)

- Controller `App\Http\Controllers\User\BookController@list` merender halaman yang berisi komponen Livewire `ListKitab` (pola `ListLibrary`/`ListGuru`).
- Hanya kitab `active()`. Paginasi 12.
- Pencarian: `title`, `title_arabic`, dan nama guru terkait (hanya guru `publiclyVisible`).
- Filter: bidang ilmu (`specialization_id`), bahasa, status guru (**semua / ulama yang telah wafat / masih hidup**). Filter status guru berarti kitab yang memiliki **minimal satu guru** dengan status itu (`whereHas` + scope `wafat()`/`masihHidup()`).
- Kartu berisi sampul thumb (placeholder jika kosong), judul, judul Arab, nama pengarang utama (peran `pengarang` dengan `sort_order` terkecil; jika tidak ada, guru pertama), dan badge bidang ilmu.
- Eager load `teachers` (dengan constraint `publiclyVisible`) dan `specialization` untuk mencegah N+1.

### 7.3 Publik — `/kitab/{slug}` (detail)

- `abort_unless($book->isVisibleTo(Auth::user()), 404)`.
- Menampilkan judul, judul Arab (RTL), sampul large, bidang ilmu, bahasa, status naskah, penerbit, dan deskripsi (`{!! !!}`, sudah di-`clean()` saat simpan).
- **Daftar guru terkait** dikelompokkan per peran. Setiap guru menampilkan foto, nama yang menaut ke `/guru/{slug}`, dan label wafat: "rahimahullah" plus tahun wafat (Masehi jika ada, jika tidak tahun Hijriah) bila `isWafat()`. Guru yang tidak `publiclyVisible` **tidak ditampilkan**.
- Jika `library_id` terisi **dan** pustaka itu `is_active`, tampilkan tombol **"Baca di Pustaka"** ke `pustaka-detail` (badge "Berbayar" jika `isPaid()`). Akses tetap diatur sepenuhnya oleh alur Pustaka.
- Jika `external_url` terisi, tampilkan tombol "Tautan Eksternal" dengan `rel="noopener nofollow" target="_blank"`.
- SEO: `@section('title')`, `meta_description`, dan `meta_image` lewat `$seo` (pola `pages/user/library/detail.blade.php`). Kitab non-aktif yang dipratinjau admin mendapat `noindex`.
- Bagian "Kitab lain dari {pengarang}" boleh ditampilkan bila ringan (maks 4). **Opsional**, bukan acceptance criteria.

### 7.4 Profil guru — `/guru/{slug}`

- `User\GuruController::detail` memuat `$teacher->books()->active()->with('specialization')->get()`.
- Bagian **"Karya"** di `resources/views/pages/user/guru/detail.blade.php` menampilkan kartu kitab beserta label peran guru tersebut (contoh: "Pengarang", "Pensyarah"). Bagian ini **disembunyikan** jika kosong.
- Jika guru punya tampilan manaqib terpisah (`pages/user/biography/detail.blade.php`), bagian "Karya" **tidak** ditambahkan di sana pada fase ini (lihat §11).

### 7.5 Beranda — widget `HomeKitabUnggulan`

- Komponen Livewire baru `app/Livewire/HomeKitabUnggulan.php` dengan pola `HomeUpcomingHaul`, disisipkan di `resources/views/pages/user/home.blade.php`.
- Query: `Book::active()->featured()->latest()->take(6)` + eager load. Hasilnya di-cache singkat (mis. 10 menit) dengan key tetap; cache di-*forget* saat admin menyimpan atau menghapus kitab.
- Jika hasil kosong, komponen tidak merender apa pun.
- Tautan "Lihat semua" ke `/kitab`.

### 7.6 Sitemap

- `SitemapController` menambah entri `kitab-detail` untuk kitab `active()`, `changefreq` `monthly`, `lastmod` `updated_at`, mengikuti pola `entry()` yang sudah ada. Halaman `/kitab` masuk sebagai halaman statis.

---

## 8. Route

```php
// Publik (routes/web.php, dekat route guru/pustaka)
Route::get('/kitab', [UserBookController::class, 'list'])->name('kitab-list');
Route::get('/kitab/{book}', [UserBookController::class, 'detail'])->name('kitab-detail');

// Admin (di dalam grup /admin + IsAdmin)
Route::resource('/kitab', \App\Http\Controllers\BookController::class)->names('admin.kitab');
```

Nama route admin memakai prefix `admin.kitab.*` agar tidak bentrok dengan `kitab-list`/`kitab-detail` publik. Binding admin memakai slug (`Book` route key), sama seperti `LibraryController`.

---

## 9. File / Interface Terkait

### 9.1 File baru

| File | Isi |
|---|---|
| `database/migrations/2026_10_xx_000001_create_books_table.php` | §6.1 |
| `database/migrations/2026_10_xx_000002_create_book_teacher_table.php` | §6.2 |
| `app/Models/Book.php` | §6.3 |
| `app/Http/Controllers/BookController.php` | Admin CRUD, `use HandlesImageUploads` |
| `app/Http/Controllers/User/BookController.php` | `list`, `detail` |
| `app/Livewire/Kitab.php` + `resources/views/livewire/kitab.blade.php` | Tabel admin |
| `app/Livewire/ListKitab.php` + `resources/views/livewire/list-kitab.blade.php` | Daftar publik |
| `app/Livewire/HomeKitabUnggulan.php` + view | Widget beranda |
| `app/Livewire/Partials/BookTeacherRows.php` + view | Baris guru + peran di form admin |
| `resources/views/components/kitab/card.blade.php` | Kartu kitab bersama |
| `resources/views/pages/kitab/{index,create,edit,_form}.blade.php` | Halaman admin (`AppLayout`) |
| `resources/views/pages/user/kitab/{list,detail}.blade.php` | Halaman publik (`UserLayout`) |
| `tests/Feature/KitabAdminTest.php`, `tests/Feature/KitabPublicTest.php` | §13 |

### 9.2 File yang diubah (additive)

| File | Perubahan |
|---|---|
| `app/Models/Teacher.php` | `books()`, `isWafat()`, `scopeWafat()`, `scopeMasihHidup()` |
| `app/Models/Specialization.php` | `books()` |
| `app/Models/Library.php` | `books()` (opsional, lihat §7.3) |
| `app/Livewire/Partials/TeacherSelect.php` | Parameter mount baru `includeWafat = false` dan `onlyPublic = false`. **Default mempertahankan perilaku lama** (tetap menyaring `wafat_masehi = null`), sehingga pemakai yang ada tidak berubah. Form kitab mengirim `includeWafat: true, onlyPublic: true`. |
| `app/Http/Controllers/User/GuruController.php` | Muat `books` aktif di `detail()` |
| `resources/views/pages/user/guru/detail.blade.php` | Bagian "Karya" |
| `resources/views/pages/user/home.blade.php` | `<livewire:home-kitab-unggulan />` |
| `app/Http/Controllers/SitemapController.php` | Entri kitab |
| `routes/web.php` | §8 |
| `resources/views/components/app/sidebar.blade.php` | Menu admin |
| Navigasi publik (menu `UserLayout`) | Tautan "Kitab Ulama" bila ada menu jelajah yang memuat Guru/Pustaka |
| `CLAUDE.md` | Tambah baris `Book` di tabel Key Models dan `kitab` di daftar route publik |

### 9.3 Abstraksi yang dipakai ulang (tidak diduplikasi)

`HandlesImageUploads`, `clean()` (mews/purifier), `Teacher::publiclyVisible()`, `Library::isPaid()`, `SeoService` (`$seo`), `SitemapController::entry()`, `TeacherSelect`, `PublicPageTestCase::makeTeacher()`.

---

## 10. Edge Cases

| Kasus | Perilaku |
|---|---|
| Guru wafat hanya punya tanggal Hijriah | `isWafat()` true (lewat `wafat_hijriah_year`). Label menampilkan tahun Hijriah. |
| Guru wafat yang hanya punya `wafat_hijriah` teks lama tanpa `_year` | Dianggap masih hidup. Ini keterbatasan data; dicatat di §14. |
| Satu guru menjadi pengarang sekaligus pensyarah | Diizinkan (dua baris pivot berbeda peran). Di profil guru, kitab tampil **sekali** dengan dua label peran. |
| Peran sama dobel untuk guru yang sama | Ditolak validasi sebelum menyentuh DB. |
| Kitab yang semua gurunya kemudian berubah menjadi tidak publik (revoke kontribusi) | Kitab tetap tampil, bagian guru kosong, dan kartu daftar tidak menampilkan nama pengarang. Tidak error. |
| Admin mencoba menghapus guru yang punya kitab | FK `restrictOnDelete` menolak. `GuruController::destroy()` masih stub, jadi ini proteksi defensif untuk implementasi hapus di masa depan. Bila implementasi hapus guru dibuat nanti, ia **harus** menangkap kondisi ini dan menampilkan "Guru masih memiliki N kitab". |
| Entri Pustaka yang ditautkan dihapus | `library_id` menjadi null (`nullOnDelete`); tombol "Baca di Pustaka" hilang. |
| Pustaka yang ditautkan `is_active = false` | Tombol "Baca di Pustaka" disembunyikan. |
| Specialization dihapus | `specialization_id` menjadi null; badge bidang tidak tampil. |
| Tabel `specializations` kosong di suatu environment | Dropdown bidang kosong; kitab tetap bisa disimpan (nullable). Lihat §14. |
| Dua kitab dengan judul sama (misal syarah dan matan) | Slug diberi sufiks `-1`. |
| Ubah judul kitab | Slug **tidak** berubah otomatis agar URL yang sudah terindeks tidak rusak; admin bisa mengubah slug secara eksplisit di form edit. |
| Deskripsi berisi `<script>` | Dibuang oleh `clean()`. |
| `external_url` berisi `javascript:` | Ditolak validasi `url:http,https`. |
| Kitab draf diakses via URL publik | 404 untuk non-admin. |
| Teks Arab di dalam kartu | `dir="rtl" lang="ar"` pada elemen judul Arab saja, bukan seluruh kartu. |

---

## 11. Di Luar Scope (Non-Goals)

- Alur **kontributor** untuk menambah kitab (moderasi, XP). Ini pekerjaan lanjutan dengan pola `docs/specs/sistem-kontributor.md`.
- **Tahun penulisan** kitab.
- Penulis yang **tidak ada** di data guru (nama teks bebas).
- Upload **PDF/naskah** langsung ke kitab. Naskah hanya lewat tautan ke Pustaka.
- Pembatasan sistem berdasarkan provinsi Kalimantan.
- Kolom status hidup/wafat baru pada `teachers` atau backfill data wafat.
- Implementasi **hapus guru** (`GuruController::destroy()` tetap stub).
- Bagian "Karya" di halaman manaqib (`biography/detail`).
- Chat AI/RAG, podcast, like/simpan/favorit kitab, komentar kitab.
- API JSON/mobile untuk kitab.
- Pengelolaan (CRUD) master `specializations` dari UI admin.
- Refactor `generateSlug` menjadi trait bersama.

---

## 12. Compatibility

- Semua migration **additive** (dua tabel baru); tidak ada perubahan atau penghapusan kolom pada tabel lama. `down()` cukup men-drop dua tabel baru tersebut.
- `TeacherSelect` mempertahankan perilaku default; pemakai lama (form majelis/jadwal) tidak berubah.
- Relasi baru pada `Teacher`, `Library`, dan `Specialization` hanya menambah method.
- Skema `teachers` produksi pernah menyimpang dari migration. FK `book_teacher.teacher_id` hanya bergantung pada `teachers.id`, jadi aman. Pastikan engine tabel `teachers` produksi InnoDB (FK membutuhkannya); periksa sebelum deploy.
- Tidak ada dependency baru.

---

## 13. Testing

Test memakai SQLite in-memory dan `RefreshDatabase`; turunkan dari `PublicPageTestCase` bila butuh helper `makeTeacher()` dan `superAdmin()`.

### `tests/Feature/KitabAdminTest.php`
1. Non-admin (jamaah, Kontributor, Penulis) mendapat 403/redirect di `/admin/kitab`, `create`, `store`, `update`, `destroy`.
2. Admin membuat kitab dengan dua guru (pengarang + pensyarah) → baris `books` dan dua baris pivot dengan `sort_order` benar; sampul tersimpan di `books/large` dan `books/thumb` (`Storage::fake('public')`).
3. Validasi: tanpa guru → error `teachers`; `(teacher_id, role)` dobel → error; guru `contribution_status = pending` → error; `external_url` `javascript:` → error.
4. Satu guru dengan dua peran berbeda → tersimpan dua baris.
5. Update mengganti daftar guru → pivot lama hilang, pivot baru sesuai; ganti sampul → file lama terhapus.
6. Deskripsi berisi `<script>` → tersimpan tanpa script.
7. Destroy → kitab dan pivot hilang, file sampul terhapus, guru tetap ada.
8. Menghapus `Teacher` yang terkait kitab langsung via Eloquent → melempar `QueryException` (FK restrict).

### `tests/Feature/KitabPublicTest.php`
1. `/kitab` hanya menampilkan kitab aktif; draf tidak muncul.
2. `/kitab/{slug}` draf → 404 untuk tamu dan jamaah, 200 untuk Super Admin.
3. Detail menampilkan guru publik dengan tautan profil; guru non-publik tidak tampil.
4. Label wafat tampil untuk guru dengan `wafat_masehi` dan untuk guru yang hanya punya `wafat_hijriah_year`; tidak tampil untuk guru tanpa data wafat.
5. Filter status guru (wafat / masih hidup) di `ListKitab` (`Livewire::test`) mengembalikan kitab yang benar.
6. Pencarian berdasarkan nama guru dan judul Arab.
7. Tombol "Baca di Pustaka" muncul hanya jika `library_id` terisi dan pustaka aktif.
8. `/guru/{slug}` menampilkan bagian "Karya" berisi kitab aktif beserta label peran; bagian tidak ada jika guru tanpa kitab.
9. Widget beranda: hanya kitab aktif + unggulan, maksimal 6; tidak dirender saat kosong.
10. `/sitemap.xml` memuat URL kitab aktif dan tidak memuat kitab draf.

### Unit (boleh digabung ke feature test)
- `Teacher::isWafat()` dan scope `wafat()`/`masihHidup()` konsisten untuk keempat kombinasi data wafat.
- `TeacherSelect` default tetap menyaring guru wafat; dengan `includeWafat: true` guru wafat muncul; dengan `onlyPublic: true` guru pending tidak muncul.

### Perintah
```bash
php artisan test --filter=Kitab
php artisan test            # regresi penuh; catat test yang memang sudah gagal sebelumnya
./vendor/bin/pint --dirty
npm run build
```

---

## 14. Risiko & Trade-off

| Risiko / Trade-off | Mitigasi / Alasan |
|---|---|
| Status wafat diturunkan dari data yang tidak lengkap; guru wafat tanpa tanggal tercatat dianggap "masih hidup". | Diterima (D-7). Admin melengkapi data wafat di form guru. Filter diberi label "berdasarkan data wafat yang tercatat" bila perlu. Kolom eksplisit bisa ditambah nanti tanpa mengubah API karena logika terpusat di `isWafat()` dan scope. |
| Penulis wajib ada di data guru, sehingga ulama klasik non-lokal harus dibuat profilnya dulu dan profil itu ikut muncul di daftar guru. | Diterima (D-3) demi integritas data. Admin perlu menyiapkan data guru sebelum input kitab. |
| `TeacherSelect` diberi parameter baru; salah default akan mengubah form lain. | Default dibuat persis sama dengan perilaku lama, ditambah test regresi. |
| `specializations` mungkin belum di-seed di produksi. | Kolom nullable. Periksa `select count(*) from specializations` sebelum rilis; jalankan `SpesialisasiSeeder` bila kosong (seeder bukan template debris). |
| FK `restrictOnDelete` membuat implementasi hapus guru di masa depan harus menangani error. | Didokumentasikan di §10; proteksi data lebih penting. |
| Admin-only memperlambat pengisian data. | Fase 1 sengaja kecil; jalur kontributor dijadwalkan setelah fitur stabil. |
| Cache widget beranda bisa basi. | TTL pendek + forget saat simpan/hapus. |
| Duplikasi kecil `generateSlug`. | Lebih aman daripada refactor lintas model di luar scope. |

---

## 15. Acceptance Criteria

1. Super Admin dapat membuat, mengubah, dan menghapus kitab di `/admin/kitab` dengan semua atribut §6.1 dan minimal satu guru berperan. Role lain tidak bisa mengakses.
2. Satu kitab dapat memiliki banyak guru dengan peran berbeda, dan satu guru dapat memiliki beberapa peran pada kitab yang sama. Duplikasi `(guru, peran)` ditolak.
3. Picker guru di form kitab dapat memilih guru yang sudah wafat dan hanya menawarkan guru publik. Picker di form lain berperilaku sama seperti sebelumnya.
4. Sampul tersimpan sebagai `books/large/*.webp` + `books/thumb/*.webp`. Daftar memakai thumb, detail memakai large.
5. Deskripsi disanitasi dengan `clean()` sebelum disimpan.
6. `/kitab` menampilkan kitab aktif dengan pencarian (judul, judul Arab, nama guru) dan filter (bidang ilmu, bahasa, status wafat/masih hidup), berpaginasi.
7. `/kitab/{slug}` menampilkan detail lengkap, guru publik dikelompokkan per peran dengan label wafat yang benar, tombol Pustaka (bila tertaut dan aktif), dan tombol tautan eksternal. Kitab draf 404 bagi non-admin.
8. `/guru/{slug}` menampilkan bagian "Karya" (kitab aktif + peran) dan menyembunyikannya bila kosong.
9. Beranda menampilkan maksimal 6 kitab aktif yang unggulan; widget tidak tampil bila tidak ada.
10. `/sitemap.xml` memuat kitab aktif saja.
11. Guru yang terhubung ke kitab tidak dapat dihapus di level DB.
12. Semua test baru lulus; tidak ada test baru yang gagal dibanding baseline; `pint` dan `npm run build` lulus.
13. `CLAUDE.md` diperbarui (Key Models + route publik).

---

## 16. Verifikasi End-to-End (Manual)

Prasyarat: MySQL Laragon menyala, `php artisan migrate`, `npm run dev`, `php artisan serve`. Pastikan `specializations` terisi.

1. **Admin create**: login sebagai Super Admin → `/admin/kitab/create`. Isi "Sabilal Muhtadin" / "سبيل المهتدين", bidang FIQIH, bahasa Arab-Melayu, status tercetak, upload sampul, tambah guru Syekh Muhammad Arsyad Al-Banjari (pengarang; guru sudah wafat, harus bisa dipilih), centang aktif + unggulan → simpan. Cek file di `storage/app/public/books/large` dan `thumb`.
2. **Validasi**: coba simpan tanpa guru → muncul error. Tambah guru yang sama dengan peran sama dua kali → muncul error.
3. **Publik daftar**: logout → `/kitab`. Kitab muncul dengan thumb, judul Arab RTL, dan nama pengarang. Cari "سبيل" → kitab ditemukan. Filter "Ulama yang telah wafat" → kitab muncul; filter "Masih hidup" → kitab tidak muncul (kecuali ada guru hidup terkait).
4. **Publik detail**: buka `/kitab/sabilal-muhtadin`. Cek bagian guru, label "rahimahullah" + tahun wafat, tautan ke `/guru/{slug}`, dan meta title/description/OG image (view-source).
5. **Tautan Pustaka**: edit kitab → tautkan ke entri pustaka aktif → tombol "Baca di Pustaka" muncul. Nonaktifkan pustaka itu → tombol hilang.
6. **Profil guru**: buka `/guru/{slug-syekh-arsyad}` → bagian "Karya" berisi kitab dengan label "Pengarang". Buka guru lain tanpa kitab → bagian tidak ada.
7. **Beranda**: `/` menampilkan widget kitab unggulan. Matikan unggulan → setelah simpan, widget hilang (cache ter-forget).
8. **Draf**: set kitab non-aktif → `/kitab/{slug}` 404 sebagai tamu, tetapi tampil saat login Super Admin. Kitab hilang dari `/kitab` dan `/sitemap.xml`.
9. **XSS**: isi deskripsi dengan `<script>alert(1)</script><p>teks</p>` → detail hanya menampilkan "teks", tanpa alert.
10. **Proteksi hapus guru**: lewat `php artisan tinker`, `Teacher::find(<id>)->delete()` untuk guru terkait kitab → error FK; data tetap utuh.
11. **Regresi picker**: buka form majelis/jadwal yang memakai `TeacherSelect` → guru wafat tetap tidak muncul seperti sebelumnya.
12. **Mobile**: cek `/kitab` dan detail di lebar 375px; judul Arab panjang tidak meluber.

---

## 17. Catatan Implementasi (Penyesuaian dari Rencana)

| Rencana | Implementasi | Alasan |
|---|---|---|
| `database/factories/BookFactory.php` | Tidak dibuat; test memakai helper `makeBook()` | Proyek hanya punya `UserFactory`; test lain membuat model lewat `::create()` (pola `PublicPageTestCase`). |
| Baris guru berulang via Alpine | Komponen baru `app/Livewire/Partials/BookTeacherRows.php` + view, yang merender `TeacherSelect` per baris | Komponen Livewire tidak bisa dibuat dari template Alpine di sisi klien; parent Livewire dengan `key` stabil per baris mempertahankan pilihan guru saat baris ditambah/dihapus. |
| — | Komponen Blade `resources/views/components/kitab/card.blade.php` | Kartu yang sama dipakai di `/kitab`, bagian "Karya" profil guru, dan widget beranda. |
| — | `Teacher::tahunWafat()` | Label "wafat 1812 M / 1227 H" terpusat di model, tidak diulang di view. |
| `Route::resource` admin penuh | `->except('show')` | Pratinjau admin memakai halaman publik `/kitab/{slug}` (draf terlihat oleh Super Admin). |
| Hapus dari tabel Livewire | Modal konfirmasi mengirim form `DELETE` ke `BookController::destroy` | Logika hapus (file sampul, cache widget) hanya ada di satu tempat. |
| `TeacherSelect` parameter baru | `includeWafat`/`onlyPublic` ditandai `#[Locked]` | Mencegah klien mengubah filter lewat payload Livewire. |
| — | Deskripsi juga di-`clean()` saat render | Mengikuti pola `guru/detail.blade.php` (pertahanan berlapis). |
