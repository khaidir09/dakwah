# Optimasi SEO Halaman Publik

**Status Dokumen:** Draft
**Tanggal:** 2026-08-05
**Author:** Muhammad Khaidir

---

## Latar Belakang

Syaikhuna sudah menyajikan seluruh halaman publik secara server-side (Blade, tanpa Livewire full-page route), sehingga secara teknis mudah dirayapi mesin telusur. Namun lapisan metadata-nya nyaris kosong: hanya ada satu blok `<head>` generik di `resources/views/layouts/user.blade.php`, dan sebagian besar halaman tidak mengisinya.

Akibatnya, tiga halaman yang paling bernilai secara bisnis — **detail guru**, **detail majelis**, dan **detail jadwal majelis** — semuanya terkirim ke Google dengan `<title>` yang sama persis: `Syaikhuna`. Situs juga belum punya `sitemap.xml`, `<link rel="canonical">`, maupun structured data, padahal domainnya kaya entitas yang sangat cocok untuk rich result (`Person` untuk ulama, `Event` untuk haul/acara).

Di sisi lain ditemukan tiga kebocoran visibilitas yang membuat konten **belum/tidak disetujui** dapat diakses publik. Selama kebocoran ini ada, menerbitkan `sitemap.xml` justru mempercepat terindeksnya konten yang belum dimoderasi — karena itu perbaikannya dijadikan prasyarat, bukan pekerjaan terpisah.

Dokumen ini juga menyelesaikan satu masalah struktural: `/guru/{slug}` dan `/manaqib/{slug}` saat ini merender **entitas `Teacher` yang sama** dengan blok teks `biografi` yang identik kata per kata — duplicate content tepat pada entitas yang jadi target utama akuisisi pencarian.

---

## Tujuan

- Setiap halaman publik memiliki `<title>`, `meta description`, `canonical`, dan Open Graph yang **unik dan deskriptif**.
- Situs dapat ditemukan lewat pencarian lokal ("jadwal pengajian Banjarmasin", "majelis di Martapura") dan pencarian nama ulama.
- Tautan yang dibagikan ke WhatsApp/Facebook menampilkan judul, deskripsi, dan gambar yang benar — bukan logo polos.
- Google memiliki peta lengkap konten yang **sah tayang** lewat `/sitemap.xml`.
- Entitas utama diekspresikan sebagai structured data agar berpeluang muncul sebagai rich result.
- Konten yang belum disetujui **tidak** dapat diakses publik dan **tidak** masuk indeks.

### Bukan Tujuan

Lihat bagian [Yang Tidak Termasuk Scope](#yang-tidak-termasuk-scope).

---

## Ruang Lingkup Keputusan (hasil wawancara)

| Keputusan | Pilihan yang disepakati |
|---|---|
| Tujuan bisnis | Akuisisi jamaah via pencarian + otoritas topik ulama Banjar + tampilan rapi saat di-share WA/FB |
| Domain kanonik | `https://syaikhuna.id` (non-www, HTTPS) |
| Struktur URL | Tambah slug pola `/{id}-{slug}`; **resolusi tetap by ID**; URL ID lama → **301** |
| Perilaku slug saat rename | Slug **ikut diperbarui**; URL lama otomatis 301 karena resolusi by ID |
| URL beranda | `/` menjadi kanonik; `/beranda` → **301** ke `/` |
| Sumber meta description | **Diturunkan otomatis** dari konten + fallback template per jenis halaman. Tanpa kolom baru |
| `og:image` | Gambar entitas → fallback **banner kategori** (aset disediakan pemilik produk) → fallback logo |
| Structured data | `Organization` + `WebSite` (global), `Person` (guru & manaqib), `Event` (acara) |
| `Article` schema | **Di luar scope** iterasi ini |
| Halaman detail acara | **Dibuat baru**: `/event/{id}-{slug}` — prasyarat `Event` schema |
| Duplikasi guru vs manaqib | Kolom `manaqib` baru (nullable) di `teachers`; selama kosong → canonical ke `/guru/{slug}` |
| Sitemap | **Route dinamis** `/sitemap.xml` + cache (bukan file statis / cron) |
| Isi sitemap | guru, majelis, jadwal-majelis, tulisan, manaqib, artikel ilmiah, pustaka gratis, catatan pengajian Public+Approved, plus halaman daftar `/wirid` & `/video` |
| Di luar sitemap | Acara & jadwal ramadhan (musiman) |
| Noindex | `kelola-*`/dasbor pribadi, URL berquery & pagination >1, `/admin/*` + debris template, `/kontributor/profil/{username}` |
| Kebocoran moderasi (R9) | **Masuk scope** — wajib diperbaiki sebelum sitemap tayang |
| Performa | Scope "murah": self-host/preload font Inter, `width`/`height` + `loading="lazy"`, pagination `/guru` & `/majelis` |
| Heading & alt | Seluruh halaman publik: satu `<h1>` berisi entitas sebenarnya, `alt` deskriptif |
| Testing | Feature test per halaman publik (pola `FaviconTest`) + test sitemap, 301, dan anti-kebocoran |

---

## Status Implementasi

Dikerjakan bertahap sesuai [RS13](#risiko-dan-trade-off).

| Tahap | Isi | Status |
|---|---|---|
| 1 | Perbaikan kebocoran moderasi (K1–K8) + test regresi | **Selesai** — 26 test di `tests/Feature/Visibility/` |
| 2 | `SeoService`, canonical otomatis, metadata seluruh halaman publik, `noindex` area privat | **Selesai** — 16 test unit + 27 test feature |
| 3 | Slug `/{id}-{slug}` + redirect 301 + `/` sebagai kanonik | **Selesai** — 20 test di `tests/Feature/Seo/CanonicalUrlTest.php`; **butuh migration** |
| 4 | `/sitemap.xml` dinamis + `robots.txt` | **Selesai** — 13 test di `tests/Feature/Seo/SitemapTest.php` + 3 di `RobotsTxtTest.php`; tanpa migration |
| 5 | Halaman detail acara + structured data JSON-LD | **Selesai** — 15 test di `tests/Feature/Seo/EventDetailTest.php` + 11 di `StructuredDataTest.php`; **butuh migration** |
| 6 | Performa (font lokal, lazy image, pagination) + heading & `alt` | **Selesai** — 11 test di `tests/Feature/Seo/PageStructureTest.php`; tanpa migration |

Tahap 1–2 tidak memerlukan migration dan tidak mengubah satu pun URL, sehingga aman dirilis lebih dulu. Tahap 3 adalah rilis pertama yang membawa migration dan mengubah URL — jalankan checklist [Verifikasi End-to-End](#verifikasi-end-to-end) sebelum melepasnya.

---

## Perilaku Saat Ini

### Lapisan metadata

- **Satu-satunya `<head>` publik** ada di `resources/views/layouts/user.blade.php:3-58`, dirender lewat komponen `App\View\Components\UserLayout` (`app/View/Components/UserLayout.php:13-16`).
- Layout memakai `@yield('title')`, `@yield('meta_description')`, `@yield('meta_keywords')`, `@yield('meta_image')`, `@yield('og_type')` (baris `8-25`).
- Halaman mengisinya dengan `@section('title', ...)` sebagai baris kedua di dalam `<x-user-layout>`. Mekanisme ini **berfungsi** karena slot komponen dievaluasi sebelum view komponen dirender, dan tidak ada route Livewire full-page di proyek ini (`grep "Livewire::" routes/` → kosong).
- **`@section('title')` terpasang di 23 view**, tetapi **tidak** di tiga halaman detail terpenting:
  - `resources/views/pages/user/guru/detail.blade.php:1`
  - `resources/views/pages/user/majelis/detail.blade.php:1`
  - `resources/views/pages/user/jadwal-majelis/detail.blade.php:1`
- **`meta_description` hanya diisi di satu view**: `resources/views/pages/user/catatan-pengajian/detail.blade.php:3`.
- **`meta_image` hanya diisi di satu view**: `resources/views/pages/user/article/detail.blade.php:4`.
- **Tidak ada `<link rel="canonical">`** di seluruh `resources/` (grep `canonical` → 0 hasil).
- **Tidak ada structured data** (grep `ld+json` / `schema.org` → 0 hasil).
- `og:url` dan `twitter:url` selalu `url()->current()` (`layouts/user.blade.php:15,22`), sehingga ikut membawa query string.
- Layout lain — `layouts/dashboard.blade.php:8`, `layouts/app.blade.php:8`, `layouts/guest.blade.php:8` — meng-hardcode `<title>Syaikhuna</title>` tanpa meta description.

### Sitemap & robots

- **Tidak ada** `public/sitemap.xml` maupun route yang menghasilkannya.
- `public/robots.txt` hanya berisi dua baris (`User-agent: *` / `Disallow:`) — mengizinkan semua, **tanpa direktif `Sitemap:`**.

### Struktur URL publik

Didefinisikan di `routes/web.php:57-125`.

| Route | Baris | Kunci | Model |
|---|---|---|---|
| `/` → `/beranda` (**302**, default `Route::redirect`) | `57` | — | — |
| `/beranda` | `59` | — | — |
| `/majelis` , `/majelis/{id}` | `60`, `63` | **ID** | `Assembly` |
| `/jadwal-majelis` , `/jadwal-majelis/{id}` | `61`, `62` | **ID** | `Schedule` |
| `/guru` , `/guru/{teacher}` | `64`, `65` | slug | `Teacher` (`app/Models/Teacher.php:36-39`) |
| `/video` , `/event` , `/wirid` | `66`, `67`, `68` | — | list saja, tanpa route detail |
| `/manaqib` , `/manaqib/{slug}` | `69`, `70` | slug | **`Teacher`** (sama dengan `/guru`) |
| `/pustaka` , `/pustaka/{library}` | `71`, `72` | slug | `Library` (`app/Models/Library.php:34-37`) |
| `/tulisan` , `/tulisan/{slug}` | `73`, `74` | slug | `Post` |
| `/artikel/{slug}` | `78` | slug | `ScientificArticle` |
| `/jadwal-ramadhan` , `/jadwal-ramadhan/{id}` | `83`, `84` | **ID** | `RamadhanSchedule` |
| `/catatan-pengajian` , `/catatan-pengajian/{id}` | `124`, `125` | **ID** | `ScheduleNote` |
| `/kontributor` , `/kontributor/profil/{username}` | `112`, `113` | username | `User` |
| `/tentang-kami` | `115` | — | — |

### Duplikasi guru vs manaqib

`User\BiographyController::detail()` (`app/Http/Controllers/User/BiographyController.php:16-24`) menjalankan `Teacher::with('contributor')->where('slug', $slug)->firstOrFail()` — **model yang sama persis** dengan route-model binding di `User\GuruController::detail()` (`app/Http/Controllers/User/GuruController.php:19`).

Field yang dirender kedua view:

| Field | `/guru/{slug}` | `/manaqib/{slug}` |
|---|---|---|
| `biografi` | `guru/detail.blade.php:129` | `biography/detail.blade.php:105` |
| `foto_bersama` + caption + atribusi | `:109-118` | `:88-97` |
| `source` | `:131-135` | `:108-112` |
| Domisili (`village`/`district`/`province`), `tahun_lahir` | `:70`, `:194` | — |
| Jadwal rutinan (`schedules`) | ada | — |
| `wafat_masehi`, `wafat_hijriah_*` | — | `:50-77` |
| Lokasi makam (`maps`) | — | `:128-133` |

### Kebocoran visibilitas moderasi

Pola visibilitas yang **benar** sudah ada dan dipakai sebagian:

- `Teacher::scopePubliclyVisible()` dan `Teacher::isVisibleTo(?User)` — `app/Models/Teacher.php:107-130`
- `Assembly::scopePubliclyVisible()` — `app/Models/Assembly.php:113-119`
- `Schedule::scopePubliclyVisible()` — `app/Models/Schedule.php:133-139`
- `ScheduleNote::scopePubliclyVisible()` — `app/Models/ScheduleNote.php:31-36`
- Dipakai dengan benar di: `GuruController.php:21`, `BiographyController.php:20`, `CatatanPengajianController.php:17-20` (`visibility='Public'` **dan** `status='Approved'`).

**Koreksi terhadap draf awal dokumen ini.** Draf pertama menuding `GuruController::list()` (`Teacher::all()`) dan `MajelisController::list()` sebagai kebocoran. Itu **keliru**: kedua view mendelegasikan daftarnya ke `<livewire:list-guru />` dan `<livewire:list-majelis />`, yang sudah memfilter lewat `publiclyVisible()` (`app/Livewire/ListGuru.php:68`, `app/Livewire/ListMajelis.php:74`). Variabel `$teachers`/`$assemblies` dari controller tidak pernah dirender — query mati yang memuat seluruh tabel pada tiap request.

Kebocoran yang benar-benar ada — **tujuh titik**, seluruhnya sudah diperbaiki (lihat [Status Implementasi](#status-implementasi)):

| # | Lokasi | Masalah |
|---|---|---|
| K1 | `app/Http/Controllers/User/MajelisController.php:21` | `detail()` tanpa cek visibilitas — detail majelis `pending`/`rejected` dapat dibuka siapa pun |
| K2 | `app/Http/Controllers/User/MajelisController.php:22` | `$schedules` tanpa `publiclyVisible()` — jadwal belum dimoderasi tampil di detail majelis |
| K3 | `app/Http/Controllers/User/MajelisController.php:23` | `$upcomingEvents` tanpa filter — acara belum dimoderasi tampil di detail majelis |
| K4 | `app/Http/Controllers/User/GuruController.php:35` | `$schedules` tanpa `publiclyVisible()` — jadwal belum dimoderasi tampil di detail guru |
| K5 | `app/Http/Controllers/User/JadwalMajelisController.php:33` | `detail()` tanpa cek visibilitas — detail jadwal `pending`/`rejected` terbuka untuk publik |
| K6 | `app/Livewire/ListJadwalMajelis.php:131,134` | Query mingguan tanpa `publiclyVisible()` (query berkala di baris 154 sudah benar) — jadwal belum dimoderasi tampil di `/jadwal-majelis` |
| K7 | `app/Livewire/ListEvent.php:48` **dan** `app/Livewire/HomeEvent.php:17` | Filter `whereNotNull('moderated_at')`, padahal `ModerasiController::revokeEvent()` **juga** menyetel `moderated_at` saat menolak (`app/Http/Controllers/Admin/ModerasiController.php:111-113`) → **acara yang ditolak tetap tampil publik**, di `/event` maupun di beranda |
| K8 | `app/Livewire/HomeUpcomingHaul.php:25,34` | `Teacher::where(...)` tanpa `publiclyVisible()` — guru `pending`/`rejected` muncul di widget haul beranda |

#### Jebakan pada `events.status`

`events.status` adalah enum `['pending','approved','rejected']` dengan **default `'pending'`** (`database/migrations/2026_06_01_224943_add_status_to_events_table.php`). Baik `EventController::store()` (`:62-64`) maupun `ManageEventController::store()` (`:64-66`) hanya mengisi `moderated_at` untuk Super Admin dan **tidak pernah menyetel `status`**.

Akibatnya acara buatan admin permanen bernilai `status = 'pending'`. Memperbaiki K7 dengan `where('status','approved')` — sebagaimana ditulis draf pertama dokumen ini — akan **menghapus seluruh acara buatan admin** dari beranda dan `/event`. Inilah alasan `Event::scopePubliclyVisible()` versi lama (`status IS NULL OR status = 'approved'`) tidak dipakai komponen mana pun.

### Performa & semantik

- Google Fonts dimuat render-blocking dari CDN eksternal — `layouts/user.blade.php:34-36`.
- Daftar publik **tanpa pagination**: `Teacher::all()` (`GuruController.php:14`), `Assembly::with('teacher')->withCount('schedule')->get()` (`MajelisController.php:14`), `Video::all()` (`VideoController.php:11`).
- Dua `<h1>` pada halaman guru: `"Detail Guru"` (`guru/detail.blade.php:22`) dan nama guru (`:67`). Pola sama di `biography/detail.blade.php:22` vs `:40`.
- `alt="Avatar"` generik pada foto guru — `guru/detail.blade.php:46`.
- `app/Livewire/ListEvent.php:17-20` mengekspos `#[Url] $search` dan `#[Url] $category` → URL berquery yang dapat terindeks.

### Konfigurasi terkait

- `config('app.url')` ← `APP_URL` (`config/app.php:58`). `.env.example:5` masih `http://localhost`.
- Google Analytics sudah terpasang bersyarat: `resources/views/components/google-analytics.blade.php:1-11` membaca `config('services.google_analytics.id')` (`config/services.php:34-36`). Kunci `GOOGLE_ANALYTICS_ID` **belum tercantum** di `.env.example`.

---

## Perilaku yang Diharapkan

### 1. Sumber metadata terpusat — `SeoService`

Dibuat `app/Services/SeoService.php` sebagai satu-satunya tempat menurunkan metadata. Ini mengikuti aturan arsitektur proyek (jangan buat utility duplikat) dan sejajar dengan `HijriService` / `ScheduleOccurrenceService` yang sudah ada.

Tanggung jawab:

- `description(?string $raw, string $fallback): string` — `Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($raw))), 155)`; jika hasilnya kosong, pakai `$fallback`.
- `canonical(?string $path = null): string` — selalu absolut dari `config('app.url')`, **tanpa query string**, tanpa trailing slash (kecuali root).
- `image(?string $storagePath, string $category): string` — URL absolut. Urutan: gambar entitas → banner kategori (`public/images/og/{category}.png`) → `images/android-chrome-512x512.png`.
- `title(string $page): string` — pola `"{judul halaman} — Syaikhuna"`, dipotong 60 karakter agar tidak terpangkas di SERP.

Aturan penting: **tidak ada kolom database baru untuk metadata**. Semua diturunkan dari konten yang sudah ada.

### 2. Distribusi `SeoService` ke view

**Keputusan implementasi (menggantikan rencana komponen `<x-seo>`).** Rencana awal memakai anonymous component `<x-seo>` yang mem-`@push` ke stack `head`. Itu ditinggalkan karena akan menghasilkan **dua mekanisme metadata yang bersaing**: 23 view sudah memakai `@section('title')`, dan halaman yang lupa memasang `<x-seo>` justru akan kehilangan seluruh meta-nya.

Yang dipakai:

- `AppServiceProvider::register()` mengikat `SeoService` sebagai singleton; `boot()` membagikannya ke seluruh view sebagai `$seo` lewat `View::share`. Halaman cukup memanggil `$seo->...` di dalam `@section` tanpa import apa pun.
- `@yield`/`@section` tetap menjadi satu-satunya mekanisme metadata, sehingga 23 view yang sudah mengisinya terus bekerja.
- `layouts/user.blade.php` menghitung **canonical secara otomatis** dari `$seo->canonical()`, jadi halaman tidak perlu mengisinya kecuali ingin menimpa (`@section('canonical', ...)`).
- `@stack('head')` tetap ditambahkan ke `<head>` sebagai tempat JSON-LD pada iterasi berikutnya.

Mekanisme ini bekerja karena slot komponen Blade dievaluasi sebelum view komponen dirender, dan tidak ada route Livewire full-page di proyek ini.

Selain itu:

- `<meta name="keywords">` (`layouts/user.blade.php:10`) **dihapus** — diabaikan seluruh mesin telusur besar.
- `<link rel="canonical">` ditambahkan ke `<head>`, default `SeoService::canonical()`.
- `og:url` dan `twitter:url` diubah dari `url()->current()` menjadi nilai canonical yang sama.
- `layouts/{dashboard,app,guest}.blade.php:8` diberi `@yield('title', 'Syaikhuna')` dan `<meta name="robots" content="noindex, nofollow">`.

### 3. Metadata per halaman publik

| Halaman | `<title>` | `meta description` (sumber) | `og:image` |
|---|---|---|---|
| `/` | `Syaikhuna — Jadwal Majelis & Pengajian Kalimantan` | statis | banner `beranda` |
| `/guru` | `Daftar Guru & Ulama — Syaikhuna` | statis + jumlah guru | banner `guru` |
| `/guru/{slug}` | `{nama} — Syaikhuna` | `biografi` → fallback: `"{nama}, ulama di {domisili}. {n} jadwal pengajian rutin."` | `foto` |
| `/manaqib/{slug}` | `Manaqib {nama} — Syaikhuna` | `manaqib` → fallback `biografi` | `foto` |
| `/majelis` | `Daftar Majelis Ilmu — Syaikhuna` | statis | banner `majelis` |
| `/majelis/{id}-{slug}` | `{nama_majelis} — Syaikhuna` | `deskripsi` → fallback: `"Majelis {nama} di {alamat}, dipimpin {leader_name}. {n} jadwal rutin."` | `gambar` (varian `large`) |
| `/jadwal-majelis` | `Jadwal Majelis — Syaikhuna` | statis | banner `jadwal` |
| `/jadwal-majelis/{id}-{slug}` | `{nama_jadwal} — Syaikhuna` | `deskripsi` → fallback: `"{nama_jadwal} setiap {hari} pukul {waktu} di {majelis}."` | gambar majelis |
| `/event` | `Acara & Haul — Syaikhuna` | statis | banner `acara` |
| `/event/{id}-{slug}` **(baru)** | `{name} — Syaikhuna` | `"{name}, {tanggal} di {location}."` | `image` acara |
| `/tulisan/{slug}` | `{title} — Syaikhuna` | `content` | `cover_image` |
| `/artikel/{slug}` | `{title} — Syaikhuna` | `abstract`/`content` | `cover_image` |
| `/pustaka/{slug}` | `{title} — Syaikhuna` | `description` | cover pustaka |
| `/catatan-pengajian/{id}` | sudah ada, dirapikan | sudah ada (`:3`) | banner `catatan` |
| `/wirid`, `/video`, `/tentang-kami`, `/kontributor` | judul deskriptif | statis | banner kategori |

`SeoService::description()` menjamin hasil akhir tidak pernah kosong dan tidak pernah berisi tag HTML.

### 4. Slug `/{id}-{slug}`

Berlaku untuk `Assembly` dan `Schedule`. `Event` menyusul pada tahap 5 bersama halaman detailnya, agar kolomnya tidak menganggur.

- Kolom baru `slug` (`string`, nullable) pada `assemblies` dan `schedules`.
- **Tanpa unique index, dan tanpa index sama sekali.** Draf pertama dokumen ini menulis `unique`, yang bertentangan dengan [E5](#edge-cases): resolusi URL lewat ID membuat dua entitas bernama sama boleh berbagi slug. Tidak ada query yang mencari berdasarkan kolom ini, jadi index apa pun hanya menambah beban tulis.
- Slug dijaga oleh trait `App\Models\Concerns\HasRouteSlug` lewat event `saving`, **bukan** di controller. Alasannya: kedua entitas dibuat dan diubah dari lima jalur berbeda (admin, pemilik majelis, kontributor, onboarding, seeder); menaruhnya di model membuat satu pun jalur tidak bisa terlewat.
- **Resolusi tetap by ID.** Parameter route di-parse dengan `(int) $param` — PHP memotong di karakter non-digit pertama, sehingga `42-majelis-ar-raudhah` → `42`. Ini yang membuat pola ini nol-risiko: slug murni kosmetik.
- Slug dihasilkan dengan mengikuti pola yang sudah terbukti di `Teacher::generateSlug($name, $ignoreId)` (`app/Models/Teacher.php:21-34`). Karena unik-per-ID sudah dijamin ID, uniqueness slug di sini bersifat kenyamanan, bukan kebenaran.
- **Slug diperbarui saat nama entitas berubah** (di `update()` masing-masing controller pengelola).
- **301 kanonikalisasi**: jika segmen URL tidak sama persis dengan `"{id}-{slug}"` terkini, controller me-`redirect()->route(..., $canonicalParam, 301)`. Ini otomatis menangani URL ID lama (`/majelis/42`) **dan** slug lama pasca-rename.
- Helper `getSlugParamAttribute()` pada tiap model mengembalikan `"{$this->id}-{$this->slug}"`, dipakai semua `route()` internal.

Entitas **tidak** mendapat slug (tetap ID): `ScheduleNote` (`/catatan-pengajian/{id}`), `RamadhanSchedule` (`/jadwal-ramadhan/{id}`) — keduanya bukan target landing page pencarian.

### 5. URL beranda

`routes/web.php:57` diubah:

```php
Route::get('/', [HomeController::class, 'index'])->name('beranda');
Route::redirect('/beranda', '/', 301);
```

Nama route `beranda` **dipertahankan** agar seluruh `route('beranda')` di view dan redirect (`routes/web.php:94`) tetap bekerja tanpa disentuh.

### 6. Pemisahan konten `/guru` dan `/manaqib`

- Migration menambahkan kolom `manaqib` (`text`, nullable) pada tabel `teachers`.
- `/guru/{slug}` menampilkan `biografi` (profil ringkas, domisili, tahun lahir, jadwal rutinan).
- `/manaqib/{slug}` menampilkan `manaqib` (riwayat hidup mendalam, sanad, wafat, lokasi makam).
- **Aturan canonical bersyarat** — selama `manaqib` masih kosong, halaman `/manaqib/{slug}` tetap merender `biografi` seperti sekarang **tetapi** memasang `<link rel="canonical">` ke `/guru/{slug}` dan **tidak** masuk sitemap. Begitu `manaqib` terisi, canonical menunjuk dirinya sendiri dan URL-nya masuk sitemap.
- Form admin guru (`resources/views/pages/guru/edit.blade.php`, `create.blade.php`) dan form kontribusi guru mendapat field `manaqib` dengan editor yang sama seperti `biografi`. Disimpan lewat `clean()` sesuai aturan keamanan proyek.

Pendekatan bersyarat ini penting: tanpa itu, menerbitkan dua URL berbeda sebelum kontennya benar-benar berbeda justru memperburuk duplikasi.

### 7. Halaman detail acara `/event/{id}-{slug}` (baru)

Prasyarat untuk `Event` structured data — saat ini `Event` tidak punya halaman detail publik sama sekali (`routes/web.php:67` hanya list, dan `User\EventController::list()` bahkan tidak memuat data).

- Route: `Route::get('/event/{param}', [UserEventController::class, 'detail'])->name('event-detail');`
- `User\EventController::detail()` memuat acara dengan filter ketat: `status === 'approved'` **dan** `access === 'Umum'`. Selain itu → `404`.
- View baru `resources/views/pages/user/events/detail.blade.php` menampilkan nama, tanggal, lokasi, kategori, gambar, peta, majelis penyelenggara, dan atribusi kontributor.
- Kartu di `resources/views/livewire/list-event.blade.php` menjadi tautan ke halaman ini.

Acara **tidak** masuk sitemap (keputusan wawancara: konten musiman), tetapi tetap dapat ditemukan lewat tautan internal dari `/event` dan detail majelis.

#### Catatan implementasi

- **`status === 'approved'` tidak dipakai** — rumusan itu adalah jebakan yang sama dengan K7: acara buatan Super Admin permanen bernilai `status = 'pending'`, sehingga filter tersebut akan menolak seluruh acara buatan admin. Syarat detail publik yang benar: `Event::publiclyVisible()` **dan** `access === 'Umum'`, dirangkum di `Event::isPubliclyVisible()`. Dikunci oleh test `acara_buatan_super_admin_tetap_dapat_dibuka`.
- **Pemilik dan Super Admin tetap dapat membuka pratinjau** acara yang belum tayang, lewat `Event::isVisibleTo(?User)` — persis pola `Teacher`, `Assembly`, dan `Schedule`. Draf awal dokumen ini menulis "selain itu → 404" tanpa pengecualian; itu akan membuat moderator tidak dapat melihat acara yang sedang ia nilai. Halaman pratinjau menampilkan pita penanda "belum tayang untuk umum".
- **Slug** memakai trait `HasRouteSlug` yang sama dengan majelis dan jadwal, ditambah dua migration kembaran (`2026_08_06_000001` skema, `2026_08_06_000002` backfill).
- **Kartu acara "Khusus" tidak ditautkan** di `/event`, beranda, maupun detail majelis — halaman detailnya 404, dan menautkan ke 404 memboroskan crawl budget sekaligus membingungkan pembaca.

### 8. Perbaikan kebocoran moderasi (prasyarat sitemap)

| # | Perbaikan |
|---|---|
| K1 | `MajelisController::detail()` — `abort_unless($assembly->isVisibleTo(Auth::user()), 404)`. Method `Assembly::isVisibleTo(?User)` meniru persis `Teacher::isVisibleTo()` (`app/Models/Teacher.php:119-130`), memakai `user_id` sebagai pemilik |
| K2–K4 | Tambah `->publiclyVisible()` pada query `$schedules` di `MajelisController::detail()` dan `GuruController::detail()`, serta `$upcomingEvents` di `MajelisController::detail()` |
| K5 | `JadwalMajelisController::detail()` — `abort_unless($schedule->isVisibleTo(Auth::user()), 404)`. Method `Schedule::isVisibleTo(?User)` ditambahkan dengan pola yang sama, memakai `contributor_user_id` |
| K6 | `ListJadwalMajelis::render()` — tambah `->publiclyVisible()` pada query mingguan dan pada `$schedules_count` |
| K7 | `Event::scopePubliclyVisible()` ditulis ulang menjadi `moderated_at IS NOT NULL AND status <> 'rejected'`, lalu dipakai `ListEvent` dan `HomeEvent`. Rumusan ini benar untuk kedua bentuk data (acara buatan admin maupun hasil moderasi) sehingga **tidak memerlukan migration** |
| K8 | `HomeUpcomingHaul::render()` — tambah `Teacher::publiclyVisible()` pada kedua query haul |

Query mati `Teacher::all()` (`GuruController::list()`), `Assembly::…->get()` (`MajelisController::list()`), dan `Schedule::…->get()` (`JadwalMajelisController::list()`) dihapus: hasilnya tidak pernah dirender, tetapi memuat seluruh tabel pada setiap request.

**Tidak** ditambahkan filter `access = 'Umum'` pada daftar acara. Menyembunyikan acara "Khusus" adalah keputusan produk, bukan perbaikan kebocoran moderasi, dan akan menghilangkan konten yang selama ini sengaja tayang. Filter itu hanya berlaku pada halaman detail acara baru (bagian 7).

Dampak di luar kanal publik: `Event::publiclyVisible()` juga dipakai `DashboardStatsService.php:115`, sehingga kartu "Acara" di dasbor admin kini ikut menghitung acara buatan admin — sebelumnya acara tersebut tidak terhitung sama sekali karena `status`-nya `'pending'`.

### 9. `/sitemap.xml` dinamis

- Route: `Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');` di luar semua middleware auth.
- Response `Content-Type: application/xml`, di-`Cache::remember(...)` selama **6 jam** (konten Syaikhuna berubah harian, bukan per menit).
- Semua URL **absolut** dari `config('app.url')`, dan **wajib** sama persis dengan nilai `canonical` halaman bersangkutan.

Isi:

| Bagian | Sumber | Filter | `changefreq` |
|---|---|---|---|
| Halaman statis | `/`, `/guru`, `/majelis`, `/jadwal-majelis`, `/tulisan`, `/manaqib`, `/pustaka`, `/wirid`, `/video`, `/catatan-pengajian`, `/tentang-kami`, `/kontributor` | — | `weekly` |
| Guru | `Teacher::publiclyVisible()` | — | `monthly` |
| Manaqib | `Teacher::publiclyVisible()` | **`whereNotNull('manaqib')`** — *belum diimplementasi, lihat catatan* | `monthly` |
| Majelis | `Assembly::publiclyVisible()` | — | `weekly` |
| Jadwal majelis | `Schedule::publiclyVisible()` | — | `weekly` |
| Tulisan | `Post::published()` | — | `monthly` |
| Artikel ilmiah | `ScientificArticle::where('status','PUBLISHED')` | — | `monthly` |
| Pustaka | `Library` | **`price_type != 'paid'` AND `is_active = true`** | `monthly` |
| Catatan pengajian | `ScheduleNote` | **`visibility='Public'` AND `status='Approved'`** + `publiclyVisible()` | `monthly` |

`lastmod` diambil dari `updated_at` dan diformat W3C (`toAtomString()`). Halaman daftar statis sengaja **tanpa** `lastmod` — nilainya tidak diketahui, dan mengarangnya lebih buruk daripada mengosongkannya.

#### Catatan implementasi (`app/Http/Controllers/SitemapController.php`)

- **URL tidak dirakit manual.** Setiap `<loc>` dibangun dari `SeoService::canonical(route($nama, $param, false))`, sehingga sitemap dan `<link rel="canonical">` halaman bersangkutan tidak mungkin menyimpang. Test `setiap_url_di_sitemap_mengembalikan_200` menelusuri seluruh isi sitemap dan menuntut `200` — bukan `301`, bukan `404`.
- **Manaqib ditunda.** Kolom `manaqib` belum ada (bagian 6 belum dikerjakan), jadi `/manaqib/{slug}` masih merender `biografi` yang identik dengan `/guru/{slug}`. Mendaftarkannya sekarang berarti mendaftarkan duplikat, karena itu detail manaqib **sama sekali tidak** masuk sitemap; halaman daftar `/manaqib` tetap masuk.
- **`is_active` ditambahkan pada pustaka** (di luar tulisan asli dokumen ini). `ListLibrary` menyembunyikan pustaka nonaktif dari `/pustaka`, jadi mendaftarkannya di sitemap akan mengiklankan konten yang situsnya sendiri anggap belum terbit.
- **`price_type` NULL diperlakukan gratis**, meniru `Library::isFree()`. Kolomnya `NOT NULL DEFAULT 'free'` hari ini, tetapi `where('price_type','!=','paid')` sendirian akan diam-diam membuang baris ber-NULL bila skema pernah diubah manual.
- **Ambang 10.000 tidak memecah sitemap.** Batas protokolnya sendiri 50.000 URL, jadi memotong isi di 10.000 justru menghilangkan konten. Yang dilakukan: `Log::warning` saat ambang terlewati, sebagai penanda bahwa sitemap index sudah waktunya dibuat.
- **Cache**: `Cache::remember('seo:sitemap', 6 jam)` atas string XML-nya. Konsekuensi E19 (konten baru terlambat terdaftar) dikunci oleh test `sitemap_disimpan_di_cache`.

### 10. `robots.txt`

`public/robots.txt` diperbarui:

```
User-agent: *
Allow: /

Disallow: /admin/
Disallow: /kelola-
Disallow: /kontributor/saya
Disallow: /favorit-saya
Disallow: /pustaka-saya
Disallow: /pengaturan-akun
Disallow: /registrasi-majelis

Sitemap: https://syaikhuna.id/sitemap.xml
```

`robots.txt` adalah sabuk pengaman, bukan mekanisme keamanan — kontrol sebenarnya tetap pada middleware `auth`/`is_admin` dan perbaikan K1–K3.

**Empat direktif dari draf pertama dokumen ini dihapus**, karena masing-masing memblokir sinyal yang justru kita kirim di HTML:

| Dihapus | Alasan |
|---|---|
| `Disallow: /kontributor/profil/` | Halaman itu sudah memasang `<meta name="robots" content="noindex, follow">` (tahap 2). Memblokir perayapannya membuat mesin telusur **tidak pernah membaca tag itu**, sehingga URL-nya tetap berpeluang muncul di hasil pencarian tanpa cuplikan — persis kebalikan dari yang diinginkan. |
| `Disallow: /*?search=` | Konsolidasi query string ditangani `<link rel="canonical">`. Canonical hanya dihormati bila halamannya dirayapi. |
| `Disallow: /*?category=` | Idem. |
| `Disallow: /*?page=` | Idem; ditambah bahwa pagination adalah jalur penemuan konten lama. Risiko crawl budget-nya kecil karena sitemap sudah mendaftarkan seluruh item secara langsung. |

Aturan yang bertahan hanyalah yang menutup area **berpagar `auth`/`is_admin`** — di sana perayap memang tidak akan pernah melihat HTML apa pun untuk dibaca, sehingga `robots.txt` adalah satu-satunya sinyal yang tersedia. `Disallow: /login` dan `/register` sengaja tidak ditambahkan dengan alasan yang sama dengan profil kontributor: keduanya sudah ber-`noindex, nofollow` lewat `layouts/authentication.blade.php`.

### 11. Meta robots & canonical untuk query string

- Middleware `AddNoindexHeader` dipasang pada grup route `auth:sanctum` dan `is_admin`, menambahkan header `X-Robots-Tag: noindex, nofollow`. Menggunakan header (bukan hanya meta tag) agar juga berlaku untuk response non-HTML.
- Halaman daftar dengan query string (`?search=`, `?category=`, `?label=`, `?page=`) memasang `<link rel="canonical">` ke **URL bersih tanpa query**, dan `<meta name="robots" content="noindex, follow">` untuk `?page=` > 1.
- `/kontributor/profil/{username}` memasang `noindex, follow` (konten berpotensi tipis).

#### Catatan implementasi

**Selesai** — 7 test di `tests/Feature/Seo/NoindexTest.php`; tanpa migration. Canonical tanpa query sudah selesai di tahap 2; `noindex` untuk `?page=` > 1 sengaja tidak dipasang (alasannya di bagian 13); `/kontributor/profil/{username}` sudah `noindex, follow` sejak tahap 2.

- **Alias `noindex`** didaftarkan di `app/Http/Kernel.php` (proyek ini masih memakai struktur kernel gaya Laravel 10, bukan `bootstrap/app.php` gaya 11), sejajar dengan `etag` dan `api.version` yang sudah ada. Middleware-nya tanpa parameter: nilai `X-Robots-Tag` mengandung koma, sedangkan koma adalah pemisah parameter middleware Laravel — memparameterkannya justru mengundang bug.
- **Dipasang pada tiga grup**: `['auth']` (verifikasi email, pengaturan akun), `['auth:sanctum','verified']`, dan grup `admin`. Ditambah dua route unduhan (`tulisan.download`, `artikel.download`) yang memasang `auth` sendiri-sendiri. Total **255 route**; audit `route:list` memastikan tidak satu pun route publik ikut terkena.
- **Ditempatkan sebelum `auth` di array**, tetapi Laravel tetap menjalankan `auth` lebih dulu karena `Authenticate` terdaftar di `$middlewarePriority` bawaan framework. Akibatnya **response pengalihan ke login tidak membawa header ini**. Dibiarkan begitu: menimpa `$middlewarePriority` berarti menyalin belasan baris konfigurasi framework yang akan usang diam-diam, sementara manfaatnya nol — perayap anonim tidak pernah menerima isi apa pun, dan halaman login sendiri sudah `noindex` lewat layout-nya. Test `pengalihan_ke_login_tidak_membawa_isi_yang_perlu_ditandai` mengunci alasan ini agar tidak berulang kali "diperbaiki".
- **Nilai sesungguhnya ada di tiga hal**, dan sebaiknya tidak dilebih-lebihkan: (1) response non-HTML untuk sesi yang sudah login — `pustaka/{library}/dokumen` menyajikan PDF, `tulisan/{slug}/download` menyajikan berkas, dan keduanya tidak punya tempat untuk meta tag; (2) pertahanan berlapis bila suatu saat `auth` hilang dari sebuah route — repositori ini punya riwayat kebocoran semacam itu (K1–K8); (3) `robots.txt` hanya menutup tujuh awalan, sedangkan header ini menutup seluruh 255 route privat tanpa perlu didaftarkan satu per satu.

### 12. Structured data (JSON-LD)

Dibuat `app/Services/StructuredDataService.php`. Semua output di-`json_encode` dengan `JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE` dan **di-escape** sebelum masuk `<script type="application/ld+json">`.

| Schema | Halaman | Properti utama |
|---|---|---|
| `Organization` | semua halaman publik | `name`, `url`, `logo`, `sameAs` (media sosial), `areaServed` (Kalsel, Kalteng, Kaltim) |
| `WebSite` | beranda | `name`, `url`, `inLanguage: id-ID` |
| `Person` | `/guru/{...}`, `/manaqib/{...}` | `name`, `description`, `image`, `jobTitle: "Ulama"`, `homeLocation`, `deathDate` (dari `wafat_masehi`, hanya jika ada) |
| `Event` | `/event/{...}` | `name`, `startDate` (ISO-8601), `location` (`Place` + `address`), `image`, `organizer` (majelis), `eventStatus`, `eventAttendanceMode: OfflineEventAttendanceMode` |

`WebSite.SearchAction` **tidak** disertakan karena situs belum punya endpoint pencarian global — mengklaimnya tanpa implementasi akan ditolak validator Google.

#### Catatan implementasi

- Dibagikan ke seluruh view sebagai `$schema` lewat `AppServiceProvider`, mengikuti pola `$seo`. `Organization` dicetak langsung di `layouts/user.blade.php`; halaman menambahkan skemanya sendiri lewat `@push('jsonld')`.
- **`sameAs` dihilangkan.** Proyek ini tidak menyimpan URL media sosial resmi di mana pun — tidak di config, tidak di database. Mengarangnya justru merusak kepercayaan entitas di mata mesin telusur. Begitu tautan resminya tersedia, tambahkan sebagai konstanta di `StructuredDataService`, sejajar dengan `AREA_SERVED`.
- **`Person` belum dipasang di `/manaqib/{slug}`.** Selama kolom `manaqib` belum ada (bagian 6), kedua halaman masih merender biografi yang sama dan sama-sama mengaku kanonik atas dirinya sendiri. Menerbitkan dua entitas `Person` untuk satu ulama di dua URL justru memperparah masalah duplikasi yang dokumen ini hendak selesaikan. Pasang bersamaan dengan canonical bersyarat di bagian 6.
- **Keamanan output**: `json_encode` memakai `JSON_HEX_TAG` (plus `HEX_AMP`/`HEX_APOS`/`HEX_QUOT`), sehingga tag penutup `script` yang menyelinap lewat nama entitas tidak dapat memutus blok. Dikunci test `nama_yang_mengandung_tag_script_tidak_memecah_blok_json_ld`.
- **Properti tanpa data dihilangkan**, bukan dikirim bernilai `null` — berlaku untuk `deathDate` (E15), `image`, `description`, `homeLocation`, dan `organizer`.
- **`startDate` membawa offset zona waktu** (`Asia/Makassar`, dari `config('app.timezone')`). Tanpa offset, Google menafsirkan waktunya sebagai waktu lokal perayap.

### 13. Performa (scope "murah")

- **Font**: unduh Inter (subset `latin`, weight 400–700) ke `public/fonts/`, ganti `<link>` Google Fonts (`layouts/user.blade.php:34-36`) dengan `@font-face` lokal + `font-display: swap` + `<link rel="preload">`. Menghapus satu round-trip render-blocking ke domain pihak ketiga.
- **Gambar**: tambahkan `width`, `height`, dan `loading="lazy"` pada seluruh `<img>` di halaman daftar publik (gambar utama/LCP tetap `eager`). Mencegah layout shift (CLS).
- **Pagination**: `/guru` → `paginate(24)`, `/majelis` → `paginate(24)`, `/video` → `paginate(24)`. Mengikuti pola yang sudah ada di `LibraryController.php:121` dan `RamadhanController.php:16`.

#### Catatan implementasi

- **Pagination ternyata sudah ada.** Premis bagian ini keliru: `Teacher::all()`, `Assembly::...->get()`, dan `Video::all()` di ketiga controller adalah **variabel mati** — view-nya merender `<livewire:list-guru />`, `<livewire:list-majelis />`, dan `<livewire:list-video />`, yang masing-masing sudah `simplePaginate(10)`. Yang dikerjakan tahap ini hanya membuang eager-load sia-sia itu (`Teacher::all()` dan `Assembly::...->get()` sudah hilang di tahap 1; `Video::all()` di tahap ini). Batas 10/halaman dipertahankan, tidak dinaikkan ke 24, karena mengubahnya adalah keputusan produk tanpa manfaat SEO.
- **Font diganti di enam layout, bukan hanya `user`.** `@font-face` tinggal di `resources/css/app.css`, yang dimuat semua layout. Menyisakan `<link>` Google Fonts di lima layout lain berarti satu permintaan render-blocking ke pihak ketiga yang hasilnya tidak lagi dipakai. Layout `app`, `authentication`, `dashboard`, `empty`, dan `guest` ikut diganti.
- **Subset `latin` dan `latin-ext`**, keduanya variable font weight 400–700. `unicode-range` membuat `latin-ext` hanya diunduh bila halaman benar-benar memuat karakternya. Berkas lisensi ikut disimpan di `public/fonts/Inter-OFL.txt` sebagaimana disyaratkan OFL.
- **`--font-inter` diperbaiki.** Nilainya `"Inter", "sans-serif"` — `sans-serif` berkutip adalah nama famili literal, bukan keyword generik, sehingga fallback-nya tidak pernah resolve. Ini justru yang terlihat pembaca selama `font-display: swap` menunggu. Diganti `"Inter", ui-sans-serif, system-ui, sans-serif`.
- **`width`/`height` hanya ditambahkan bila CSS belum memaku kotaknya.** Pada gambar ber-class `h-48`, `h-56`, `h-80`, atau `w-16 h-16`, tinggi sudah pasti sehingga tidak ada layout shift untuk dicegah; di sana yang ditambahkan cukup `loading="lazy"`.
- **Poster acara pertama tetap `eager`** lewat `loading="{{ $loop->first ? 'eager' : 'lazy' }}"` di `list-event` dan `home-event`. Poster inilah kandidat elemen LCP; menundanya justru memperlambat halaman.
- **Perbaikan di luar spec: `500` pada jadwal tanpa guru.** Test `<h1>` tahap ini menemukan bahwa `schedules.teacher_id` nullable sementara empat pemanggilan `route('guru-detail', $schedule->teacher)` di `list-jadwal-majelis` dan `home-jadwal-majelis`, serta satu di `majelis/detail`, tidak berpenjaga. Satu jadwal tanpa guru membuat **beranda, `/jadwal-majelis`, dan detail majelis** membalas `500` — perayap membacanya sebagai situs rusak, bukan sekadar kosong, dan itu langsung membatalkan kriteria penerimaan #9. `href`-nya kini bersyarat dan nama guru memakai `?->`. Dikunci test `jadwal_tanpa_guru_tidak_meruntuhkan_halaman_publik`.
- **`noindex` untuk `?page=` > 1 tidak dipasang** (lihat bagian 11 dan RS7). Canonical sudah membuang query string sejak tahap 2, jadi `?page=2` sudah menunjuk `/guru`. Menambahkan `noindex` di atas canonical adalah sinyal yang saling bertentangan dan justru dapat membuat Google mengabaikan keduanya. Penemuan konten dalam tetap terjamin karena sitemap memuat setiap URL detail secara langsung; `?page=2` sendiri terbukti dirender di sisi server dan dapat dirayapi (dikunci test).

- Satu `<h1>` per halaman, berisi **entitas sebenarnya**.
  - `guru/detail.blade.php:22` — `"Detail Guru"` diturunkan menjadi teks biasa/breadcrumb; `<h1>` di `:67` (nama guru) dipertahankan.
  - `biography/detail.blade.php:22` — `"Manaqib Ulama"` diturunkan; `<h1>` di `:40` dipertahankan.
  - Pola yang sama diterapkan pada `majelis/detail`, `jadwal-majelis/detail`.
- `alt` deskriptif: `guru/detail.blade.php:46` dari `alt="Avatar"` menjadi `alt="Foto {{ $teacher->name }}"`.
- Gambar dekoratif murni diberi `alt=""` agar dilewati pembaca layar.

#### Catatan implementasi

- **Beranda punya tiga `<h1>`,** bukan satu: "Majelis Hari Ini" di `home.blade.php`, plus "Acara Akan Datang" dan "Haul Terdekat" dari widget Livewire (dan "Jadwal Ramadhan Hari Ini" begitu widgetnya diaktifkan lagi). Judul widget diturunkan menjadi `<h2>` — itu memang heading bagian, bukan judul halaman. Class-nya tidak diubah, dan Tailwind preflight menghapus ukuran bawaan heading, sehingga tampilannya sama persis.
- **`<h1>` beranda masih berbunyi "Majelis Hari Ini".** Halaman ini tidak punya judul halaman di desainnya, jadi menggantinya dengan nama/tagline situs adalah keputusan salinan milik pemilik produk, bukan perubahan teknis. Struktur headingnya sudah benar; teksnya yang masih lemah.
- **`jadwal-majelis/detail` tidak punya `<h1>` entitas sama sekali** — hanya label "Detail Jadwal". Nama jadwal (`<h2>` di `:51`) dinaikkan menjadi `<h1>`, dan "Catatan Jadwal" dari `<h3>` menjadi `<h2>` agar urutan headingnya tidak melompat.
- **`alt="Application 22"` pada `list-majelis.blade.php`** — sisa template Mosaic pada gambar majelis sungguhan — diganti `alt="Gambar {{ $assembly->nama_majelis }}"`.
- **Gambar di dalam modal pratinjau** (`list-event`, `home-event`) diberi `alt=""`. Isinya adalah perbesaran gambar yang teksnya sudah diumumkan di kartu; mengulanginya hanya menambah kebisingan bagi pembaca layar.

---

## Data Model

Tiga migration baru, seluruhnya **aditif dan backward-compatible** (tidak menghapus tabel/kolom/data, sesuai Database Rules proyek):

| Migration | Perubahan |
|---|---|
| `xxxx_add_slug_to_assemblies_schedules_events_tables.php` | `slug` (`string`, nullable, unique, index) pada `assemblies`, `schedules`, `events` |
| `xxxx_add_manaqib_to_teachers_table.php` | `manaqib` (`text`, nullable) pada `teachers` |
| `xxxx_backfill_seo_slugs.php` | Data migration: isi `slug` untuk seluruh baris existing dari `nama_majelis` / `nama_jadwal` / `name`. Idempoten (`whereNull('slug')`), dan **`down()` tidak mengosongkan data** |

**Peringatan operasional:** menurut catatan proyek, skema database produksi pernah menyimpang dari migration (tabel `teachers` diedit manual), sehingga `migrate:fresh` di lokal tidak mencerminkan produksi. Migration di atas harus diverifikasi terhadap skema produksi sebelum dijalankan — lihat [Verifikasi End-to-End](#verifikasi-end-to-end).

---

## Role dan Otorisasi

Fitur ini **tidak menambah role, permission, atau perubahan otorisasi apa pun**. Semua metadata diturunkan otomatis dan tidak dapat diedit siapa pun.

| Aktor | Kemampuan terkait fitur ini |
|---|---|
| Anonim / mesin telusur | Membaca halaman publik yang lolos filter visibilitas, `/sitemap.xml`, `/robots.txt` |
| Jamaah (login) | Sama seperti anonim; halaman pribadinya `noindex` |
| Kontributor | Mengisi field `manaqib` pada kontribusi guru — melewati alur moderasi `pending → approved` yang sudah ada, tanpa perubahan |
| `Penulis` | Tidak berubah |
| `Super Admin` | Mengisi `manaqib` tanpa moderasi; dapat melihat konten `pending` lewat `isVisibleTo()` yang sudah ada |

Yang **berubah** adalah pengetatan: konten `pending`/`rejected` yang sebelumnya bocor ke anonim (K1–K3) kini `404` / tersembunyi.

---

## API atau UI

Fitur ini **murni server-rendered UI + dua endpoint publik non-HTML**. Tidak ada perubahan pada API partner (`routes/api/v1.php`) dan tidak ada kontrak JSON baru.

| Endpoint | Method | Auth | Response |
|---|---|---|---|
| `/sitemap.xml` | `GET` | — | `application/xml` |
| `/robots.txt` | `GET` | — | file statis |

Perubahan UI yang terlihat pengguna:

1. URL majelis/jadwal/acara kini mengandung nama (URL lama tetap bekerja lewat 301).
2. Beranda berada di `/`, bukan `/beranda`.
3. Halaman detail acara baru.
4. Halaman `/guru` dan `/majelis` menjadi berhalaman (paginated).
5. Field `manaqib` baru di form guru (admin & kontributor).
6. Preview WhatsApp/Facebook menampilkan judul, deskripsi, dan gambar yang benar.

---

## File yang Terlibat

### Baru

| File | Peran |
|---|---|
| `app/Services/SeoService.php` | Penurunan title, description, canonical, image |
| `app/Services/StructuredDataService.php` | Pembangun JSON-LD |
| `app/Http/Controllers/SitemapController.php` | `/sitemap.xml` (invokable) |
| `app/Http/Middleware/AddNoindexHeader.php` | `X-Robots-Tag` untuk area privat |
| `resources/views/components/seo.blade.php` | Komponen `<x-seo>` |
| `resources/views/pages/user/events/detail.blade.php` | Halaman detail acara |
| `database/migrations/xxxx_add_slug_to_assemblies_schedules_events_tables.php` | Kolom slug |
| `database/migrations/xxxx_add_manaqib_to_teachers_table.php` | Kolom manaqib |
| `database/migrations/xxxx_backfill_seo_slugs.php` | Backfill slug |
| `public/fonts/inter-*.woff2` | Font self-hosted |
| `public/images/og/*.png` | Banner kategori 1200×630 — **disediakan pemilik produk** |
| `tests/Feature/Seo/*.php` | Lihat bagian Testing |

### Diubah

| File | Perubahan |
|---|---|
| `resources/views/layouts/user.blade.php` | `@stack('head')`, canonical, hapus `keywords`, `og:url` dari canonical, font lokal |
| `resources/views/layouts/{dashboard,app,guest}.blade.php` | `@yield('title')` + `noindex` |
| `routes/web.php:57` | `/` menyajikan `HomeController`; `/beranda` 301 |
| `routes/web.php:60-67` | Parameter slug untuk majelis, jadwal-majelis; route detail acara baru |
| `app/Http/Kernel.php` | Registrasi `AddNoindexHeader` |
| `app/Http/Controllers/User/GuruController.php:14` | `publiclyVisible()` + `paginate()` |
| `app/Http/Controllers/User/MajelisController.php:14,21` | `publiclyVisible()`, `isVisibleTo()`, `paginate()`, kanonikalisasi slug |
| `app/Http/Controllers/User/JadwalMajelisController.php` | `publiclyVisible()`, kanonikalisasi slug |
| `app/Http/Controllers/User/EventController.php` | Method `detail()` |
| `app/Http/Controllers/User/VideoController.php:11` | `paginate()` |
| `app/Livewire/ListEvent.php:48` | `status='approved'` + `access='Umum'` |
| `app/Models/Assembly.php` | `isVisibleTo()`, `generateSlug()`, `getSlugParamAttribute()` |
| `app/Models/Schedule.php`, `app/Models/Event.php` | `generateSlug()`, `getSlugParamAttribute()` |
| `app/Models/Teacher.php` | `manaqib` ikut `$guarded = []` (tidak perlu perubahan kode) |
| `app/Http/Controllers/GuruController.php` + view admin guru | Field `manaqib` |
| `app/Http/Controllers/User/KontribusiGuruController.php` + view | Field `manaqib` |
| `app/Http/Controllers/User/ManagedMajelisController.php` | Regenerasi slug saat rename |
| Seluruh view publik di `resources/views/pages/user/**` | `<x-seo>`, satu `<h1>`, `alt` deskriptif, `width`/`height`/`loading` |
| `public/robots.txt` | Direktif lengkap + `Sitemap:` |
| `.env.example` | Tambah `GOOGLE_ANALYTICS_ID`, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`; `APP_URL` diberi komentar bahwa nilainya dipakai untuk canonical |

### Interface yang Digunakan Kembali (tidak diubah)

- `Teacher::generateSlug()` — `app/Models/Teacher.php:21-34` (pola slug)
- `Teacher::isVisibleTo()` — `:119-130` (pola visibilitas)
- `Teacher::scopePubliclyVisible()` / `Assembly` / `Schedule` / `ScheduleNote`
- `Post::published()`, `Library::isFree()` / `isPaid()` — `app/Models/Library.php:46-52`
- `clean()` (mews/purifier) untuk field `manaqib`
- `HandlesImageUploads` — varian `large`/`thumb` untuk `og:image`
- `config('services.google_analytics.id')` — GA yang sudah terpasang

---

## Yang Tidak Termasuk Scope

1. **`Article` structured data** untuk `/tulisan` dan `/artikel` — ditunda ke iterasi berikutnya.
2. **Slug untuk `ScheduleNote` dan `RamadhanSchedule`** — tetap ID.
3. **Acara & jadwal ramadhan di sitemap** — konten musiman.
4. **Riset kata kunci, penulisan ulang konten, dan strategi backlink** — pekerjaan editorial/marketing, bukan rekayasa.
5. **Audit & code-splitting bundle JS** (jQuery, moment, chart.js, pdfjs) — scope terpisah yang menyentuh konfigurasi build.
6. **Server-side rendering untuk komponen Livewire** — sudah SSR, tidak perlu.
7. **Pembersihan debris template Flowbite/Mosaic** (~40 route, 7 controller, 9 model) — CLAUDE.md melarang menyentuhnya tanpa diskusi. Cukup ditutup lewat `robots.txt` + `is_admin`.
8. **Pendaftaran Google Search Console / Bing Webmaster** — tugas operasional pemilik produk.
9. **Hreflang / multi-bahasa** — situs monolingual (`id-ID`).
10. **Redirect www→non-www dan http→https** — level server/Cloudflare, di luar repo.
11. **AMP** — sudah usang, tidak relevan.
12. **Perubahan pada API partner `/api/v1/*`** — kontrak stabil, tidak disentuh.

---

## Edge Cases

| # | Kasus | Perilaku yang diharapkan |
|---|---|---|
| E1 | Guru tanpa `foto` | `og:image` jatuh ke banner kategori `guru`, bukan broken image |
| E2 | Majelis tanpa `deskripsi` | `meta description` memakai template fallback berisi nama + alamat + jumlah jadwal |
| E3 | `biografi` berisi HTML dengan entitas (`&amp;`, `&nbsp;`) | `strip_tags` + normalisasi whitespace + decode entitas sebelum dipotong 155 karakter |
| E4 | Nama yang tidak menghasilkan slug apa pun (mis. emoji atau tanda baca saja) | `slug` = `null`; URL menjadi `/majelis/42` saja dan **tetap valid** (resolusi by ID). Tidak boleh menghasilkan `/majelis/42-`. **Catatan:** nama berhuruf Arab **tidak** masuk kasus ini — `Str::slug()` mentransliterasinya (`مجلس` → `mgls`), jadi slug tetap terbentuk |
| E5 | Dua majelis bernama sama persis | Slug boleh identik — ID yang membedakan. Tidak ada tabrakan |
| E6 | Nama majelis diedit | Slug diperbarui; URL lama `/majelis/42-nama-lama` → **301** → `/majelis/42-nama-baru` |
| E7 | URL `/majelis/42-slug-ngawur-buatan-orang` | **301** ke bentuk kanonik, bukan 404 — mencegah spam URL menghasilkan konten duplikat |
| E8 | URL `/majelis/abc` (bukan angka) | `(int) 'abc' === 0` → `findOrFail(0)` → **404** |
| E9 | Majelis berstatus `pending` | Detail **404** untuk anonim; tetap terbuka untuk kontributor pemilik & Super Admin (pratinjau); **tidak** masuk sitemap |
| E10 | Acara berstatus `rejected` | Tidak tampil di `/event`, detail **404** (perbaikan K3) |
| E11 | Acara `access = 'Khusus'` (undangan) | Tidak punya halaman detail publik, **404**, tidak masuk `Event` schema |
| E12 | Pustaka `price_type = 'paid'` | Halaman detail tetap publik, tetapi **tidak** masuk sitemap |
| E13 | Catatan pengajian `visibility='Private'` atau `status != 'Approved'` | Sudah 404 hari ini (`CatatanPengajianController.php:17-20`); dipastikan **tidak** masuk sitemap |
| E14 | `manaqib` kosong | `/manaqib/{slug}` canonical → `/guru/{slug}`, tidak masuk sitemap |
| E15 | Guru `wafat_masehi` kosong | Properti `deathDate` **dihilangkan** dari JSON-LD `Person` — bukan diisi `null` (validator Google menolak `null`) |
| E16 | Acara `date` di masa lalu | Detail tetap dapat diakses (arsip), `Event.eventStatus` tetap `EventScheduled`. Tidak di sitemap |
| E17 | `APP_URL` masih `http://localhost` di produksi | Canonical dan sitemap akan salah host. **Feature test wajib menyetel `APP_URL`** dan verifikasi produksi mengeceknya lebih dulu |
| E18 | Sitemap > 10.000 URL | Pecah menjadi sitemap index |
| E19 | Cache sitemap basi setelah konten disetujui | Toleransi 6 jam diterima; moderasi tidak perlu memicu invalidasi cache |
| E20 | `/beranda` masih tersebar di tautan WhatsApp lama | **301** permanen ke `/`; tidak ada tautan yang mati |
| E21 | Judul entitas > 60 karakter | `<title>` dipotong pada batas kata + `— Syaikhuna`; `<h1>` tetap penuh |
| E22 | Halaman daftar dengan `?page=2` | `canonical` ke URL bersih, `robots: noindex, follow` |

---

## Kompatibilitas

- **URL lama tidak ada yang mati.** Seluruh `/majelis/{id}`, `/jadwal-majelis/{id}`, `/beranda` di-301 permanen. Tautan yang sudah tersebar di WhatsApp, bookmark, dan indeks Google tetap sampai ke tujuan dan mewariskan otoritasnya.
- **Nama route dipertahankan seluruhnya** (`beranda`, `majelis-detail`, `jadwal-majelis-detail`, dst) sehingga tidak ada `route()` di view yang perlu dicari-ganti.
- **`@yield`/`@section` yang sudah ada tidak dihapus** — 23 view yang sudah mengisi `@section('title')` terus bekerja tanpa disentuh.
- **Migration aditif**: hanya menambah kolom nullable. Tidak ada `drop`, tidak ada `change` pada kolom existing. Aplikasi versi lama tetap berjalan di atas skema baru.
- **API partner `/api/v1/*` tidak tersentuh** — kontrak stabil terjaga.
- **Kompatibilitas SQLite terjaga** untuk test: tidak ada `FIELD()` baru; jika perlu pengurutan hari gunakan `CASE` seperti `GuruController.php:25-33`. Perlu diperhatikan bahwa `MajelisController.php:22` **masih memakai `FIELD()`** — halaman ini karenanya belum dapat diuji di SQLite; lihat [Risiko](#risiko-dan-trade-off).
- **Livewire `#[Url]` tetap berfungsi** — hanya ditambahkan canonical + `noindex`, tanpa mengubah perilaku filter.

---

## Testing

Mengikuti pola `tests/Feature/FaviconTest.php:12-21` (`$this->get(...)->assertSee(...)`) — satu-satunya preseden pengujian isi `<head>` di proyek ini.

Semua test menyetel `config(['app.url' => 'https://syaikhuna.id'])` agar assertion canonical tidak bergantung pada environment.

### Feature — `tests/Feature/Seo/PublicPageMetaTest.php`

- Untuk setiap halaman publik: `<title>` **unik** dan bukan sekadar `"Syaikhuna"`.
- `<meta name="description">` ada, tidak kosong, ≤ 160 karakter, dan **tidak mengandung tag HTML**.
- `<link rel="canonical">` ada, absolut, berhost `syaikhuna.id`, dan **tanpa query string**.
- `og:image` absolut dan bukan string kosong.
- `<meta name="keywords">` **sudah tidak ada**.
- Tepat **satu** `<h1>` per halaman publik.
- Regresi spesifik: detail guru/majelis/jadwal memuat nama entitas di dalam `<title>`.

### Feature — `tests/Feature/Seo/SitemapTest.php`

- `GET /sitemap.xml` → `200`, `Content-Type: application/xml`, XML valid.
- Memuat URL guru approved, majelis approved, tulisan published.
- **Tidak** memuat: guru `pending`/`rejected`, majelis `pending`, pustaka `paid`, catatan `Private`, catatan `status != Approved`, manaqib dengan `manaqib` kosong, URL acara, URL ramadhan.
- Setiap URL di sitemap, ketika di-`GET`, mengembalikan `200` (bukan 301/404) — menjamin sitemap dan canonical konsisten.

### Feature — `tests/Feature/Seo/CanonicalRedirectTest.php`

- `GET /beranda` → `301` ke `/`.
- `GET /majelis/{id}` → `301` ke `/majelis/{id}-{slug}`.
- `GET /majelis/{id}-slug-salah` → `301` ke bentuk kanonik.
- `GET /majelis/{id}-{slug}` → `200` (tidak ada redirect loop).
- `GET /majelis/abc` → `404`.
- Majelis tanpa slug (E4) → `200` di `/majelis/{id}`, tanpa trailing `-`.

### Feature — `tests/Feature/Seo/ContentLeakTest.php`

Test regresi untuk K1–K3:

- Anonim `GET /guru` → **tidak** melihat nama guru `pending`.
- Anonim `GET /majelis/{id-pending}` → `404`.
- Kontributor pemilik `GET /majelis/{id-pending}` → `200`.
- `Super Admin` `GET /majelis/{id-pending}` → `200`.
- Anonim `GET /event` → **tidak** melihat acara `rejected` maupun `access='Khusus'`.
- Anonim `GET /event/{id-rejected}` → `404`.

### Feature — `tests/Feature/Seo/StructuredDataTest.php`

- Halaman publik memuat blok `application/ld+json` yang **valid JSON** (di-`json_decode` di test, bukan sekadar `assertSee`).
- `/guru/{...}` → `@type: Person` dengan `name` benar.
- Guru tanpa `wafat_masehi` → key `deathDate` **absen**, bukan `null` (E15).
- `/event/{...}` → `@type: Event` dengan `startDate` format ISO-8601.
- `Organization` hadir di semua halaman publik.

### Feature — `tests/Feature/Seo/NoindexTest.php`

- Halaman `kelola-*`, `/favorit-saya`, `/pustaka-saya`, `/admin/dashboard` mengirim header `X-Robots-Tag: noindex`.
- `/kontributor/profil/{username}` memuat `<meta name="robots" content="noindex, follow">`.
- Halaman daftar dengan `?page=2` → `noindex, follow` + canonical bersih.

### Unit — `tests/Unit/SeoServiceTest.php`

- `description()`: memotong tepat di 155 karakter, membuang tag HTML, menormalkan whitespace ganda, decode entitas HTML, dan mengembalikan fallback saat input kosong/hanya-tag.
- `canonical()`: selalu absolut, membuang query string, tanpa trailing slash (kecuali root).
- `image()`: urutan fallback entitas → banner → logo.
- `generateSlug()` pada `Assembly`: nama non-ASCII → `null` (E4), nama duplikat → tidak error (E5).

### Catatan menjalankan test

Test memakai SQLite in-memory (`phpunit.xml:24-25`). Catatan lingkungan: **PHP CLI di mesin pengembangan utama tidak memuat `pdo_sqlite`** — perlu mengaktifkan ekstensi tersebut lebih dulu, atau menjalankan test lewat PHP dari Laragon yang memuatnya. Ini bukan bagian dari perubahan spec, tetapi menghalangi verifikasi jika diabaikan.

---

## Acceptance Criteria

1. Setiap halaman publik mengirim `<title>` unik yang memuat nama entitas; tidak ada lagi halaman detail ber-`<title>` `"Syaikhuna"`.
2. Setiap halaman publik mengirim `<meta name="description">` yang tidak kosong, ≤ 160 karakter, bebas tag HTML.
3. Setiap halaman publik mengirim `<link rel="canonical">` absolut berhost `syaikhuna.id` tanpa query string.
4. `<meta name="keywords">` sudah tidak ada di seluruh output.
5. Setiap halaman publik mengirim `og:title`, `og:description`, `og:image`, dan `og:url` yang konsisten dengan canonical; `og:image` selalu absolut dan selalu ada.
6. `GET /` → `200` menyajikan beranda; `GET /beranda` → `301` ke `/`.
7. `GET /majelis/{id}` dan `/jadwal-majelis/{id}` → `301` ke bentuk `/{id}-{slug}`; bentuk kanonik → `200` tanpa redirect loop.
8. Slug diperbarui saat nama entitas diedit, dan URL lama tetap `301` ke bentuk baru.
9. `GET /sitemap.xml` → `200`, XML valid, hanya berisi konten yang sah tayang; **setiap URL di dalamnya mengembalikan `200`**.
10. `public/robots.txt` memuat direktif `Sitemap:` dan seluruh `Disallow` yang disepakati.
11. Konten `pending`/`rejected` (guru, majelis, acara) `404` untuk anonim dan absen dari sitemap; kontributor pemilik & Super Admin tetap dapat melihatnya.
12. Acara `rejected` dan `access='Khusus'` tidak tampil di `/event` maupun detailnya.
13. `/event/{id}-{slug}` tersedia untuk acara `approved` + `Umum`, memuat JSON-LD `Event` valid.
14. `Organization` hadir di semua halaman publik; `Person` di halaman guru & manaqib; seluruh JSON-LD lolos `json_decode`.
15. Kolom `manaqib` tersedia dan dapat diisi admin serta kontributor (via moderasi); selama kosong, `/manaqib/{slug}` canonical ke `/guru/{slug}` dan absen dari sitemap.
16. Area privat dan `/admin/*` mengirim `X-Robots-Tag: noindex`.
17. `/guru`, `/majelis`, `/video` berhalaman (paginated).
18. Font Inter dilayani dari domain sendiri; tidak ada request ke `fonts.googleapis.com`.
19. Tepat satu `<h1>` per halaman publik, berisi nama entitas.
20. Seluruh migration bersifat aditif; tidak ada kolom/tabel/data yang dihapus.
21. `php artisan test` lulus; `./vendor/bin/pint` bersih; `npm run build` sukses.
22. Tidak ada perubahan pada `routes/api/v1.php` maupun kontrak API partner.

---

## Verifikasi End-to-End

### Prasyarat

- [ ] Konfirmasi `APP_URL=https://syaikhuna.id` di produksi (E17). Canonical yang salah host lebih merusak daripada tidak ada canonical sama sekali.
- [ ] Konfirmasi redirect `www → non-www` dan `http → https` sudah aktif di level server/Cloudflare.
- [ ] Terima aset banner `public/images/og/*.png` (1200×630) dari pemilik produk. Sampai tersedia, implementasi jatuh ke logo.
- [ ] Verifikasi skema tabel `teachers`, `assemblies`, `schedules`, `events` di produksi cocok dengan migration — repositori ini punya riwayat penyimpangan skema.
- [ ] Aktifkan `pdo_sqlite` pada PHP CLI agar test dapat dijalankan.

### Langkah verifikasi manual

1. **Migration & backfill (staging lebih dulu)**
   - Ambil backup database.
   - `php artisan migrate` — verifikasi tiga migration baru berjalan.
   - Cek: `SELECT COUNT(*) FROM assemblies WHERE slug IS NULL;` — sisanya hanya baris E4 (nama non-ASCII).
   - Jalankan ulang backfill; pastikan idempoten (tidak ada baris berubah).

2. **Redirect**
   - `curl -sI https://syaikhuna.id/beranda` → `301`, `Location: https://syaikhuna.id/`
   - `curl -sI https://syaikhuna.id/majelis/1` → `301` ke `/majelis/1-{slug}`
   - `curl -sI https://syaikhuna.id/majelis/1-{slug}` → `200` (bukan 301 — pastikan tidak ada loop)
   - `curl -sI https://syaikhuna.id/majelis/abc` → `404`

3. **Metadata**
   - Buka view-source `/guru/{slug}`: pastikan `<title>` berisi nama guru, ada `canonical`, `og:image` menunjuk foto guru.
   - Ulangi untuk `/majelis/{...}`, `/jadwal-majelis/{...}`, `/event/{...}`, `/tulisan/{slug}`.

4. **Sitemap & robots**
   - `curl https://syaikhuna.id/sitemap.xml | xmllint --noout -` → tanpa error.
   - Ambil 10 URL acak dari sitemap, `curl -sI` masing-masing → semua `200`.
   - `curl https://syaikhuna.id/robots.txt` → memuat baris `Sitemap:`.

5. **Kebocoran moderasi**
   - Buat majelis `pending` di staging. Sebagai anonim (incognito): `/majelis/{id}` → `404`.
   - Login sebagai kontributor pemiliknya → `200`.
   - Tolak sebuah acara → hilang dari `/event`, detailnya `404`.
   - Cari ID majelis `pending` tersebut di `/sitemap.xml` → tidak ditemukan.

6. **Structured data**
   - Uji `/guru/{slug}` dan `/event/{...}` di [Rich Results Test](https://search.google.com/test/rich-results) → `Person` dan `Event` terdeteksi tanpa error.
   - Uji di [Schema Markup Validator](https://validator.schema.org/).

7. **Preview share**
   - Uji `/guru/{slug}` di Facebook Sharing Debugger → judul, deskripsi, gambar guru benar.
   - Kirim tautan `/majelis/{...}` ke WhatsApp → preview menampilkan nama + gambar majelis, bukan logo.

8. **Performa**
   - PageSpeed Insights (mobile) pada `/` dan `/guru/{slug}`.
   - Panel Network: **tidak ada** request ke `fonts.googleapis.com`.
   - Bandingkan LCP & CLS sebelum/sesudah — CLS harus turun berkat `width`/`height` pada gambar.

9. **Pasca-deploy (operasional)**
   - Daftarkan properti di Google Search Console, submit `/sitemap.xml`.
   - Ajukan pengindeksan ulang untuk `/` dan beberapa halaman guru utama.
   - Pantau laporan Coverage 2–4 minggu: pastikan tidak ada lonjakan `Duplicate without user-selected canonical` maupun `Submitted URL not found (404)`.

---

## Risiko dan Trade-off

| # | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| RS1 | Perubahan URL massal membuat Google merayapi ulang seluruh situs; peringkat dapat berfluktuasi 2–6 minggu | Sedang, sementara | Semua **301** (permanen, mewariskan otoritas), bukan 302. Sitemap disubmit segera setelah deploy |
| RS2 | `APP_URL` salah di produksi → seluruh canonical & sitemap menunjuk host keliru | **Tinggi** — dapat mendeindeks situs | Dijadikan prasyarat berpalang di checklist verifikasi; feature test menyetel `app.url` eksplisit |
| RS3 | Backfill slug pada tabel besar mengunci tabel | Rendah–sedang | Backfill dalam `chunk()`; dijalankan di jam sepi; backup dulu |
| RS4 | Skema produksi menyimpang dari migration (riwayat nyata pada `teachers`) → migration gagal di tengah | **Tinggi** | Verifikasi skema produksi sebagai prasyarat; uji di staging hasil dump produksi, bukan `migrate:fresh` |
| RS5 | `MajelisController.php:22` masih memakai `FIELD()` (khusus MySQL) sehingga halaman detail majelis **tidak dapat diuji** di SQLite | Sedang — menghalangi acceptance criteria #1 & #7 | Ganti ke `CASE` mengikuti preseden `GuruController.php:25-33`. Perubahan ini kecil, satu query, dan **wajib** agar test dapat menutup halaman ini |
| RS6 | Memperketat visibilitas (K1–K3) menyembunyikan konten yang selama ini terlihat; pemilik konten bisa mengira datanya hilang | Sedang | Kontributor pemilik tetap melihat pratinjau. Umumkan ke kontributor sebelum rilis; sediakan daftar konten yang perlu dimoderasi kepada admin |
| RS7 | Pagination `/guru` & `/majelis` mengubah pengalaman yang sudah dikenal (semua data dalam satu halaman) | Rendah | 24 item/halaman; halaman >1 diberi `noindex, follow` sehingga tidak menimbulkan duplikasi |
| RS8 | Dua URL untuk satu ulama tetap ada sampai `manaqib` terisi | Rendah | Canonical bersyarat menjamin nol duplikasi sejak hari pertama; sitemap hanya memuat manaqib yang benar-benar terisi |
| RS9 | Field `manaqib` menambah beban editorial yang mungkin tidak pernah diisi | Rendah | Fitur tetap benar saat kosong (halaman canonical ke `/guru`). Tidak ada regresi jika diabaikan |
| RS10 | Cache sitemap 6 jam → konten baru terlambat terdaftar | Rendah | Google merayapi sitemap harian, bukan per jam. Toleransi diterima |
| RS11 | JSON-LD tidak akurat (tanggal salah, lokasi kosong) dapat memicu manual action Google | Rendah–sedang | Properti tanpa data **dihilangkan**, tidak diisi `null`; divalidasi di Rich Results Test sebelum rilis |
| RS12 | Self-host font mengubah rendering halus jika subset salah | Rendah | Subset latin + weight 400–700 sesuai penggunaan; verifikasi visual di beranda & detail guru |
| RS13 | Diff menyentuh banyak view sekaligus sehingga review berat | Sedang | Pecah menjadi beberapa PR berurutan: (1) kebocoran moderasi + test, (2) `SeoService` + `<x-seo>` + meta, (3) slug + 301, (4) sitemap + robots, (5) detail acara + JSON-LD, (6) performa + heading |

---

## Temuan di Luar Scope

Ditemukan selama analisis, **tidak** ditangani dokumen ini, dicatat agar tidak hilang:

1. `app/Livewire/PustakaChat copy.php` dan `app/Services/OpenNotebookService copy.php` — file duplikat yang belum di-commit.
2. Artefak di root repositori: `serve.log`, `server.log`, `server_output.log`, `verification.py`, `verification_tulisan_initial.png`, `verification_tulisan_article_tab.png`.
3. Dua lock file berdampingan — `package-lock.json` dan `pnpm-lock.yaml` — package manager proyek ambigu.
4. `routes/web.php` sepanjang 426 baris mencampur route publik, user, admin, dan debris template dalam satu file.
5. `Route::fallback()` (`routes/web.php:423`) hanya terdaftar **di dalam grup admin**; tidak ada halaman 404 kustom untuk pengunjung publik.
6. **Ketidaksesuaian dokumentasi**: CLAUDE.md menyebut env `OPEN_NOTEBOOK_BASE_URL`, sedangkan kode membaca `OPEN_NOTEBOOK_API_URL` (`config/services.php:50`, `.env.example:64`).
7. **Ketidaksesuaian dokumentasi**: CLAUDE.md menyatakan hanya ada dua role (`Super Admin`, `Penulis`), padahal role `Kontributor` aktif dipakai (`routes/web.php:183`).
8. **Ketidaksesuaian dokumentasi**: Definition of Done CLAUDE.md mensyaratkan "lint dan type check lulus", padahal proyek tidak punya ESLint, Prettier, maupun TypeScript — `package.json` hanya berisi script `dev` dan `build`.
9. **Ketidaksesuaian dokumentasi**: `README.md:136` mengklaim situs "SEO-friendly" — klaim ini baru menjadi benar setelah spec ini diimplementasikan.
10. `app/Http/Controllers/User/EventController::list()` mengembalikan view tanpa memuat data apa pun; seluruh logika ada di `app/Livewire/ListEvent.php` — pola yang tidak konsisten dengan controller publik lain.
11. **Blok "Who to follow" berisi pengguna palsu.** `components/community/feed-right-content.blade.php:32-77` merender empat pengguna fiktif ("User 01"–"User 05") dengan avatar template, dan komponen ini tampil di **setiap** halaman daftar publik serta beranda. Sisa template yang terlanjur jadi konten publik; menghapus atau menggantinya dengan majelis/guru sungguhan adalah keputusan produk.
12. `components/home/list-majelis.blade.php` dan `components/home/jadwal-majelis.blade.php` tidak dirujuk dari mana pun — beranda memakai komponen Livewire. Keduanya masih menyimpan gambar dan `alt` template.
