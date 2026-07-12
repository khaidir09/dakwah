# Dashboard Admin — Ringkasan Data

**Status Dokumen:** Draft
**Tanggal:** 2026-07-12
**Author:** Muhammad Khaidir

---

## Latar Belakang

Halaman `/admin/dashboard` saat ini **tidak menampilkan satu pun data Syaikhuna**. Isinya sepenuhnya sisa template Mosaic: kartu grafik e-commerce palsu (`dashboard-card-04` s/d `dashboard-card-11`) yang menampilkan "Sales Over Time", "Top Countries", "Income/Expenses", dsb. `DashboardController@index()` hanya melakukan `new DataFeed()` lalu me-render view.

Akibatnya, Super Admin yang login tidak punya titik masuk untuk mengetahui: berapa banyak konten yang ada, apa yang tumbuh, dan — yang paling penting — **apa yang sedang menunggu dimoderasi**. Antrian moderasi hari ini hanya terlihat kalau admin membuka satu per satu halaman index (majelis, guru, jadwal, amalan, acara, catatan) dan mengklik tab "Moderasi" di masing-masing halaman.

Fitur ini mengganti isi dashboard dengan ringkasan nyata: **KPI konten + antrian moderasi + aktivitas terbaru**.

---

## Tujuan

Menjadikan `/admin/dashboard` sebagai halaman pertama yang berguna bagi Super Admin — menjawab dua pertanyaan sekaligus dalam satu layar:

1. **"Bagaimana kondisi platform?"** → jumlah konten hidup dan pertumbuhannya 30 hari terakhir.
2. **"Apa yang harus saya kerjakan sekarang?"** → berapa banyak kontribusi menunggu moderasi, dan yang mana.

---

## Perilaku Saat Ini

| Aspek | Kondisi sekarang |
|---|---|
| Route | `GET /admin/dashboard` → `DashboardController@index`, name `dashboard`, middleware `['auth:sanctum', 'verified', 'is_admin']` (`routes/web.php:215-216`) |
| Controller | `DashboardController@index()` — instansiasi `new DataFeed()`, kirim ke view. Tidak ada query data nyata. |
| View | `resources/views/pages/dashboard/dashboard.blade.php` — judul "Dashboard" + 8 kartu template Mosaic (`dashboard-card-04` … `-11`). Kartu `-01`, `-02`, `-03` sudah dikomentari. |
| Data | Semua angka/grafik di kartu tersebut **hardcoded di dalam komponen**, bukan dari database Syaikhuna. |
| Antrian moderasi | Tersebar di 6 halaman index terpisah; tiap komponen Livewire menghitung `$pending_count` sendiri-sendiri. |

---

## Expected Behavior

Dashboard terdiri dari **tiga bagian**, dari atas ke bawah.

### Bagian 1 — Kartu Statistik (KPI)

Delapan kartu angka. Tiap kartu menampilkan: label, angka total, dan delta 30 hari.

| Kartu | Model | Definisi "Total" |
|---|---|---|
| Pengguna | `User` | `User::count()` (semua user terdaftar) |
| Majelis | `Assembly` | `Assembly::publiclyVisible()->count()` |
| Guru | `Teacher` | `Teacher::publiclyVisible()->count()` |
| Jadwal | `Schedule` | `Schedule::publiclyVisible()->count()` |
| Acara | `Event` | `Event::publiclyVisible()->count()` |
| Amalan | `Wirid` | `Wirid::publiclyVisible()->count()` |
| Tulisan | `Post` | `Post::published()->count()` (`status = 'published'`) |
| Pustaka | `Library` | `Library::count()` (tidak ada moderasi) |

**Semantik total = "konten yang hidup di platform"**, bukan `COUNT(*)` mentah. Item `pending` dan `rejected` **tidak** ikut dihitung. Scope `publiclyVisible()` sudah ada di 6 model (`Assembly`, `Teacher`, `Schedule`, `Event`, `Wirid`, `ScheduleNote`) dan berarti `contribution_status IS NULL OR = 'approved'` — `NULL` adalah data lama/buatan admin yang dianggap sah.

**Delta 30 hari** — dihitung dari `created_at`, membandingkan dua periode:

- `current` = jumlah baris (dengan filter total yang sama) dengan `created_at` dalam 30 hari terakhir.
- `previous` = jumlah baris dengan `created_at` antara 60 dan 30 hari lalu.
- Tampilan:
  - `previous > 0` → persentase: `round((current - previous) / previous * 100)`, dengan panah naik (hijau) / turun (merah).
  - `previous == 0 && current > 0` → tampilkan **`+N baru`** tanpa persentase (menghindari pembagian nol).
  - `current == 0 && previous == 0` → tidak ada badge delta sama sekali.

### Bagian 2 — Panel "Menunggu Moderasi"

Satu kartu berisi **6 baris hitungan** + **daftar 5 item pending terbaru (gabungan lintas jenis)**.

| Jenis | Model | Kondisi pending | Link tujuan |
|---|---|---|---|
| Majelis | `Assembly` | `contribution_status = 'pending'` | `route('majelis.index', ['tab' => 'moderasi'])` |
| Guru | `Teacher` | `contribution_status = 'pending'` | `route('guru.index', ['tab' => 'moderasi'])` |
| Jadwal | `Schedule` | `contribution_status = 'pending'` | `route('jadwal-majelis.index', ['tab' => 'moderasi'])` |
| Amalan | `Wirid` | `contribution_status = 'pending'` | `route('wirid.index', ['tab' => 'moderasi'])` |
| Acara | `Event` | `status = 'pending'` **AND** `user_id IS NOT NULL` | `route('event.index', ['tab' => 'moderasi'])` |
| Catatan Pengajian | `ScheduleNote` | `contribution_status = 'pending'` | `route('schedule-notes.index')` — tanpa filter (lihat Catatan) |

> **Perhatikan dua konvensi kolom yang berbeda.** `Event` memakai kolom `status`; lima lainnya memakai `contribution_status`. Ini bukan kesalahan — ikuti persis seperti yang sudah dilakukan `app/Livewire/Acara.php:65` dan `ModerasiController@moderasiEvent`. Filter `whereNotNull('user_id')` pada Event penting: acara yang dibuat admin sendiri tidak boleh masuk antrian moderasi.

Baris dengan hitungan **0** tetap ditampilkan (dengan angka 0, warna netral, tidak nge-link) agar admin tahu antrian itu memang kosong, bukan hilang.

**Daftar 5 item pending terbaru** digabung dari keenam sumber, diurutkan `created_at` menurun, diambil 5 teratas. Tiap baris: badge jenis, judul, nama kontributor, waktu relatif (`diffForHumans`), dan link ke halaman moderasinya.

**Tidak ada tombol approve/reject di dashboard.** Aksi tetap dilakukan di halaman aslinya lewat `ModerasiController` / `ScheduleNoteController`, supaya logika XP, badge, dan notifikasi di `KhidmahService` tidak terduplikasi.

### Bagian 3 — Aktivitas Terbaru

Satu feed gabungan, **10 item**, urut waktu terbaru. Sumber:

| Sumber | Model | Keterangan |
|---|---|---|
| Pendaftaran user baru | `User` | `latest()` |
| Konten baru masuk | `Assembly`, `Teacher`, `Schedule`, `Event`, `Wirid` | `latest()`, **terlepas dari status moderasi** (ini feed "apa yang terjadi", bukan "apa yang publik") |
| Tulisan baru | `Post` | `latest()` |
| Komentar baru | `Comment` | `latest()`, polymorphic (`commentable`: Teacher / ScheduleNote) |

Implementasi: tiap sumber di-query `latest()->limit(10)`, di-map ke bentuk seragam, di-`merge`, di-`sortByDesc('created_at')`, lalu `take(10)`. **Bukan** `UNION` SQL — tabelnya berbeda-beda bentuk dan volumenya kecil.

Bentuk seragam tiap item:

```php
[
    'type'       => 'majelis',            // slug jenis, untuk badge & ikon
    'label'      => 'Majelis baru',       // teks yang dibaca admin
    'title'      => $assembly->nama_majelis,
    'actor'      => $assembly->user?->name,   // boleh null
    'url'        => route('majelis.index'),   // boleh null
    'created_at' => $assembly->created_at,
]
```

---

## Role & Authorization

| Role | Akses `/admin/dashboard` |
|---|---|
| Super Admin | Ya — melihat seluruh dashboard |
| Penulis | Tidak |
| Jamaah / kontributor | Tidak |
| Guest | Tidak (redirect ke login) |

**Tidak ada perubahan otorisasi.** Route sudah berada di grup `['auth:sanctum', 'verified', 'is_admin']`; middleware `IsAdmin` (`app/Http/Middleware/IsAdmin.php`) mengecek `hasRole('Super Admin')`. Tidak ada percabangan isi per-role — dashboard ini eksklusif Super Admin.

`DashboardStatsService` tetap ditulis tanpa asumsi "user saat ini adalah admin" (tidak membaca `Auth::user()` di dalamnya) supaya nanti bisa dipakai ulang untuk dashboard role lain tanpa refactor.

---

## Data Model

**Tidak ada perubahan schema. Tidak ada migration baru. Tidak ada kolom/tabel yang dihapus.** Fitur ini murni membaca.

Kolom yang dibaca:

| Tabel | Kolom relevan |
|---|---|
| `assemblies` | `contribution_status`, `created_at`, `nama_majelis`, `user_id` |
| `teachers` | `contribution_status`, `created_at`, `name`, `contributor_user_id` |
| `schedules` | `contribution_status`, `created_at`, `nama_jadwal`, `contributor_user_id` |
| `wirids` | `contribution_status`, `created_at`, `nama`, `contributor_user_id` |
| `events` | `status`, `user_id`, `created_at`, `name` |
| `schedule_notes` | `contribution_status`, `created_at`, `user_id` |
| `posts` | `status`, `created_at`, `title`, `user_id` |
| `libraries` | `created_at` |
| `users` | `created_at`, `name` |
| `comments` | `created_at`, `user_id`, `commentable_type`, `commentable_id` |

`contribution_status` adalah `enum('pending','approved','rejected') NULL` (lihat migration `2026_06_24_00000{2,3,4,5}_*` dan `2026_06_26_175508_*`). **`NULL` berarti data lama / dibuat admin → dianggap approved**, sesuai `scopePubliclyVisible()`.

### Catatan penting: `ScheduleNote` punya dua kolom status

`schedule_notes` memiliki `status` (`'Approved'`/`'Rejected'`, huruf besar) **dan** `contribution_status` (`'pending'`/`'approved'`/`'rejected'`, huruf kecil). `ScheduleNoteController@approve()` menulis keduanya. Untuk dashboard, **gunakan `contribution_status`** — itulah kolom yang ditulis saat kontributor submit (`User\ScheduleNoteController:39`), yang dibaca `scopePubliclyVisible()`, dan yang dikelola `KhidmahService`. Kolom `status` tidak dipakai di fitur ini.

---

## UI

Tidak ada API/endpoint baru. Tidak ada route baru. Tidak ada komponen Livewire baru — dashboard di-render server-side sekali jalan (tidak butuh reaktivitas).

Layout tetap `<x-app-layout>` (AppLayout, layout Super Admin).

Susunan grid mengikuti pola Mosaic yang sudah ada (`grid grid-cols-12 gap-6`):

- KPI: 8 kartu, masing-masing `col-span-12 sm:col-span-6 xl:col-span-3`
- Menunggu Moderasi: `col-span-12 xl:col-span-6`
- Aktivitas Terbaru: `col-span-12 xl:col-span-6`

**Gaya visual meniru kartu Mosaic** (`bg-white dark:bg-gray-800 shadow-xs rounded-xl border border-gray-200 dark:border-gray-700`) agar konsisten dengan sisa area admin — tapi dibuat sebagai **komponen baru**, tanpa mengubah atau menghapus `dashboard-card-*` yang lama.

---

## Implementasi

### File yang Berubah

| File | Status | Perubahan |
|---|---|---|
| `app/Services/DashboardStatsService.php` | **BARU** | Semua query: totals, delta, antrian moderasi, activity feed |
| `app/Http/Controllers/DashboardController.php` | Ubah | `index()` inject `DashboardStatsService`; hapus `new DataFeed()` yang tidak lagi dipakai view ini |
| `resources/views/pages/dashboard/dashboard.blade.php` | Tulis ulang | Ganti 8 kartu Mosaic dengan 3 bagian di atas |
| `resources/views/components/dashboard/stat-card.blade.php` | **BARU** | Kartu KPI (label, angka, badge delta) |
| `resources/views/components/dashboard/moderation-panel.blade.php` | **BARU** | Panel antrian moderasi |
| `resources/views/components/dashboard/activity-feed.blade.php` | **BARU** | Feed aktivitas |
| `app/Livewire/Majelis.php` | Ubah | +1 baris di `mount()` — hidrasi `tab` dari query string |
| `app/Livewire/Guru.php` | Ubah | idem |
| `app/Livewire/JadwalMajelis.php` | Ubah | idem |
| `app/Livewire/Wirids.php` | Ubah | idem |
| `app/Livewire/Acara.php` | Ubah | idem |
| `tests/Feature/AdminDashboardTest.php` | **BARU** | Smoke test |

`routes/web.php` **tidak berubah**.

### Perbaikan hidrasi `tab` (prasyarat deep-link)

Kelima komponen index mendeklarasikan `protected $updatesQueryString = ['search', 'tab'];`. **Ini API Livewire v2 yang diabaikan Livewire 3** (proyek memakai `livewire/livewire ^3.6`). Akibatnya `?tab=moderasi` di URL tidak terbaca sama sekali — properti `$tab` tetap `'semua'`. Properti `search` hanya berfungsi karena `mount()` menghidrasinya manual.

Tanpa perbaikan ini, link "Menunggu Moderasi" dari dashboard akan mendarat di tab "Semua", bukan tab moderasi.

Perbaikan minimal — satu baris di `mount()` tiap komponen, mengikuti pola `search` yang sudah ada:

```php
public function mount()
{
    $this->search = request()->query('search', $this->search);
    $this->tab = request()->query('tab', $this->tab);   // <— baris baru
}
```

Backward-compatible: tanpa `?tab=`, nilainya tetap default `'semua'`. Komponen `Guru`, `JadwalMajelis`, `Wirids`, `Acara` yang belum punya `mount()` perlu ditambahkan method `mount()`-nya.

> **Sengaja TIDAK** memigrasi `$updatesQueryString` ke atribut `#[Url]` Livewire 3. Itu perbaikan yang lebih "benar" secara framework, tapi mengubah perilaku sinkronisasi query-string `search` yang sekarang berjalan manual — risiko regresi di luar scope dashboard. Dicatat sebagai utang teknis.

### Struktur `DashboardStatsService`

```php
namespace App\Services;

class DashboardStatsService
{
    private const CACHE_KEY = 'admin.dashboard.summary';
    private const CACHE_TTL = 600; // 10 menit

    /** KPI + activity feed — di-cache. */
    public function summary(): array;

    /** Antrian moderasi — SELALU real-time, tidak pernah di-cache. */
    public function moderationQueues(): array;

    /** 5 item pending terbaru lintas jenis — real-time. */
    public function latestPending(int $limit = 5): array;
}
```

### Strategi cache

| Data | Cache | Alasan |
|---|---|---|
| KPI (totals + delta) | `Cache::remember(self::CACHE_KEY, 600, …)` | Tidak mendesak; basi 10 menit tidak berbahaya. Menghemat ~16 COUNT query per load. |
| Aktivitas terbaru | ikut di payload cache yang sama | Sama — bersifat informatif, bukan aksi. |
| **Antrian moderasi** | **tidak di-cache** | Ini angka yang dipakai admin untuk bertindak. Setelah admin approve sesuatu, angkanya **harus** langsung turun. |

Cache memakai **plain key, bukan tag** — driver cache proyek (file/database) tidak mendukung tag. Tidak ada invalidasi eksplisit; staleness maksimal 10 menit diterima. `KhidmahService` **tidak** disentuh.

---

## Edge Cases

| Kasus | Perilaku yang diharapkan |
|---|---|
| Database kosong (fresh install) | Semua KPI `0`, tanpa badge delta. Panel moderasi menampilkan 6 baris bernilai 0. Feed aktivitas menampilkan empty state *"Belum ada aktivitas."* Halaman tetap 200, tidak error. |
| Periode sebelumnya = 0, sekarang > 0 | Badge `+N baru`, **bukan** `+∞%` atau `Division by zero`. |
| Periode sekarang = 0 dan sebelumnya = 0 | Tidak ada badge delta sama sekali. |
| `contribution_status = NULL` (data lama) | Dihitung sebagai **publik/approved**, **bukan** pending. |
| Acara dibuat admin (`user_id IS NULL`, `status = 'pending'`) | **Tidak** masuk antrian moderasi (konsisten dengan `Livewire\Acara:65`). |
| `Comment` yang `commentable`-nya sudah dihapus | `morphTo()` mengembalikan `null` → item di-skip dari feed, tidak memicu error. |
| Konten pending yang `user_id`/`contributor_user_id`-nya null | Kolom kontributor ditampilkan `—`, tidak error (`$model->user?->name`). |
| Judul konten kosong/null | Fallback `'(tanpa judul)'`. Perhatikan tiap model punya nama kolom judul berbeda (`nama_majelis`, `name`, `nama_jadwal`, `nama`, `title`) — sama seperti `ModerasiController@getLabel()`. |
| Item pending dihapus setelah query hitungan | Tidak relevan — hitungan dan daftar diambil dalam request yang sama. |
| Zona waktu | Aplikasi memakai `Asia/Makassar` (WITA). Batas 30/60 hari memakai `Carbon::now()->subDays(30)` yang otomatis mengikuti `APP_TIMEZONE`. |

---

## Compatibility

- **Template debris tidak dihapus.** Komponen `dashboard-card-01` … `dashboard-card-11`, model `DataFeed`, `DataFeedController`, dan route `/admin/dashboard/analytics` + `/admin/dashboard/fintech` **tetap utuh dan tetap berfungsi**. Halaman analytics & fintech masih memakai sebagian kartu tersebut; menghapusnya akan merusak halaman itu, dan CLAUDE.md melarang menghapus template debris tanpa diskusi.
- Satu-satunya pemutusan hubungan: `DashboardController@index()` berhenti mengirim `$dataFeed` ke view, karena view yang baru tidak memakainya. `DashboardController@analytics()` dan `@fintech()` **tidak disentuh**.
- Tidak ada perubahan database → tidak ada risiko migrasi, tidak perlu rollback plan.
- Perubahan pada 5 komponen Livewire bersifat aditif (menambah hidrasi query-string yang sebelumnya tidak jalan). Perilaku default tanpa `?tab=` tidak berubah.

---

## Yang Tidak Termasuk Scope

- **Grafik / chart apa pun.** Tidak ada Chart.js, tidak ada grafik tren bulanan. Hanya angka + delta.
- **Filter periode** (7/30/90 hari) — periode dikunci di 30 hari.
- **Approve/reject dari dashboard.** Aksi moderasi tetap di halaman masing-masing.
- **Antrian Pembelian Pustaka (`LibraryPurchase`) dan Klaim Reward (`RewardClaim`).** Keduanya punya alur admin sendiri; tidak masuk dashboard v1.
- **Panel monetisasi / nilai rupiah.**
- **Penghapusan** `dashboard-card-*`, `DataFeed`, `DataFeedController`, route analytics/fintech.
- **Migrasi `$updatesQueryString` → `#[Url]`** pada komponen Livewire (utang teknis, dicatat terpisah).
- **Dashboard untuk role Penulis atau kontributor.**
- **Filter pending pada halaman admin Catatan Pengajian** (`ScheduleNoteController@index` saat ini menampilkan semua catatan `Public` tanpa tab moderasi). Dashboard cukup nge-link ke halamannya tanpa filter.
- **Migration / perubahan schema apa pun.**
- Kesehatan data (majelis tanpa jadwal, guru tanpa biografi, dsb.) — ide bagus, tapi fitur terpisah.

---

## Acceptance Criteria

- [ ] **AC-1**: Super Admin membuka `/admin/dashboard` dan mendapat **200**, tanpa satu pun kartu template Mosaic lama (tidak ada "Sales", "Countries", "Income/Expenses").
- [ ] **AC-2**: Tampil 8 kartu KPI: Pengguna, Majelis, Guru, Jadwal, Acara, Amalan, Tulisan, Pustaka.
- [ ] **AC-3**: Angka total tiap kartu memakai semantik "publik" — konten `pending` dan `rejected` **tidak** ikut terhitung; konten dengan `contribution_status = NULL` **ikut** terhitung.
- [ ] **AC-4**: Tiap kartu menampilkan delta 30 hari; saat periode pembanding bernilai 0 dan periode sekarang > 0, tampil `+N baru` (bukan error atau `∞%`).
- [ ] **AC-5**: Panel "Menunggu Moderasi" menampilkan 6 baris (Majelis, Guru, Jadwal, Amalan, Acara, Catatan) dengan hitungan `pending` yang benar per jenis.
- [ ] **AC-6**: Acara `pending` yang dibuat admin (`user_id` null) **tidak** ikut dihitung di antrian Acara.
- [ ] **AC-7**: Mengklik baris antrian membawa admin ke halaman index terkait **dengan tab Moderasi sudah aktif** (kecuali Catatan Pengajian, yang ke index biasa).
- [ ] **AC-8**: Panel moderasi menampilkan 5 item pending terbaru gabungan lintas jenis, lengkap dengan jenis, judul, kontributor, dan waktu.
- [ ] **AC-9**: Panel "Aktivitas Terbaru" menampilkan 10 item gabungan (user baru, konten baru, tulisan, komentar) urut waktu terbaru.
- [ ] **AC-10**: Setelah admin approve satu kontribusi lalu kembali ke dashboard, **angka antrian langsung berkurang** (antrian tidak di-cache).
- [ ] **AC-11**: Non-admin (Penulis / jamaah) yang membuka `/admin/dashboard` ditolak; guest diarahkan ke login.
- [ ] **AC-12**: Dengan database kosong, dashboard tetap render 200 dengan angka 0 dan empty state — tidak ada exception.
- [ ] **AC-13**: Halaman `/admin/dashboard/analytics` dan `/admin/dashboard/fintech` tetap berfungsi seperti sebelumnya.

---

## Test Plan (`tests/Feature/AdminDashboardTest.php`)

Smoke test, `RefreshDatabase`, SQLite in-memory (sudah dikonfigurasi di `phpunit.xml`).

| Test | Skenario |
|---|---|
| `test_super_admin_can_view_dashboard` | Login Super Admin → `GET /admin/dashboard` → assert 200 |
| `test_non_admin_cannot_view_dashboard` | Login user biasa (tanpa role) → assert ditolak (403 / redirect) |
| `test_guest_is_redirected_to_login` | Tanpa login → assert redirect ke login |
| `test_dashboard_renders_with_empty_database` | DB kosong, login Super Admin → assert 200 (tidak ada exception, tidak ada division-by-zero) |

Sesuai keputusan, assertion **tidak** memverifikasi nilai angka. Kebenaran angka diverifikasi manual lewat langkah end-to-end di bawah.

---

## Verifikasi End-to-End

1. Nyalakan MySQL Laragon, jalankan aplikasi.
2. Login sebagai **Super Admin**, buka `/admin/dashboard`.
3. Verifikasi: tidak ada lagi grafik e-commerce palsu. Tampil 8 kartu KPI + panel Menunggu Moderasi + panel Aktivitas Terbaru.
4. Buka `/admin/majelis`, hitung manual jumlah majelis yang statusnya bukan pending/rejected. Bandingkan dengan angka kartu **Majelis** di dashboard — harus sama.
5. Login sebagai **kontributor** (user biasa), submit **satu majelis baru** lewat alur kontribusi.
6. Kembali sebagai Super Admin, muat ulang dashboard. Verifikasi: baris **Majelis** di panel Menunggu Moderasi bertambah 1, dan majelis tadi muncul di daftar "5 item pending terbaru" beserta nama kontributornya.
7. Verifikasi juga majelis baru itu **belum** menambah angka kartu KPI "Majelis" (karena masih pending, belum publik).
8. Klik baris **Majelis** di panel moderasi. Verifikasi: mendarat di `/admin/majelis?tab=moderasi` **dengan tab Moderasi sudah aktif** dan majelis pending tadi terlihat.
9. Setujui majelis tersebut.
10. Kembali ke dashboard. Verifikasi: angka antrian Majelis **langsung turun 1** (real-time, tidak tertahan cache).
11. Verifikasi kartu KPI "Majelis": angkanya naik 1 — **tapi mungkin baru terlihat setelah ≤10 menit** karena KPI di-cache. Ini perilaku yang diinginkan, bukan bug.
12. Ulangi langkah 5–10 untuk **Acara** (pastikan acara buatan admin tidak masuk antrian) dan **Catatan Pengajian**.
13. Verifikasi panel Aktivitas Terbaru memuat user yang baru mendaftar dan konten yang baru dibuat.
14. Logout, login sebagai **Penulis**. Buka `/admin/dashboard` → verifikasi ditolak.
15. Buka `/admin/dashboard/analytics` dan `/admin/dashboard/fintech` sebagai Super Admin → verifikasi keduanya **masih render normal** (tidak rusak oleh perubahan ini).

---

## Risiko & Trade-off

| Risiko | Dampak | Mitigasi / keputusan |
|---|---|---|
| **KPI basi hingga 10 menit** | Admin approve konten, tapi angka KPI belum berubah | Diterima secara sadar. Angka yang *penting untuk bertindak* (antrian moderasi) selalu real-time; KPI hanya informatif. Jika mengganggu, turunkan TTL atau invalidasi `admin.dashboard.summary` di `KhidmahService` — tapi itu menambah coupling, sengaja dihindari di v1. |
| **Jumlah query cukup banyak** (~16 COUNT untuk KPI + ~7 untuk antrian + ~8 untuk feed) | Load dashboard melambat saat data membesar | KPI & feed di-cache 10 menit, jadi biaya penuh hanya sekali per 10 menit. Antrian & COUNT-nya ringan (`WHERE contribution_status = 'pending'`). Jika lambat di kemudian hari: tambahkan index pada `contribution_status` / `status`. **Tidak** ditambahkan sekarang — belum terbukti perlu, dan itu perubahan schema. |
| **Activity feed di-sort di PHP, bukan SQL** | Boros memori kalau limit dinaikkan drastis | Aman pada `limit(10)` per sumber (≤80 baris). Jangan naikkan limit tanpa mengganti pendekatan. |
| **Menyentuh 5 komponen Livewire di luar dashboard** | Potensi regresi pada halaman index | Perubahannya satu baris aditif per komponen dan default-nya tidak berubah. Halaman index yang ada punya test (`ListWiridTest`, dll.) — jalankan `php artisan test` penuh setelah perubahan. |
| **Dua konvensi kolom status** (`status` vs `contribution_status`) | Mudah salah hitung, terutama pada Event | Didokumentasikan eksplisit di spec ini. Terpusat di `DashboardStatsService` agar tidak menyebar. |
| **Semantik "total = publik" bisa mengejutkan** | Angka dashboard ≠ `COUNT(*)` di tabel | Disengaja: dashboard mengukur *konten hidup*, bukan baris database. Panel moderasi yang menjelaskan sisanya. |
| **Utang teknis `$updatesQueryString`** | Bug laten Livewire v2→v3 masih ada di 5 komponen | Ditambal minimal (hidrasi manual di `mount()`), tidak dimigrasikan penuh ke `#[Url]`. Dicatat sebagai pekerjaan lanjutan. |

---

## Pekerjaan Lanjutan (bukan bagian dari fitur ini)

1. Migrasi `$updatesQueryString` → atribut `#[Url]` di seluruh komponen Livewire (memperbaiki bug laten Livewire 3).
2. Tab/filter moderasi pada halaman admin Catatan Pengajian.
3. Antrian Pembelian Pustaka & Klaim Reward di dashboard.
4. Panel "Kesehatan Data" (majelis tanpa jadwal, guru tanpa biografi, jadwal basi).
5. Grafik tren pertumbuhan 12 bulan.
6. Pembersihan template debris Mosaic secara menyeluruh (perlu diskusi terpisah).
