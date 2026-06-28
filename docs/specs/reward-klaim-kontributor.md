# Reward Klaim Kontributor (Khadam Banua)

**Status Dokumen:** Implemented
**Tanggal:** 2026-06-27
**Author:** Muhammad Khaidir
**Spec terkait:** [`sistem-kontributor.md`](./sistem-kontributor.md) (sistem XP/badge induk) · [`atribusi-dan-ajakan-kontributor.md`](./atribusi-dan-ajakan-kontributor.md)

---

## Latar Belakang

Sistem Kontributor sudah berjalan: kontribusi yang disetujui admin menambah `users.total_khidmah_points` (XP) dan menaikkan `users.badge_title` secara otomatis melalui `User::updateBadge()` (`Jamaah Aktif` < 101 ≤ `Penuntut Ilmu` < 501 ≤ `Khadam Banua`). Saat ini badge tertinggi **`Khadam Banua` (≥ 501 XP)** hanya berupa gelar kehormatan tanpa insentif nyata.

Fitur ini menambahkan **reward uang tunai** (default **Rp 50.000**) yang dapat **diklaim** oleh kontributor saat mencapai threshold XP `Khadam Banua`. Reward **ditransfer manual oleh admin** melalui e-wallet; aplikasi hanya mengelola pengajuan, status, dan audit trail — **tidak** ada integrasi payment gateway.

---

## Tujuan

1. Memberi insentif konkret bagi kontributor yang mencapai gelar `Khadam Banua` untuk mendorong kontribusi berkualitas.
2. Menyediakan alur klaim mandiri (pull) bagi kontributor + alur pemrosesan & audit bagi admin.
3. Menjaga kontrol budget: reward **sekali seumur hidup** per kontributor, nominal/threshold/aktivasi **dapat dikonfigurasi admin**.

---

## Ringkasan Keputusan (hasil wawancara)

| Aspek | Keputusan |
|---|---|
| Frekuensi reward | **Sekali seumur hidup** per kontributor (dihitung dari klaim yang berhasil berstatus `paid`) |
| Pemicu klaim | **Manual (pull)** — kontributor menekan tombol "Klaim Reward" lalu mengisi data e-wallet |
| Kelayakan XP | **Snapshot saat klaim** — sah jika `total_khidmah_points ≥ min_xp` pada saat klaim **diajukan**; penurunan XP setelahnya tidak membatalkan klaim yang sudah masuk |
| Syarat role | Harus **role `Kontributor` aktif** saat mengajukan klaim |
| Nominal & threshold | **Configurable admin** (nominal, threshold XP, toggle aktif/nonaktif program) |
| Data klaim | E-wallet: **jenis**, **nomor**, **nama pemilik akun** |
| Status klaim | `pending` → `paid` / `rejected` |
| Saat `paid` | Catat **tanggal transfer**, **admin pemroses**, **catatan/keterangan admin**, **upload bukti transfer (gambar, disk privat)** |
| Re-klaim | Jika klaim **`rejected`**, kontributor **boleh mengajukan ulang** |
| Klaim aktif | **Hanya satu klaim `pending`** pada satu waktu |
| UI kontributor | Kartu/banner klaim di **dashboard `/kontributor/saya`** |
| UI admin | **Halaman admin baru** `/admin/reward-klaim` |
| Notifikasi | In-app + email untuk: **klaim diterima (pending)**, **sudah ditransfer (paid)**, **ditolak** |

---

## Non-Goals (Di Luar Scope)

- Tidak ada integrasi payment gateway / transfer otomatis. Transfer dilakukan manual oleh admin di luar aplikasi.
- Tidak mengubah sistem XP, badge, threshold badge, atau alur moderasi kontribusi (`sistem-kontributor.md`).
- Tidak menambah reward untuk badge selain `Khadam Banua` (tidak ada tier reward bertingkat).
- Tidak ada reward berulang/berkala (hanya sekali seumur hidup).
- Tidak menyimpan/menghitung saldo, ledger keuangan, pajak, atau laporan finansial.
- Tidak mengubah halaman profil publik kontributor (`/kontributor/profil/{username}`) — data e-wallet bersifat privat dan tidak ditampilkan publik.
- Tidak menambah metode transfer bank/manual lain selain e-wallet di scope ini.

---

## Current Behavior

- `User::updateBadge()` (`app/Models/User.php:152`) menetapkan `badge_title = 'Khadam Banua'` saat `total_khidmah_points >= 501`. Tidak ada efek samping lain.
- `KhidmahService` (`app/Services/KhidmahService.php`) menaikkan/menurunkan XP dan memanggil `updateBadge()` saat approve/revoke. Revoke dapat menurunkan XP (dan badge) di bawah threshold.
- Dashboard kontributor `/kontributor/saya` (`resources/views/pages/kontributor/saya.blade.php`) menampilkan ringkasan XP/badge & riwayat kontribusi. **Belum ada** elemen reward.
- Pengaturan admin XP ada di `/admin/pengaturan/xp-kontribusi` (`Admin\XpSettingController`, model `KontribusiXpSetting`). **Belum ada** pengaturan reward.
- **Belum ada** tabel klaim reward, model, controller, route, notifikasi, maupun halaman admin reward.

---

## Expected Behavior

### Kontributor
- Di `/kontributor/saya`, kontributor yang **memenuhi syarat** (lihat FR-02) melihat **kartu ajakan klaim** berisi nominal reward + tombol "Klaim Reward".
- Menekan tombol menampilkan form pengisian data e-wallet (jenis, nomor, nama pemilik). Setelah submit → klaim berstatus `pending`, tombol berganti menjadi indikator status "Klaim sedang diproses".
- Kontributor melihat **riwayat & status klaim**-nya (pending/paid/rejected, beserta alasan jika ditolak, dan bukti transfer jika sudah dibayar).
- Jika klaim `rejected`, kartu klaim kembali muncul agar kontributor dapat mengajukan ulang (memperbaiki data e-wallet).
- Jika kontributor sudah pernah memiliki klaim `paid`, kartu klaim **tidak muncul lagi** (digantikan status "Reward sudah diterima").

### Admin
- Di `/admin/reward-klaim`, admin melihat daftar klaim (default: tab/filter `pending`) berisi nama kontributor, XP saat klaim, nominal, dan data e-wallet.
- Admin dapat **menandai `paid`**: mengisi tanggal transfer, catatan, dan mengunggah bukti transfer → status `paid`, audit terisi, notifikasi terkirim.
- Admin dapat **menolak (`rejected`)** dengan alasan wajib → notifikasi terkirim.
- Di `/admin/pengaturan/reward` (atau section pada pengaturan yang sudah ada — lihat UI Impact), admin mengatur **nominal**, **threshold XP**, dan **toggle aktif/nonaktif** program.

---

## Functional Requirements

### FR-01: Pengaturan Reward (configurable)
- **FR-01a:** Tabel konfigurasi single-row `reward_settings`: `amount` (integer rupiah, default `50000`), `min_xp` (integer, default `501`), `is_active` (boolean, default `true`).
- **FR-01b:** Helper akses konfigurasi pada model `RewardSetting`, mis. `RewardSetting::current()` yang mengembalikan baris konfigurasi (membuat baris default bila belum ada — pola idempoten).
- **FR-01c:** Admin dapat mengubah ketiga nilai melalui form admin (validasi: `amount` integer ≥ 0; `min_xp` integer ≥ 1; `is_active` boolean).
- **FR-01d:** Jika `is_active = false`, **tidak ada** kontributor yang dapat mengajukan klaim baru (kartu klaim disembunyikan, endpoint store menolak). Klaim yang sudah masuk tetap dapat diproses admin.

### FR-02: Kelayakan Klaim (eligibility)
Kontributor **boleh mengajukan** klaim bila **semua** terpenuhi pada saat pengajuan:
- **FR-02a:** Program aktif (`reward_settings.is_active = true`).
- **FR-02b:** Memiliki role `Kontributor` aktif (Spatie `hasRole('Kontributor')`).
- **FR-02c:** `total_khidmah_points >= reward_settings.min_xp` (snapshot saat klaim).
- **FR-02d:** **Belum** memiliki klaim berstatus `paid` (sekali seumur hidup).
- **FR-02e:** **Tidak** sedang memiliki klaim berstatus `pending` (anti-duplikat — satu klaim aktif).

> Catatan: penurunan XP setelah klaim `pending` masuk **tidak** membatalkan klaim (FR-02c hanya dievaluasi saat pengajuan). Admin tetap memproses berdasarkan `xp_at_claim` yang ter-snapshot.

### FR-03: Pengajuan Klaim (kontributor)
- **FR-03a:** Form input: `ewallet_type` (pilihan: Dana, GoPay, OVO, ShopeePay), `ewallet_number` (string, wajib), `ewallet_holder_name` (string, wajib).
- **FR-03b:** Saat store, server **wajib** memvalidasi ulang seluruh syarat FR-02 (jangan hanya mengandalkan tampilan UI). Bila gagal → redirect dengan pesan error yang sesuai (tidak menyimpan).
- **FR-03c:** Saat tersimpan: buat record `reward_claims` dengan `status = 'pending'`, `amount = reward_settings.amount` (**snapshot**), `xp_at_claim = total_khidmah_points` (**snapshot**), data e-wallet, `user_id = Auth::id()`.
- **FR-03d:** Kirim notifikasi **"Klaim diterima"** (in-app + email) ke kontributor.
- **FR-03e:** Pengajuan dibungkus database transaction + (disarankan) pengecekan anti-duplikat untuk mencegah race condition double-pending.

### FR-04: Pemrosesan Klaim (admin) — Tandai `paid`
- **FR-04a:** Input admin: `transferred_at` (tanggal, wajib), `admin_note` (teks, opsional), `transfer_proof` (gambar, wajib).
- **FR-04b:** Hanya klaim berstatus `pending` yang dapat ditandai `paid` (cek status sebelum update; gunakan transaction).
- **FR-04c:** Bukti transfer diproses dengan `ImageUploadTrait::handleImageUpload()` (WebP, folder mis. `reward-proofs`) namun **disimpan di disk privat** (`local`), bukan `public`. Simpan path ke `transfer_proof_path`. File **tidak** dapat diakses via URL langsung; disajikan melalui route ber-otorisasi (FR-08).
- **FR-04d:** Set `status = 'paid'`, `transferred_at`, `admin_note`, `processed_by = Auth::id()`, `processed_at = now()`.
- **FR-04e:** Kirim notifikasi **"Reward sudah ditransfer"** (in-app + email) ke kontributor.

### FR-05: Pemrosesan Klaim (admin) — Tolak (`rejected`)
- **FR-05a:** Input admin: `rejection_reason` (teks, **wajib**).
- **FR-05b:** Hanya klaim `pending` yang dapat ditolak.
- **FR-05c:** Set `status = 'rejected'`, `rejection_reason`, `processed_by`, `processed_at = now()`.
- **FR-05d:** Kirim notifikasi **"Klaim ditolak"** (in-app + email) berisi alasan.
- **FR-05e:** Setelah `rejected`, kontributor kembali memenuhi FR-02e (tidak ada `pending`) sehingga **boleh mengajukan ulang** bila masih memenuhi FR-02 lainnya.

### FR-06: Tampilan Status di Dashboard Kontributor
- **FR-06a:** Render kartu klaim **hanya** bila kontributor memenuhi FR-02 (eligible & belum punya pending/paid).
- **FR-06b:** Bila ada klaim `pending` → tampilkan badge status "Sedang diproses" + ringkasan data e-wallet yang diajukan.
- **FR-06c:** Bila ada klaim `paid` → tampilkan "Reward sudah diterima" + tanggal transfer + tautan/preview bukti.
- **FR-06d:** Bila klaim terakhir `rejected` → tampilkan alasan penolakan + kartu klaim ulang.

### FR-07: Halaman Admin Klaim
- **FR-07a:** `/admin/reward-klaim` menampilkan daftar klaim dengan filter status (default `pending`), eager-load relasi `user` (hindari N+1), pagination.
- **FR-07b:** Tiap baris: nama kontributor, badge/XP saat klaim, nominal, jenis+nomor+nama e-wallet, tanggal pengajuan, aksi (Tandai Paid / Tolak).
- **FR-07c:** Semua HTML input admin (`admin_note`, `rejection_reason`) ditampilkan sebagai **plain text** (escaping Blade), bukan dirender HTML (selaras SC-05 spec induk).

### FR-08: Penyajian Bukti Transfer (disk privat)
- **FR-08a:** Bukti transfer disimpan di disk privat (`storage/app/private`, tidak di-`storage:link`).
- **FR-08b:** Route penyaji `GET /reward-klaim/{claim}/bukti` (name `reward-klaim.bukti`) men-stream file via `Storage::disk('local')->response(...)`.
- **FR-08c:** Otorisasi penyaji: hanya **admin** (`is_admin`) **atau** **pemilik klaim** (`claim.user_id == Auth::id()`) yang boleh mengakses; selain itu `403`/`404`.
- **FR-08d:** Bila `transfer_proof_path` `null` (klaim belum `paid`) → `404`.

---

## Authorization Rules

| Aksi | Syarat |
|---|---|
| Melihat kartu klaim di `/kontributor/saya` | Auth + verified + role `Kontributor` + memenuhi FR-02 |
| Mengajukan klaim (`POST`) | Auth + verified + role `Kontributor` + FR-02 (validasi server-side) |
| Melihat status/riwayat klaim sendiri | Auth + verified + role `Kontributor` (hanya klaim `user_id == Auth::id()`) |
| Melihat/men-stream bukti transfer (disk privat) | Auth + verified; **admin** (`is_admin`) **atau** pemilik klaim (`claim.user_id == Auth::id()`) — FR-08 |
| Mengelola/ memproses klaim | Role `Super Admin` (middleware `is_admin`) |
| Mengubah pengaturan reward | Role `Super Admin` (middleware `is_admin`) |

- **AZ-01:** Endpoint klaim kontributor berada di grup `auth:sanctum` + `verified` + `role:Kontributor` (konsisten dengan grup `/kontributor/saya/*` yang ada di `routes/web.php:168`).
- **AZ-02:** Endpoint admin berada di grup `prefix('admin')` + `is_admin` (konsisten `routes/web.php:198`).
- **AZ-03:** Data e-wallet bersifat privat — hanya pemilik klaim dan admin yang dapat melihatnya; tidak pernah ditampilkan di halaman publik.
- **AZ-04:** Bukti transfer disimpan di disk privat dan hanya dapat diakses lewat route penyaji ber-otorisasi (FR-08); tidak ada URL publik langsung.

---

## Data Model / Database Impact

### Tabel Baru: `reward_settings` (single-row config, migration + seed default)
```
id          bigint PK
amount      integer        default 50000   -- nominal rupiah reward
min_xp      integer        default 501     -- threshold XP minimal (selaras Khadam Banua)
is_active   boolean        default true    -- toggle program
timestamps
```

### Tabel Baru: `reward_claims` (migration)
```
id                   bigint PK
user_id              bigint unsigned, FK users(id) ON DELETE CASCADE
amount               integer                 -- snapshot reward_settings.amount saat klaim
xp_at_claim          integer                 -- snapshot total_khidmah_points saat klaim
ewallet_type         string                  -- Dana | GoPay | OVO | ShopeePay
ewallet_number       string
ewallet_holder_name  string
status               enum('pending','paid','rejected') default 'pending'  (index)
rejection_reason     text NULL
admin_note           text NULL               -- keterangan admin saat paid
transfer_proof_path  string NULL             -- path bukti transfer (disk privat 'local', webp)
transferred_at       timestamp NULL          -- tanggal transfer (diisi admin)
processed_by         bigint unsigned NULL, FK users(id) ON DELETE SET NULL
processed_at         timestamp NULL
timestamps
```
- `user_id` `ON DELETE CASCADE`: klaim tidak bermakna tanpa kontributor; trade-off audit dibahas di Risks (RR-04).
- Index pada `status` untuk filter admin; pertimbangkan index `(user_id, status)` untuk cek eligibility FR-02d/02e.

### Perubahan Model (bukan migration)
- Model baru `RewardSetting` (helper `current()`).
- Model baru `RewardClaim` (`belongsTo` `user`, `belongsTo` `processor` via `processed_by`; cast `transferred_at`/`processed_at` datetime; konstanta status).
- `User`: tambah relasi `rewardClaims()` → `hasMany(RewardClaim::class)`; (opsional) helper `eligibleForReward(): bool` & `hasPaidRewardClaim(): bool` untuk dipakai view/controller.

> Tidak ada perubahan pada tabel `users`, `contributions`, atau tabel kontribusi lain. Threshold reward **independen** dari `badge_title` (memakai `min_xp` numerik), sehingga tidak bergantung pada string badge.

---

## UI / Route Impact

### Route Baru
```php
// Auth + verified + role:Kontributor  (dalam grup yang sudah ada di routes/web.php:168)
GET   /kontributor/saya/reward            → User\RewardClaimController@index   name: kontributor.reward.index   // opsional: halaman riwayat
POST  /kontributor/saya/reward            → User\RewardClaimController@store   name: kontributor.reward.store

// Penyaji bukti transfer (disk privat) — auth + verified; otorisasi admin ATAU pemilik klaim (FR-08)
GET   /reward-klaim/{claim}/bukti         → RewardProofController@show          name: reward-klaim.bukti

// Admin  (grup prefix admin + is_admin, routes/web.php:198)
GET   /admin/reward-klaim                 → Admin\RewardClaimController@index   name: admin.reward-klaim.index
PUT   /admin/reward-klaim/{claim}/paid    → Admin\RewardClaimController@markPaid name: admin.reward-klaim.paid
PUT   /admin/reward-klaim/{claim}/reject  → Admin\RewardClaimController@reject   name: admin.reward-klaim.reject
GET   /admin/pengaturan/reward            → Admin\RewardSettingController@index  name: admin.reward-settings.index
PUT   /admin/pengaturan/reward            → Admin\RewardSettingController@update name: admin.reward-settings.update
```
> **Keputusan:** Pengaturan reward ditempatkan sebagai **halaman terpisah** `/admin/pengaturan/reward` (bukan section di pengaturan XP).

### Controller Baru
```
app/Http/Controllers/User/RewardClaimController.php     -- index (opsional), store
app/Http/Controllers/RewardProofController.php          -- show (penyaji bukti privat, otorisasi admin atau pemilik)
app/Http/Controllers/Admin/RewardClaimController.php    -- index, markPaid, reject
app/Http/Controllers/Admin/RewardSettingController.php  -- index, update
```

### Service (disarankan, agar logika tidak menumpuk di controller)
```
app/Services/RewardClaimService.php   -- submit(User), markPaid(RewardClaim, data), reject(RewardClaim, reason)
```
> Mengikuti pola `KhidmahService` yang sudah ada untuk operasi transaksional + notifikasi.

### Notifikasi Baru (channel: database + mail, pola sama dengan `BadgeNaik`)
```
app/Notifications/RewardKlaimDiterima.php   -- konfirmasi pending (nominal)
app/Notifications/RewardKlaimDibayar.php    -- paid (nominal + tanggal transfer)
app/Notifications/RewardKlaimDitolak.php    -- rejected (alasan)
```

### View Baru / Diubah
```
resources/views/pages/kontributor/saya.blade.php             -- (diubah) kartu klaim + status reward
resources/views/pages/admin/reward-klaim/index.blade.php     -- (baru) daftar & proses klaim
resources/views/pages/admin/reward/index.blade.php           -- (baru/atau section) pengaturan reward
```

### Trait / Util yang Dipakai Ulang (Architecture Rules — tanpa duplikasi)
- `App\Traits\ImageUploadTrait` untuk bukti transfer. **Perubahan kecil backward-compatible:** tambahkan parameter opsional `string $disk = 'public'` pada `handleImageUpload()` (dan `deleteImage()`) lalu ganti `Storage::disk('public')` → `Storage::disk($disk)`. Default `'public'` menjaga seluruh pemakaian lama (`LibraryController`) tetap berfungsi; reward memanggil dengan `$disk = 'local'`. Hindari membuat trait/util upload baru (duplikasi).
- Pola notifikasi `database`+`mail` mengikuti `app/Notifications/BadgeNaik.php`.
- Pola settings mengikuti `KontribusiXpSetting` / `Admin\XpSettingController`.

---

## Validasi Input

| Field | Rule |
|---|---|
| `ewallet_type` | required, in: Dana,GoPay,OVO,ShopeePay |
| `ewallet_number` | required, string, max:30 |
| `ewallet_holder_name` | required, string, max:100 |
| Admin `transferred_at` | required, date |
| Admin `transfer_proof` | required, image, max:2048 (KB), mimes webp/jpg/jpeg/png |
| Admin `admin_note` | nullable, string, max:500 |
| Admin `rejection_reason` | required (saat tolak), string, max:500 |
| Pengaturan `amount` | required, integer, min:0, max:100000000 |
| Pengaturan `min_xp` | required, integer, min:1 |
| Pengaturan `is_active` | required, boolean |

---

## Edge Cases

- **RC-01:** Program nonaktif (`is_active=false`) saat kontributor membuka dashboard → kartu klaim tidak muncul; POST store ditolak dengan pesan "Program reward sedang tidak aktif".
- **RC-02:** Kontributor sudah punya klaim `paid` → kartu klaim tidak muncul; POST store ditolak ("Reward sudah pernah diterima").
- **RC-03:** Kontributor sudah punya klaim `pending` → kartu klaim diganti status "Sedang diproses"; POST store ditolak ("Sudah ada klaim yang sedang diproses").
- **RC-04:** XP turun di bawah `min_xp` (akibat revoke) **setelah** klaim `pending` masuk → klaim **tetap valid** & dapat diproses (snapshot `xp_at_claim`).
- **RC-05:** XP turun di bawah `min_xp` **sebelum** mengajukan → kartu klaim tidak muncul; POST store ditolak ("XP belum mencukupi").
- **RC-06:** Role `Kontributor` dicabut sebelum mengajukan → tidak dapat mengajukan (FR-02b). Bila role dicabut **setelah** klaim `pending` masuk → admin tetap dapat memproses klaim yang sudah ada.
- **RC-07:** Admin mengubah `amount` setelah ada klaim `pending` → klaim lama tetap memakai `amount` yang ter-snapshot saat klaim (tidak retroaktif).
- **RC-08:** Double-submit form (race) → transaction + cek anti-duplikat memastikan hanya satu `pending` terbuat.
- **RC-09:** Admin menandai `paid`/`reject` pada klaim yang sudah bukan `pending` (race / double click) → operasi di-skip (cek status di dalam transaction), tidak terjadi notifikasi/efek ganda.
- **RC-10:** Klaim `rejected` lalu kontributor memperbaiki data e-wallet & mengajukan ulang → klaim baru `pending` terbuat (klaim lama tetap tersimpan sebagai `rejected`, audit).
- **RC-11:** Akun kontributor dihapus → record klaim ikut terhapus (CASCADE). Bila audit historis diperlukan, lihat RR-04.
- **RC-12:** Upload bukti gagal/format salah → validasi gagal, status klaim tidak berubah, tidak ada notifikasi terkirim.

---

## Compatibility Considerations

- **CC-01:** Threshold reward memakai kolom numerik `min_xp`, **independen** dari logika `badge_title` di `User::updateBadge()`. Mengubah `min_xp` tidak memengaruhi badge, dan sebaliknya. Default `501` menyelaraskan reward dengan badge `Khadam Banua`.
- **CC-02:** Tabel & route baru sepenuhnya aditif; tidak mengubah skema/relasi `users`, `contributions`, maupun alur `KhidmahService`. Tidak ada breaking change.
- **CC-03:** Menggunakan tabel `notifications` yang sudah aktif (dipakai `KontribusiDisetujui`/`BadgeNaik`) — tidak perlu migration notifikasi baru.
- **CC-04:** Bukti transfer disimpan di disk **privat** (`local`, `storage/app/private`), **tidak** memerlukan `storage:link` dan tidak dapat diakses publik. Penambahan parameter `$disk` pada `ImageUploadTrait` bersifat backward-compatible (default `'public'`), sehingga pemakaian lama (`LibraryController`) tidak terpengaruh.

---

## Acceptance Criteria

### AC-01: Kartu Klaim Muncul untuk Kontributor yang Memenuhi Syarat
```
Given kontributor aktif dengan total_khidmah_points = 520, min_xp = 501, program aktif, belum punya klaim apa pun
When ia membuka /kontributor/saya
Then tampil kartu "Klaim Reward" beserta nominal (Rp 50.000) dan tombol klaim
```

### AC-02: Pengajuan Klaim Berhasil
```
Given kontributor memenuhi syarat
When ia mengisi jenis e-wallet, nomor, dan nama pemilik lalu submit
Then terbuat record reward_claims status 'pending' dengan amount=50000 dan xp_at_claim=520
And ia menerima notifikasi in-app + email "Klaim diterima"
And kartu klaim berganti menjadi status "Sedang diproses"
```

### AC-03: Eligibility Ditegakkan Server-Side
```
Given kontributor dengan total_khidmah_points = 300 (di bawah min_xp)
When ia mem-POST pengajuan klaim secara langsung
Then server menolak (tidak menyimpan) dengan pesan "XP belum mencukupi"
```

### AC-04: Anti-Duplikat Pending
```
Given kontributor sudah memiliki satu klaim berstatus 'pending'
When ia mencoba mengajukan klaim lagi
Then server menolak dengan pesan "Sudah ada klaim yang sedang diproses"
And tidak ada record klaim kedua terbuat
```

### AC-05: Sekali Seumur Hidup
```
Given kontributor sudah memiliki klaim berstatus 'paid'
When ia membuka /kontributor/saya
Then kartu klaim tidak muncul; tampil "Reward sudah diterima"
And POST store ditolak dengan pesan "Reward sudah pernah diterima"
```

### AC-06: Admin Menandai Paid
```
Given ada klaim berstatus 'pending'
When admin mengisi tanggal transfer, catatan, mengunggah bukti, lalu submit "Tandai Paid"
Then status menjadi 'paid', transferred_at/processed_by/processed_at/transfer_proof_path terisi
And kontributor menerima notifikasi in-app + email "Reward sudah ditransfer"
```

### AC-07: Admin Menolak Klaim
```
Given ada klaim berstatus 'pending'
When admin menolak dengan alasan "Nomor e-wallet tidak valid"
Then status menjadi 'rejected', rejection_reason tersimpan, processed_by/processed_at terisi
And kontributor menerima notifikasi berisi alasan
```

### AC-08: Re-klaim Setelah Ditolak
```
Given kontributor memiliki klaim 'rejected' dan masih memenuhi FR-02 lainnya
When ia membuka /kontributor/saya
Then kartu klaim muncul kembali (dengan alasan penolakan ditampilkan)
And ia dapat mengajukan klaim baru yang valid
```

### AC-09: Snapshot Tahan terhadap Penurunan XP
```
Given kontributor mengajukan klaim saat XP = 510 (klaim 'pending', xp_at_claim=510)
And kemudian admin merevoke kontribusi sehingga XP turun ke 480
When admin membuka /admin/reward-klaim
Then klaim tetap tampil dan dapat ditandai 'paid' (tidak otomatis batal)
```

### AC-10: Pengaturan Admin Berlaku ke Depan
```
Given admin mengubah amount menjadi 75000 dan min_xp menjadi 600
When kontributor baru yang memenuhi syarat mengajukan klaim
Then klaim baru memakai amount=75000
And kontributor dengan XP 550 tidak lagi memenuhi syarat (min_xp 600)
And klaim 'pending' yang dibuat sebelum perubahan tetap memakai amount lamanya
```

### AC-11: Program Nonaktif
```
Given admin men-set is_active = false
When kontributor yang memenuhi syarat XP membuka /kontributor/saya
Then kartu klaim tidak muncul
And POST store ditolak dengan pesan program tidak aktif
And klaim 'pending' yang sudah ada tetap dapat diproses admin
```

---

## Testing and Verification

### Unit / Feature Test (PHPUnit, SQLite in-memory)
- `RewardEligibilityTest`: matriks FR-02 (program aktif/nonaktif, role ada/dicabut, XP di atas/bawah `min_xp`, sudah/belum `paid`, ada/tidak `pending`).
- `RewardClaimSubmitTest`: store membuat record `pending` dengan snapshot `amount`/`xp_at_claim`; menolak duplikat pending; menolak saat sudah `paid`; mengirim notifikasi `RewardKlaimDiterima`.
- `RewardClaimAdminTest`: `markPaid` mengisi audit + bukti + notifikasi `RewardKlaimDibayar`; `reject` mengisi alasan + notifikasi `RewardKlaimDitolak`; operasi pada non-`pending` di-skip (RC-09).
- `RewardSettingTest`: `current()` membuat baris default idempoten; update memvalidasi rule FR-01c.
- `RewardSnapshotTest`: penurunan XP setelah `pending` tidak membatalkan klaim (RC-04 / AC-09).
- `RewardProofTest`: penyaji bukti (FR-08) — admin & pemilik klaim `200`; user lain `403`/`404`; klaim tanpa bukti `404`.
- Gunakan `Notification::fake()` & `Storage::fake('local')` untuk isolasi.

> Catatan lingkungan: test memakai SQLite in-memory. Lihat memori proyek `php-cli-no-sqlite.md` dan `broken-biographies-migration.md` untuk menjalankan suite di mesin ini.

### Manual Verification (End-to-End)
1. Set XP kontributor ke ≥ `min_xp` → buka `/kontributor/saya` → kartu klaim tampil dengan nominal benar.
2. Ajukan klaim → cek record `reward_claims` (`pending`, `amount`/`xp_at_claim` ter-snapshot) → cek notifikasi in-app + email "Klaim diterima" → kartu berubah jadi "Sedang diproses".
3. Coba ajukan klaim kedua → ditolak (anti-duplikat).
4. Login admin → `/admin/reward-klaim` → tandai `paid` (isi tanggal, catatan, unggah bukti) → cek status `paid`, file bukti tersimpan webp di disk privat (`storage/app/private/reward-proofs`, tidak dapat diakses via URL publik), audit terisi, notifikasi "ditransfer" diterima kontributor.
9. Coba akses URL bukti transfer sebagai user lain (bukan pemilik/admin) → `403`/`404`; sebagai pemilik klaim atau admin → gambar tampil.
5. Buka `/kontributor/saya` lagi → "Reward sudah diterima"; coba POST store langsung → ditolak (sekali seumur hidup).
6. Pada kontributor lain: ajukan klaim → admin tolak dengan alasan → kontributor melihat alasan & kartu klaim ulang → ajukan ulang berhasil.
7. Ubah pengaturan: `amount` & `min_xp` & matikan `is_active` → verifikasi kartu klaim hilang dan klaim baru memakai nilai baru saat diaktifkan kembali.
8. Revoke kontribusi kontributor yang punya klaim `pending` → verifikasi klaim tetap dapat diproses (snapshot).

---

## Risks and Trade-offs

| # | Risiko / Trade-off | Dampak | Mitigasi |
|---|---|---|---|
| RR-01 | Race condition double-pending atau double-process | Sedang | Database transaction + cek status/eligibility di dalam transaction (FR-03e, RC-08, RC-09) |
| RR-02 | Eligibility hanya divalidasi di UI → bypass via POST langsung | Tinggi | Validasi ulang seluruh FR-02 server-side pada store (FR-03b, AC-03) |
| RR-03 | Snapshot XP vs XP terkini membingungkan admin (klaim valid padahal XP kini < threshold) | Rendah | Tampilkan `xp_at_claim` di UI admin + dokumentasikan kebijakan snapshot |
| RR-04 | `ON DELETE CASCADE` menghapus jejak klaim saat akun dihapus → kehilangan audit finansial | Sedang | Disepakati cascade untuk scope ini; bila audit jangka panjang dibutuhkan, pertimbangkan `SET NULL` + snapshot nama/email di kolom terpisah (pekerjaan lanjutan) |
| RR-05 | Penyalahgunaan: kontributor mengumpulkan XP lalu klaim, XP via konten berkualitas rendah | Sedang | Moderasi admin pada kontribusi (sistem induk) + reward sekali seumur hidup membatasi eksposur |
| RR-06 | Data e-wallet (PII) tersimpan plaintext | Sedang | Akses dibatasi (pemilik + admin), tidak ditampilkan publik (AZ-03); enkripsi kolom dapat ditambahkan bila kebijakan privasi menuntut (di luar scope) |
| RR-07 | Bukti transfer (gambar) berisi info sensitif | Rendah | Disimpan di **disk privat** (`local`), disajikan via route ber-otorisasi admin/pemilik (FR-08, AZ-04); tidak ada URL publik |

### Dependensi
- Sistem Kontributor & XP/badge (`sistem-kontributor.md`) sudah terimplementasi (role `Kontributor`, `total_khidmah_points`, `KhidmahService`).
- Tabel `notifications` aktif; mailer terkonfigurasi.
- `storage:link` aktif untuk akses bukti transfer.

---

## Referensi File Proyek

| File | Relevansi |
|---|---|
| `app/Models/User.php` | `total_khidmah_points`, `badge_title`, `updateBadge()` (threshold 501); tambah relasi `rewardClaims()` + helper eligibility |
| `app/Services/KhidmahService.php` | Pola service transaksional + notifikasi (referensi untuk `RewardClaimService`); sumber penurunan XP (revoke) yang relevan ke snapshot |
| `app/Models/KontribusiXpSetting.php` | Pola model settings (`pointsFor`) → referensi `RewardSetting::current()` |
| `app/Http/Controllers/Admin/XpSettingController.php` | Pola controller pengaturan admin → referensi `RewardSettingController` |
| `app/Traits/ImageUploadTrait.php` | Upload bukti transfer; tambah param `$disk` opsional (default `'public'`) → reward pakai `'local'` (privat) |
| `app/Notifications/BadgeNaik.php` | Pola notifikasi `database`+`mail` → referensi 3 notifikasi reward |
| `resources/views/pages/kontributor/saya.blade.php` | Dashboard kontributor → tempat kartu/status klaim |
| `routes/web.php` (baris 168, 198) | Grup route `role:Kontributor` & grup admin `is_admin` → registrasi route baru |
| `app/Http/Middleware/IsAdmin.php` | Middleware admin (`Super Admin`) untuk route admin reward |
| `docs/specs/sistem-kontributor.md` | Spec induk XP/badge/moderasi |

---

## Rencana Implementasi Bertahap

Setiap fase berdiri sendiri, dapat di-commit terpisah, dan diakhiri **gerbang verifikasi** sebelum lanjut. Test ditulis berbarengan dengan fase yang relevan (bukan ditumpuk di akhir).

### Fase 0 — Persiapan & Kerangka (tanpa perilaku)
**Tujuan:** fondasi data tanpa mengubah UI/alur apa pun.
- Migration `reward_settings` (FR-01a) + seeder/`current()` default (`amount=50000`, `min_xp=501`, `is_active=true`).
- Migration `reward_claims` (data model) — kolom, enum `status`, FK, index `(user_id, status)`.
- Model `RewardSetting` (`current()`), `RewardClaim` (relasi `user`/`processor`, cast tanggal, konstanta status).
- `User`: relasi `rewardClaims()` + helper `hasPaidRewardClaim()` & `eligibleForReward()` (membungkus FR-02b–02e; XP/aktif dievaluasi dengan `RewardSetting::current()`).

**Gerbang:** `php artisan migrate` jalan; `RewardSettingTest` (default idempoten) hijau; tidak ada perubahan UI.

---

### Fase 1 — Pengaturan Reward (Admin)
**Tujuan:** admin dapat mengatur nominal/threshold/aktivasi lebih dulu, karena seluruh eligibility bergantung padanya.
- `Admin\RewardSettingController@index/update` (pola `XpSettingController`; validasi FR-01c).
- Route `GET/PUT /admin/pengaturan/reward` di grup admin `is_admin`.
- View `pages/admin/reward/index.blade.php` + entri navigasi admin.

**Gerbang:** ubah nominal/threshold/toggle dari UI → tersimpan; validasi menolak input invalid. (AC-10/AC-11 bagian pengaturan.)

---

### Fase 2 — Trait Disk Privat + Notifikasi
**Tujuan:** siapkan dependensi lintas-fase (upload privat & notifikasi) sekali saja.
- `ImageUploadTrait`: tambah param `$disk = 'public'` pada `handleImageUpload()` & `deleteImage()` (backward-compatible — pemakaian `LibraryController` tak berubah).
- 3 notifikasi `database`+`mail` (pola `BadgeNaik`): `RewardKlaimDiterima`, `RewardKlaimDibayar`, `RewardKlaimDitolak`.

**Gerbang:** test trait — `$disk='local'` menulis ke disk privat, default tetap `public`; smoke test notifikasi via `Notification::fake()`.

---

### Fase 3 — Service Klaim (logika inti)
**Tujuan:** semua aturan transaksional terpusat, mudah diuji unit (pola `KhidmahService`).
- `RewardClaimService`:
  - `submit(User $user): RewardClaim` — validasi ulang FR-02 (lempar exception/return error bila gagal), snapshot `amount`+`xp_at_claim`, buat `pending`, kirim `RewardKlaimDiterima`, dibungkus `DB::transaction` + anti-duplikat (FR-03, RC-08).
  - `markPaid(RewardClaim, array $data)` — guard status `pending` (RC-09), simpan bukti via trait disk `local`, isi audit, kirim `RewardKlaimDibayar` (FR-04).
  - `reject(RewardClaim, string $reason)` — guard `pending`, isi alasan/audit, kirim `RewardKlaimDitolak` (FR-05).

**Gerbang:** `RewardEligibilityTest`, `RewardClaimSubmitTest`, `RewardSnapshotTest` (AC-03/04/05/09, RC-04/08/09) hijau — sebelum UI apa pun menyentuh logika ini.

---

### Fase 4 — Alur Kontributor (UI klaim)
**Tujuan:** kontributor mengajukan klaim & melihat status.
- `User\RewardClaimController@store` (delegasi ke service) + `@index` opsional; route di grup `role:Kontributor` (`web.php:168`).
- Ubah `pages/kontributor/saya.blade.php`: kartu klaim (eligible), status `pending`/`paid`/`rejected` (FR-06).

**Gerbang:** E2E langkah 1–3 & 6 (ajukan, anti-duplikat, lihat status, re-klaim setelah ditolak); `RewardClaimSubmitTest` lengkap.

---

### Fase 5 — Alur Admin (proses klaim)
**Tujuan:** admin memproses klaim pending.
- `Admin\RewardClaimController@index/markPaid/reject` (delegasi ke service; eager-load `user`, pagination, filter status).
- Route `GET /admin/reward-klaim`, `PUT .../paid`, `PUT .../reject`.
- View `pages/admin/reward-klaim/index.blade.php` (escaping plain text untuk `admin_note`/`rejection_reason`, FR-07c) + navigasi admin.

**Gerbang:** E2E langkah 4 (paid + bukti tersimpan privat) & 5/7; `RewardClaimAdminTest` hijau.

---

### Fase 6 — Penyaji Bukti Privat
**Tujuan:** bukti transfer hanya dapat diakses pemilik/admin.
- `RewardProofController@show` — stream `Storage::disk('local')->response(...)`, otorisasi admin **atau** pemilik (FR-08, AZ-04); `404` bila tanpa bukti.
- Route `GET /reward-klaim/{claim}/bukti` (auth+verified). Tautkan dari view kontributor (status `paid`) & view admin.

**Gerbang:** `RewardProofTest` (admin/pemilik `200`; lainnya `403/404`; tanpa bukti `404`); E2E langkah 9.

---

### Fase 7 — Pengerasan & Penyelesaian
- Jalankan seluruh suite reward + suite kontributor terdampak; `./vendor/bin/pint`.
- Tinjau `git diff`: pastikan tidak ada perubahan di luar scope, tidak ada debug/credential.
- Perbarui dokumentasi bila ada perilaku publik yang bergeser; set **Status Dokumen → Implemented**.

**Definition of Done:** seluruh AC-01..AC-11 terpenuhi, test/lint hijau, diff bersih (lihat *Definition of Done* CLAUDE.md).

### Ringkasan Urutan & Dependensi
```
F0 data → F1 settings → F2 trait+notif → F3 service → F4 UI kontributor → F5 UI admin → F6 penyaji bukti → F7 hardening
                              (F2 prasyarat F3/F5)         (F3 prasyarat F4 & F5)        (F5 mengisi bukti → F6 menyajikan)
```
