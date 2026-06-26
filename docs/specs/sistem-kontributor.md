# Sistem Kontributor

**Status Dokumen:** Final Draft  
**Tanggal:** 2026-06-24  
**Author:** Muhammad Khaidir  

---

## Latar Belakang

Syaikhuna bertujuan menjadi "Sistem Operasi Digital" bagi kehidupan religius masyarakat Banjar, dengan data majelis, guru, jadwal, acara, dan amalan yang terpusat dan terpercaya. Saat ini seluruh data hanya dapat ditambahkan oleh Admin, yang menciptakan bottleneck dan membatasi kecepatan pengayaan data.

---

## Masalah

Penggambaran data yang kaya dan akurat memerlukan kontribusi komunitas, namun tidak ada jalur bagi pengguna biasa untuk menambah atau memperkaya data. Admin menjadi satu-satunya penjaga gerbang, sehingga pertumbuhan data terhambat.

---

## Tujuan

1. Membuka jalur bagi pengguna terverifikasi untuk berkontribusi data (Majelis, Guru, Jadwal Majelis, Acara, Amalan/Wirid).
2. Menjaga kualitas data melalui alur moderasi admin sebelum data tayang ke publik.
3. Memberikan insentif kontribusi melalui sistem poin XP dan badge yang dapat dikonfigurasi admin.
4. Menampilkan leaderboard kontributor sebagai apresiasi komunitas.

---

## Non-Goals

- Sistem komentar atau diskusi antar kontributor.
- Export atau laporan kontribusi.
- Kontributor menambahkan media (foto/video) ke entitas milik pengguna lain.
- Filter periode (bulanan/tahunan) pada leaderboard.
- Editing data milik pengguna lain (termasuk data yang sudah ada sebelum fitur ini).

---

## Aktor dan Role

| Aktor | Spatie Role | Keterangan |
|---|---|---|
| Jamaah | _(tidak ada role khusus)_ | Pengguna terautentikasi, belum mendaftar Kontributor |
| Kontributor | `Kontributor` | Pengguna terautentikasi yang sudah mendaftar dan memenuhi syarat |
| Admin | `Super Admin` | Memoderasi submission dan mengelola pengaturan XP |

---

## Current Behavior

- Semua data (Majelis, Guru, Jadwal, Acara, Amalan) hanya dapat ditambah oleh Admin di `/admin/*`.
- `User` memiliki relasi `hasOne(Assembly::class)` — satu user satu majelis.
- Tabel `contributions` sudah ada dengan kolom: `user_id`, `contributable_id`, `contributable_type`, `points_earned`.
- Kolom `total_khidmah_points` (default 0) dan `badge_title` (default 'Jamaah Aktif') sudah ada di tabel `users`.
- Kolom `phone` sudah ada di tabel `users`.
- Tabel `events` sudah memiliki pola moderasi: `user_id`, `status` (pending/approved/rejected), `moderated_at`.
- Tidak ada tabel notifikasi in-app; Laravel `notifications` table belum digunakan.
- Tidak ada role `Kontributor` di Spatie.

---

## Expected Behavior

- Pengguna yang memenuhi syarat dapat mendaftar sebagai Kontributor secara mandiri.
- Kontributor dapat mengirim data baru yang masuk ke tabel utama dengan status `pending`.
- Admin memoderasi submission melalui tab khusus di halaman admin yang sudah ada.
- Data `approved` tampil ke publik; data `pending` dan `rejected` hanya terlihat oleh si Kontributor di dashboardnya.
- XP diberikan setelah admin menyetujui; badge diperbarui otomatis saat XP melewati threshold.
- Pengguna mendapat notifikasi in-app dan email untuk: disetujui, ditolak (dengan alasan), dan naik badge.
- Admin dapat mengubah nilai XP per jenis kontribusi melalui halaman pengaturan.

---

## User Stories

### Pendaftaran Kontributor
- **US-01:** Sebagai Jamaah, saya ingin membaca informasi tentang program Kontributor agar saya memahami manfaatnya sebelum mendaftar.
- **US-02:** Sebagai Jamaah yang profilnya sudah lengkap dan email terverifikasi, saya ingin mendaftar menjadi Kontributor dengan satu klik agar saya bisa mulai berkontribusi.
- **US-03:** Sebagai Jamaah yang profilnya belum lengkap, saya ingin mendapat informasi field apa yang masih perlu diisi agar saya tahu apa yang harus dilengkapi.

### Pengiriman Kontribusi
- **US-04:** Sebagai Kontributor, saya ingin menambahkan Majelis baru agar data majelis di Syaikhuna semakin lengkap.
- **US-05:** Sebagai Kontributor, saya ingin menambahkan Guru baru agar informasi ulama bisa diakses komunitas.
- **US-06:** Sebagai Kontributor, saya ingin menambahkan Jadwal pada Majelis yang saya buat agar jadwal pengajian terdaftar dengan lengkap.
- **US-07:** Sebagai Kontributor, saya ingin menambahkan Acara baru agar informasi acara keagamaan tersebar ke komunitas.
- **US-08:** Sebagai Kontributor, saya ingin menambahkan Amalan/Wirid baru agar koleksi amalan bertambah.

### Riwayat Kontribusi
- **US-09:** Sebagai Kontributor, saya ingin melihat riwayat semua kontribusi saya beserta statusnya (Menunggu/Disetujui/Ditolak) agar saya tahu progres saya.
- **US-10:** Sebagai Kontributor, saya ingin mengedit dan mengirim ulang submission yang ditolak agar saya bisa memperbaiki data yang kurang tepat.
- **US-11:** Sebagai Kontributor, saya ingin mengedit data kontribusi saya yang sudah disetujui agar informasinya tetap akurat.

### Moderasi Admin
- **US-12:** Sebagai Admin, saya ingin melihat semua submission pending melalui tab di halaman admin yang sudah ada agar workflow moderasi tidak terpisah-pisah.
- **US-13:** Sebagai Admin, saya ingin menyetujui atau menolak submission dengan alasan agar Kontributor mendapat feedback yang jelas.
- **US-14:** Sebagai Admin, saya ingin mengatur nilai XP per jenis kontribusi agar sistem gamifikasi bisa disesuaikan.

### XP, Badge, dan Leaderboard
- **US-15:** Sebagai Kontributor, saya ingin mendapat XP setelah kontribusi disetujui dan naik badge secara otomatis agar kontribusi saya terasa dihargai.
- **US-16:** Sebagai pengguna publik, saya ingin melihat leaderboard kontributor agar saya termotivasi untuk ikut berkontribusi.

---

## User Flow

### Flow 1: Pendaftaran Kontributor

```
Pengguna mengunjungi /kontributor
    → Membaca informasi program + manfaat + leaderboard
    → Klik "Daftar Jadi Kontributor"
        ├─ [Belum login] → Redirect ke login
        ├─ [Login, email belum verified] → Tampil pesan "Verifikasi email dulu"
        ├─ [Login, profil tidak lengkap] → Tampil pesan + daftar field yang belum diisi + link ke /user/profile/edit
        └─ [Login, email verified, profil lengkap] → Role `Kontributor` diberikan langsung
                → Flash success + redirect ke /kontributor/saya
```

### Flow 2: Pengiriman Kontribusi

```
Kontributor mengunjungi /kontributor/saya
    → Klik "Tambah [Majelis/Guru/Jadwal/Acara/Amalan]"
    → Mengisi formulir (field identik dengan form admin)
    → Submit
        → Data tersimpan ke tabel utama dengan contribution_status = 'pending'
        → Record baru di tabel contributions (points_earned = 0 sementara)
        → Redirect ke /kontributor/saya dengan flash "Kontribusi berhasil dikirim, menunggu moderasi"
```

### Flow 3: Moderasi Admin

```
Admin membuka halaman admin (misal /admin/majelis)
    → Klik tab "Perlu Moderasi"
    → Melihat daftar submission pending
    → Klik "Lihat Detail" → Preview data lengkap
        ├─ Klik "Setujui"
        │       → contribution_status = 'approved', moderated_at = now()
        │       → XP ditambahkan ke contributions dan users.total_khidmah_points
        │       → Badge diperiksa dan diperbarui jika perlu
        │       → Notifikasi in-app + email dikirim ke Kontributor
        │
        └─ Klik "Tolak" → Form alasan penolakan (teks bebas, wajib)
                → contribution_status = 'rejected', rejection_reason disimpan
                → Notifikasi in-app + email dikirim ke Kontributor (dengan alasan)
```

### Flow 4: Edit dan Kirim Ulang Setelah Ditolak

```
Kontributor melihat status "Ditolak" di /kontributor/saya
    → Membaca alasan penolakan
    → Klik "Edit & Kirim Ulang"
    → Mengisi ulang formulir
    → Submit
        → Data diperbarui in-place, contribution_status kembali ke 'pending'
        → rejection_reason dibersihkan (NULL)
```

### Flow 5: Edit Data yang Sudah Disetujui

```
Kontributor melihat status "Disetujui" di /kontributor/saya
    → Klik "Edit"
    → Mengubah data
    → Submit
        → Data tersimpan langsung, tanpa moderasi ulang
        → contribution_status tetap 'approved'
```

---

## Functional Requirements

### FR-01: Halaman Informasi Kontributor
- Tersedia di `/kontributor` (publik, tanpa login).
- Menampilkan: deskripsi program, daftar manfaat, tabel badge (nama + threshold XP), tabel top 10 kontributor (nama, badge, total poin), tombol "Daftar Jadi Kontributor".

### FR-02: Pendaftaran Kontributor
- Syarat: email terverifikasi + `name`, `phone`, `province_code`, `city_code`, `district_code`, `village_code` semua terisi.
- Jika syarat terpenuhi: role `Kontributor` diberikan langsung, tanpa persetujuan admin.
- Jika syarat tidak terpenuhi: tampilkan daftar field yang belum diisi dengan link ke halaman edit profil.
- Jika sudah menjadi Kontributor: tombol "Daftar" diganti dengan "Kelola Kontribusi Saya".

### FR-03: Dashboard Kontributor
- Tersedia di `/kontributor/saya` (auth + verified + role Kontributor).
- Menggunakan `DashboardLayout`.
- Menampilkan: total XP, badge saat ini, progress ke badge berikutnya, riwayat semua kontribusi dengan status.
- Riwayat per item menampilkan: jenis data, judul/nama, tanggal kirim, status, XP diperoleh, alasan penolakan (jika ditolak), tombol aksi (Edit / Edit & Kirim Ulang).
- Riwayat kontribusi menggunakan pagination.

### FR-04: Form Kontribusi
- Tersedia untuk: Majelis, Guru, Jadwal (hanya ke majelis milik sendiri), Acara, Amalan/Wirid.
- Field identik dengan form admin untuk masing-masing entitas.
- Jadwal: dropdown majelis hanya menampilkan majelis milik Kontributor yang bersangkutan.

### FR-05: Moderasi Admin
- Di setiap halaman admin yang relevan (`/admin/majelis`, `/admin/guru`, `/admin/jadwal-majelis`, `/admin/events`, `/admin/wirid`): tambahkan tab atau filter "Perlu Moderasi".
- Tab tersebut menampilkan daftar submission berstatus `pending` beserta nama Kontributor pengirim.
- Tombol "Setujui" dan "Tolak" tersedia pada setiap item pending.
- Penolakan memerlukan alasan (teks bebas, wajib diisi).
- Pada item yang sudah berstatus `approved`, Admin dapat membatalkan persetujuan (revoke). Revoke memerlukan alasan (wajib diisi), mengubah status kembali ke `rejected`, mengurangi `total_khidmah_points` sejumlah XP yang pernah diberikan, memperbarui `badge_title` jika XP turun di bawah threshold, dan mengirim notifikasi ke Kontributor.

### FR-06: Sistem XP dan Badge
- XP diberikan ke Kontributor hanya saat submission disetujui.
- `users.total_khidmah_points` diperbarui atomik (increment).
- `users.badge_title` diperbarui otomatis setelah XP berubah berdasarkan threshold.
- Threshold badge:
  - `0–100` XP → `Jamaah Aktif`
  - `101–500` XP → `Penuntut Ilmu`
  - `≥501` XP → `Khadam Banua`

### FR-07: Pengaturan XP Admin
- Tersedia di `/admin/pengaturan/xp-kontribusi`.
- Admin dapat mengubah nilai XP per jenis kontribusi.
- Nilai XP default yang diusulkan:

| Jenis Kontribusi | XP Default |
|---|---|
| Majelis baru | 50 |
| Guru baru | 40 |
| Jadwal Majelis | 15 |
| Acara/Event | 25 |
| Amalan/Wirid | 30 |

- Nilai disimpan di tabel `kontribusi_xp_settings`.

### FR-08: Notifikasi
- Channel: in-app (Laravel database notifications) + email.
- Trigger notifikasi:
  1. Submission disetujui → nama data + XP yang diperoleh.
  2. Submission ditolak → nama data + alasan penolakan.
  3. Badge naik → badge baru yang diperoleh.

### FR-09: Leaderboard
- Menampilkan 10 kontributor teratas berdasarkan `total_khidmah_points` tertinggi.
- Data yang ditampilkan: nama, badge_title, total_khidmah_points.
- Ditampilkan di halaman `/kontributor` (publik).
- Tidak ada filter periode.

### FR-10: Edit Data Disetujui
- Kontributor dapat mengedit data kontribusi miliknya yang berstatus `approved`.
- Perubahan langsung tersimpan tanpa moderasi ulang.
- `contribution_status` tetap `approved`.

---

## Business Rules

- **BR-01:** Kontributor hanya bisa menambah Jadwal ke Majelis yang `user_id`-nya adalah miliknya sendiri.
- **BR-02:** Kontributor tidak bisa mengedit data yang `contributor_user_id`-nya bukan miliknya.
- **BR-03:** Kontributor tidak bisa mengedit data yang dibuat Admin (data lama tanpa `contributor_user_id`).
- **BR-04:** Data dengan `contribution_status = 'pending'` atau `'rejected'` tidak tampil di halaman publik.
- **BR-05:** Data dengan `contribution_status = NULL` (dibuat Admin) selalu tampil di halaman publik.
- **BR-06:** XP hanya diberikan sekali per submission yang disetujui; edit setelah disetujui tidak memberi XP tambahan.
- **BR-07:** Jika submission yang ditolak diedit dan dikirim ulang, status kembali ke `pending` dan `rejection_reason` dikosongkan.
- **BR-08:** Nilai XP yang digunakan adalah nilai dari tabel `kontribusi_xp_settings` pada saat submission disetujui (bukan saat submission dibuat).
- **BR-09:** Badge diperbarui setiap kali `total_khidmah_points` berubah, baik naik maupun turun. Penurunan XP (akibat revoke approval oleh Admin) dapat menurunkan badge jika total XP turun di bawah threshold badge saat ini.
- **BR-10:** Jika akun Kontributor dihapus, data yang sudah disetujui tetap ada di sistem (`contributor_user_id` menjadi `NULL`).
- **BR-11:** Seorang User dapat memiliki lebih dari satu Assembly (relasi `hasMany`).

---

## Authorization Rules

| Aksi | Syarat |
|---|---|
| Melihat `/kontributor` | Publik |
| Daftar jadi Kontributor | Auth + email verified + profil lengkap |
| Mengakses `/kontributor/saya` | Auth + email verified + role `Kontributor` |
| Submit kontribusi baru | Auth + email verified + role `Kontributor` |
| Edit kontribusi milik sendiri | Auth + role `Kontributor` + `contributor_user_id == Auth::id()` |
| Memoderasi submission | Role `Super Admin` |
| Mengelola pengaturan XP | Role `Super Admin` |

---

## Data Requirements

### Data yang Dibaca
- `users`: profil, total_khidmah_points, badge_title
- `contributions`: riwayat kontribusi per user
- `assemblies`, `teachers`, `schedules`, `events`, `wirids`: data utama + status moderasi
- `kontribusi_xp_settings`: nilai XP per jenis

### Data yang Dibuat
- Record baru di `assemblies`, `teachers`, `schedules`, `events`, `wirids` (dengan contribution_status = pending)
- Record baru di `contributions`
- Record baru di `notifications`
- Role assignment di `model_has_roles` (Spatie)
- Record baru di `kontribusi_xp_settings` (saat seed/default)

### Data yang Diubah
- `assemblies/teachers/schedules/events/wirids`: `contribution_status`, `moderated_at`, `rejection_reason`, konten data (saat edit)
- `users`: `total_khidmah_points`, `badge_title`
- `contributions`: `points_earned` (diisi saat approved)

### Data yang Tidak Dihapus
- Submission yang ditolak tidak dihapus; tetap ada dengan status `rejected`.
- Data kontributor yang akunnya dihapus tetap ada (`contributor_user_id = NULL`).

---

## Database Impact

### Tabel Baru

#### `kontribusi_xp_settings`
```
id                  bigint PK
contribution_type   enum('majelis', 'guru', 'jadwal', 'acara', 'amalan')  UNIQUE
points              integer (default sesuai FR-07)
timestamps
```

### Perubahan Tabel yang Ada

#### `assemblies` — tambah kolom
```
contribution_status  enum('pending','approved','rejected') NULL
                     (NULL = dibuat Admin, bukan kontribusi)
rejection_reason     text NULL
moderated_at         timestamp NULL
```
> `user_id` sudah ada (nullable, FK ke users ON DELETE SET NULL). Tidak ada unique constraint — tidak perlu migration baru untuk mendukung `hasMany`.

#### `teachers` — tambah kolom
```
contributor_user_id  bigint unsigned NULL, FK users(id) ON DELETE SET NULL
contribution_status  enum('pending','approved','rejected') NULL
rejection_reason     text NULL
moderated_at         timestamp NULL
```

#### `schedules` — tambah kolom
```
contributor_user_id  bigint unsigned NULL, FK users(id) ON DELETE SET NULL
contribution_status  enum('pending','approved','rejected') NULL
rejection_reason     text NULL
moderated_at         timestamp NULL
```
> `schedules.status` yang ada adalah status operasional (Aktif/Selesai/Batal/Libur Ramadhan) — kolom ini berbeda.

#### `wirids` — tambah kolom
```
contributor_user_id  bigint unsigned NULL, FK users(id) ON DELETE SET NULL
contribution_status  enum('pending','approved','rejected') NULL
rejection_reason     text NULL
moderated_at         timestamp NULL
```

#### `events` — tambah kolom
```
rejection_reason     text NULL
```
> `events` sudah memiliki `user_id`, `status` (pending/approved/rejected), `moderated_at` — hanya butuh `rejection_reason`.

### Perubahan Model (bukan migration)

- `User::assembly()`: `hasOne(Assembly::class)` → `hasMany(Assembly::class)`
- `User`: tambah relasi `contributions()` → `hasMany(Contribution::class)`
- `Assembly`, `Teacher`, `Schedule`, `Event`, `Wirid`: tambah scope `publiclyVisible()` untuk filter `contribution_status IS NULL OR contribution_status = 'approved'`

### Tabel Laravel Baru
- `notifications` — dibuat via `php artisan notifications:table` + migrate

---

## UI / Route Impact

### Route Baru

```php
// Public
GET  /kontributor                           → KontributorController@index

// Auth + verified
POST /kontributor/daftar                    → KontributorController@daftar

// Auth + verified + role:Kontributor
GET  /kontributor/saya                      → User\KontribusiController@index
GET  /kontributor/saya/majelis/create       → User\KontribusiMajelisController@create
POST /kontributor/saya/majelis              → User\KontribusiMajelisController@store
GET  /kontributor/saya/majelis/{id}/edit    → User\KontribusiMajelisController@edit
PUT  /kontributor/saya/majelis/{id}         → User\KontribusiMajelisController@update

GET  /kontributor/saya/guru/create          → User\KontribusiGuruController@create
POST /kontributor/saya/guru                 → User\KontribusiGuruController@store
GET  /kontributor/saya/guru/{id}/edit       → User\KontribusiGuruController@edit
PUT  /kontributor/saya/guru/{id}            → User\KontribusiGuruController@update

GET  /kontributor/saya/jadwal/create        → User\KontribusiJadwalController@create
POST /kontributor/saya/jadwal               → User\KontribusiJadwalController@store
GET  /kontributor/saya/jadwal/{id}/edit     → User\KontribusiJadwalController@edit
PUT  /kontributor/saya/jadwal/{id}          → User\KontribusiJadwalController@update

GET  /kontributor/saya/acara/create         → User\KontribusiAcaraController@create
POST /kontributor/saya/acara                → User\KontribusiAcaraController@store
GET  /kontributor/saya/acara/{id}/edit      → User\KontribusiAcaraController@edit
PUT  /kontributor/saya/acara/{id}           → User\KontribusiAcaraController@update

GET  /kontributor/saya/amalan/create        → User\KontribusiAmalanController@create
POST /kontributor/saya/amalan               → User\KontribusiAmalanController@store
GET  /kontributor/saya/amalan/{id}/edit     → User\KontribusiAmalanController@edit
PUT  /kontributor/saya/amalan/{id}          → User\KontribusiAmalanController@update

// Admin
GET  /admin/pengaturan/xp-kontribusi        → Admin\XpSettingController@index
PUT  /admin/pengaturan/xp-kontribusi        → Admin\XpSettingController@update
PUT  /admin/majelis/{id}/moderasi           → Admin\ModerasiController@moderasiAssembly
PUT  /admin/guru/{id}/moderasi              → Admin\ModerasiController@moderasiTeacher
PUT  /admin/jadwal/{id}/moderasi            → Admin\ModerasiController@moderasiJadwal
PUT  /admin/events/{id}/moderasi            → Admin\ModerasiController@moderasiEvent
PUT  /admin/wirid/{id}/moderasi             → Admin\ModerasiController@moderasiWirid
PUT  /admin/majelis/{id}/revoke             → Admin\ModerasiController@revokeAssembly
PUT  /admin/guru/{id}/revoke                → Admin\ModerasiController@revokeTeacher
PUT  /admin/jadwal/{id}/revoke              → Admin\ModerasiController@revokeJadwal
PUT  /admin/events/{id}/revoke              → Admin\ModerasiController@revokeEvent
PUT  /admin/wirid/{id}/revoke               → Admin\ModerasiController@revokeWirid
```

### Controller Baru

```
app/Http/Controllers/KontributorController.php
app/Http/Controllers/User/KontribusiController.php
app/Http/Controllers/User/KontribusiMajelisController.php
app/Http/Controllers/User/KontribusiGuruController.php
app/Http/Controllers/User/KontribusiJadwalController.php
app/Http/Controllers/User/KontribusiAcaraController.php
app/Http/Controllers/User/KontribusiAmalanController.php
app/Http/Controllers/Admin/XpSettingController.php
app/Http/Controllers/Admin/ModerasiController.php
```

### Model Baru

```
app/Models/KontribusiXpSetting.php
```

### Notification Baru

```
app/Notifications/KontribusiDisetujui.php
app/Notifications/KontribusiDitolak.php
app/Notifications/BadgeNaik.php
```

### UI: Perubahan Halaman Admin yang Ada
- `/admin/majelis`, `/admin/guru`, `/admin/jadwal-majelis`, `/admin/events`, `/admin/wirid`: tambah tab "Perlu Moderasi" di atas tabel data.
- Tab ini menampilkan item dengan `contribution_status = 'pending'` beserta nama Kontributor.

### UI: Halaman Baru
- `/kontributor` — halaman publik informasi + leaderboard.
- `/kontributor/saya` — dashboard Kontributor: ringkasan XP/badge, riwayat kontribusi, tombol tambah data.
- Form create/edit per jenis kontribusi.
- `/admin/pengaturan/xp-kontribusi` — tabel pengaturan XP.

---

## Validasi Input

Validasi form kontribusi mengikuti validasi form admin yang sudah ada untuk masing-masing entitas. Tambahan khusus:

| Field | Rule |
|---|---|
| Jadwal → assembly_id | Wajib milik `Auth::id()` (validasi server-side) |
| Moderasi → alasan penolakan | Wajib diisi jika aksi = tolak |
| Pengaturan XP → points | Integer, min: 1, max: 1000 |

---

## Error Handling

| Skenario | Respons |
|---|---|
| Jamaah mencoba daftar Kontributor tapi profil belum lengkap | Flash message + daftar field yang kurang + link ke edit profil |
| Kontributor mencoba akses `/kontributor/saya` tanpa role | Redirect ke `/kontributor` dengan pesan "Daftar dulu" |
| Kontributor mencoba edit data milik orang lain | 403 Forbidden |
| Kontributor mengirim jadwal ke majelis bukan miliknya | Validasi gagal: "Majelis tidak valid" |
| Admin menolak tanpa mengisi alasan | Validasi gagal: "Alasan penolakan wajib diisi" |
| Admin revoke tanpa mengisi alasan | Validasi gagal: "Alasan pembatalan wajib diisi" |
| Revoke menyebabkan XP negatif (data tidak konsisten) | XP di-clamp ke 0, badge dikembalikan ke 'Jamaah Aktif', log warning |
| Notifikasi email gagal terkirim | Log error, tidak block alur moderasi |

---

## Edge Cases

- **EC-01:** Pengguna yang sudah punya role `Kontributor` mengakses tombol daftar → tampilkan "Anda sudah terdaftar sebagai Kontributor" dan arahkan ke `/kontributor/saya`.
- **EC-02:** Kontributor belum punya majelis mencoba tambah Jadwal → form dropdown majelis kosong + pesan "Tambahkan Majelis dulu".
- **EC-03:** Admin mengubah nilai XP setelah beberapa submission sudah disetujui → nilai XP lama yang sudah tercatat di `contributions.points_earned` tidak berubah retroaktif; nilai baru hanya berlaku untuk approval berikutnya.
- **EC-04:** Kontributor mencapai threshold badge yang sama (misal sudah `Penuntut Ilmu`, dapat XP lebih banyak tapi masih di rentang 101–500) → badge tidak berubah, tidak ada notifikasi naik badge.
- **EC-05:** Akun Kontributor dihapus Admin → `contributor_user_id` menjadi `NULL` (ON DELETE SET NULL), data yang sudah `approved` tetap tampil di publik.
- **EC-06:** Admin menyetujui submission yang sama dua kali (race condition) → gunakan database transaction saat update status + XP untuk mencegah double credit.
- **EC-08:** Admin merevoke approval → XP Kontributor turun di bawah threshold badge saat ini → `badge_title` diturunkan otomatis dan notifikasi dikirim ke Kontributor.
- **EC-09:** Admin merevoke approval pada data yang sudah diedit oleh Kontributor setelah approval → data tetap berubah status ke `rejected` dengan kondisi terkini; tidak ada rollback konten ke versi sebelumnya.
- **EC-10:** Total XP setelah revoke menjadi negatif (misal data tidak konsisten) → XP di-clamp ke 0, badge dikembalikan ke 'Jamaah Aktif'.
- **EC-07:** Kontributor mengedit data yang sudah `approved` dan menyimpan data tidak valid → validasi gagal, data lama tetap tersimpan.

---

## Security Considerations

- **SC-01:** Semua route `/kontributor/saya/*` wajib middleware `auth`, `verified`, dan pengecekan role `Kontributor`.
- **SC-02:** Endpoint moderasi admin wajib middleware `IsAdmin` (role `Super Admin`).
- **SC-03:** Ownership check di server: Kontributor hanya bisa mengakses/edit entitas dengan `contributor_user_id == Auth::id()` atau `user_id == Auth::id()` (untuk Assembly).
- **SC-04:** Semua HTML dari input pengguna (deskripsi, biografi, dll.) wajib diproses dengan `clean()` (mews/purifier) sebelum disimpan, konsisten dengan security notes di CLAUDE.md.
- **SC-05:** Alasan penolakan dari admin disimpan sebagai plain text dan ditampilkan dengan escaping HTML, bukan dirender sebagai HTML.
- **SC-06:** Dropdown assembly pada form jadwal difilter di server-side (bukan hanya client-side) untuk mencegah IDOR.

---

## Compatibility Considerations

- **CC-01:** Perubahan `User::assembly()` dari `hasOne` ke `hasMany` adalah breaking change. Semua referensi `$user->assembly` (singular) di codebase harus diaudit dan diperbarui:
  - Cek seluruh view, controller, dan Livewire component yang mengakses `$user->assembly`.
  - Pola umum yang perlu diperbarui: `$user->assembly->id`, `$user->assembly?->nama_majelis`, dll.
- **CC-02:** `assemblies.user_id` sudah nullable tanpa unique constraint → tidak perlu migration baru untuk mendukung `hasMany`. Relasi sudah kompatibel di level database.
- **CC-03:** Data admin lama di `assemblies`, `teachers`, `schedules`, `events`, `wirids` akan memiliki `contribution_status = NULL` setelah migration. Query publik harus mengakomodasi kondisi ini: `WHERE contribution_status IS NULL OR contribution_status = 'approved'`.
- **CC-04:** `events` sudah memiliki pola moderasi sendiri. Perlu diselaraskan: pastikan tab "Perlu Moderasi" pada `/admin/events` menggunakan `status` (bukan `contribution_status`) yang sudah ada.

---

## Acceptance Criteria

### AC-01: Pendaftaran Kontributor Berhasil
```
Given pengguna sudah login, email terverifikasi, dan semua field profil terisi (name, phone, province, city, district, village)
When pengguna mengklik "Daftar Jadi Kontributor" di halaman /kontributor
Then pengguna mendapat role Kontributor secara langsung
And pengguna diarahkan ke /kontributor/saya dengan pesan sukses
```

### AC-02: Pendaftaran Kontributor Gagal — Profil Tidak Lengkap
```
Given pengguna sudah login dan email terverifikasi, namun belum mengisi field phone
When pengguna mengklik "Daftar Jadi Kontributor"
Then pendaftaran ditolak
And sistem menampilkan pesan yang menyebutkan field "Nomor HP" belum diisi
And terdapat link menuju halaman edit profil
```

### AC-03: Kontribusi Majelis Baru
```
Given pengguna memiliki role Kontributor
When pengguna mengisi dan mengirim form tambah Majelis baru
Then data tersimpan di tabel assemblies dengan contribution_status = 'pending'
And record baru tersimpan di tabel contributions dengan points_earned = 0
And data tidak tampil di halaman publik /majelis
And data tampil di /kontributor/saya dengan status "Menunggu"
```

### AC-04: Admin Menyetujui Kontribusi
```
Given ada submission Majelis dengan contribution_status = 'pending'
When Admin mengklik "Setujui" di tab Perlu Moderasi
Then contribution_status berubah menjadi 'approved'
And contributions.points_earned diisi sesuai nilai di kontribusi_xp_settings untuk 'majelis'
And users.total_khidmah_points bertambah sejumlah XP tersebut
And badge_title diperbarui jika XP melewati threshold
And notifikasi in-app dan email dikirim ke Kontributor
And data tampil di halaman publik /majelis
```

### AC-05: Admin Menolak Kontribusi
```
Given ada submission Guru dengan contribution_status = 'pending'
When Admin mengklik "Tolak" dan mengisi alasan "Foto tidak jelas"
Then contribution_status berubah menjadi 'rejected'
And rejection_reason tersimpan
And notifikasi in-app dan email dikirim ke Kontributor berisi alasan "Foto tidak jelas"
And data tidak tampil di halaman publik /guru
```

### AC-06: Edit dan Kirim Ulang Submission Ditolak
```
Given Kontributor memiliki submission Guru dengan contribution_status = 'rejected'
When Kontributor mengedit data dan menekan "Kirim Ulang"
Then data diperbarui di tabel teachers
And contribution_status kembali menjadi 'pending'
And rejection_reason dikosongkan (NULL)
```

### AC-07: Badge Naik Otomatis
```
Given Kontributor memiliki total_khidmah_points = 98 dan badge_title = 'Jamaah Aktif'
When Admin menyetujui kontribusi Majelis (XP = 50)
Then total_khidmah_points menjadi 148
And badge_title diperbarui menjadi 'Penuntut Ilmu'
And notifikasi in-app dan email "Selamat, Anda naik badge ke Penuntut Ilmu" dikirim
```

### AC-12: Admin Revoke Approval
```
Given ada Majelis dengan contribution_status = 'approved' dan Kontributor mendapat 50 XP dari approval ini
And total_khidmah_points Kontributor adalah 120 (badge: Penuntut Ilmu)
When Admin mengklik "Batalkan Persetujuan" dan mengisi alasan "Data duplikat"
Then contribution_status berubah menjadi 'rejected'
And rejection_reason diisi "Data duplikat"
And total_khidmah_points Kontributor berkurang 50, menjadi 70
And badge_title tetap 'Penuntut Ilmu' (70 masih di rentang 101–500... tunggu, 70 < 101)
```
> Koreksi: 70 XP masuk rentang 0–100, maka badge turun ke 'Jamaah Aktif'.
```
And badge_title diperbarui menjadi 'Jamaah Aktif'
And notifikasi in-app + email dikirim ke Kontributor
And data tidak tampil di halaman publik
```

### AC-08: Admin Ubah Nilai XP
```
Given Admin mengakses /admin/pengaturan/xp-kontribusi
When Admin mengubah nilai XP untuk 'majelis' dari 50 menjadi 75 dan menyimpan
Then nilai baru tersimpan di tabel kontribusi_xp_settings
And approval berikutnya untuk kontribusi Majelis memberikan 75 XP
And approval sebelumnya tidak terpengaruh
```

### AC-09: Kontributor Tambah Jadwal ke Majelis Sendiri
```
Given Kontributor memiliki Majelis dengan id = 5
When Kontributor membuka form tambah Jadwal
Then dropdown Majelis hanya menampilkan Majelis dengan user_id = Auth::id()
When Kontributor memilih Majelis id = 5 dan mengisi data jadwal lalu submit
Then jadwal tersimpan dengan contribution_status = 'pending'
```

### AC-10: Kontributor Tidak Bisa Tambah Jadwal ke Majelis Orang Lain
```
Given Kontributor A dan Majelis milik Kontributor B (id = 10)
When Kontributor A mencoba POST ke /kontributor/saya/jadwal dengan assembly_id = 10
Then server mengembalikan validasi error "Majelis tidak valid"
And data tidak tersimpan
```

### AC-11: Leaderboard Publik
```
Given ada beberapa Kontributor dengan total_khidmah_points berbeda
When pengguna (tanpa login) mengunjungi /kontributor
Then tabel leaderboard menampilkan nama, badge, dan total poin
And diurutkan dari poin tertinggi ke terendah
```

---

## Testing and Verification

### Unit Test
- `KontributorRegistrationTest`: validasi syarat profil lengkap (semua kombinasi field kosong).
- `XpBadgeTest`: threshold badge untuk setiap perubahan XP.
- `ContributionOwnershipTest`: IDOR check — Kontributor tidak bisa akses/edit data milik orang lain.

### Feature Test
- `KontributorFlowTest`: full flow pendaftaran → submit → approve → XP bertambah → badge naik.
- `ModerasiTest`: approve dan reject dari admin, verifikasi notifikasi.
- `EditAfterRejectionTest`: edit dan resubmit submission yang ditolak.
- `JadwalOwnershipTest`: Kontributor hanya bisa tambah jadwal ke majelis sendiri.

### Manual Verification
1. Daftar sebagai Kontributor dengan profil tidak lengkap → verifikasi pesan error spesifik.
2. Submit Majelis baru → konfirmasi tidak muncul di `/majelis` publik.
3. Admin setujui → konfirmasi muncul di `/majelis` publik + XP bertambah di profil Kontributor.
4. Admin tolak dengan alasan → konfirmasi notifikasi diterima + alasan tampil di `/kontributor/saya`.
5. Edit dan kirim ulang → konfirmasi status kembali ke pending.
6. Ubah XP di admin settings → submit dan approve baru → konfirmasi XP baru yang terpakai.
7. Verifikasi leaderboard menampilkan maksimal 10 entri di `/kontributor` tanpa login.
8. Admin revoke approval → konfirmasi XP berkurang + badge turun jika melewati threshold + notifikasi terkirim + data tidak tampil publik.
9. Verifikasi pagination pada riwayat kontribusi di `/kontributor/saya`.

---

## Risks and Dependencies

| # | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| R-01 | Perubahan `hasOne` → `hasMany` pada Assembly memecah fitur yang sudah ada | Tinggi | Audit seluruh referensi `$user->assembly` sebelum implementasi |
| R-02 | Query publik tidak memfilter `contribution_status` dengan benar → data pending bocor ke publik | Tinggi | Tambahkan scope `publiclyVisible()` ke semua model yang terdampak dan gunakan secara konsisten |
| R-03 | Race condition saat admin menyetujui dua kali → XP double credit | Sedang | Gunakan database transaction + cek status sebelum update |
| R-04 | Nilai XP diubah admin saat ada banyak submission pending → ketidakkonsistenan ekspektasi Kontributor | Rendah | Dokumentasikan di UI admin bahwa perubahan XP hanya berlaku ke depan |
| R-05 | Email notifikasi gagal (SMTP down) → Kontributor tidak tahu status moderasi | Rendah | In-app notification sebagai fallback utama; log error email tanpa blocking |

### Dependensi
- `php artisan notifications:table` harus dijalankan sebelum implementasi notifikasi.
- Role `Kontributor` harus di-seed ke tabel Spatie sebelum fitur daftar bisa berfungsi.
- Nilai default `kontribusi_xp_settings` harus di-seed saat deployment.

---

## Open Questions

Semua open questions sudah dijawab dan diintegrasikan ke dalam spesifikasi ini. Tidak ada pertanyaan terbuka yang tersisa.

---

## Referensi File Proyek

| File | Relevansi |
|---|---|
| `app/Models/User.php` | Relasi assembly (hasOne → hasMany), total_khidmah_points, badge_title |
| `app/Models/Contribution.php` | Model kontribusi dengan morphTo |
| `app/Models/Assembly.php` | Model utama yang terdampak |
| `database/migrations/2026_06_01_211308_add_contributions_table.php` | Skema tabel contributions |
| `database/migrations/2026_06_01_211843_add_points_and_badge_field_to_users_table.php` | Kolom XP dan badge di users |
| `database/migrations/2025_12_23_000111_add_user_id_to_assemblies_table.php` | FK user_id di assemblies (nullable, no unique) |
| `database/migrations/2026_06_01_215310_add_user_and_moderation_field_to_events_table.php` | Pola moderasi yang sudah ada di events |
| `database/migrations/2026_06_01_224943_add_status_to_events_table.php` | Enum status moderasi di events |
| `app/Http/Controllers/User/ManagedMajelisController.php` | Referensi pola dual-controller untuk majelis |
| `app/Http/Controllers/User/ManageEventController.php` | Referensi pola moderasi event yang sudah ada |
| `app/Traits/HandlesImageUploads.php` | Trait upload gambar (dipakai form majelis dan guru) |
| `routes/web.php` | Registrasi route baru |
| `app/Http/Middleware/IsAdmin.php` | Middleware admin yang digunakan untuk route moderasi dan XP settings |
