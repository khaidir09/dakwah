# Atribusi Kontributor & Ajakan Menjadi Kontributor

**Status Dokumen:** Implemented
**Tanggal:** 2026-06-27
**Author:** Muhammad Khaidir
**Spec terkait:** [`sistem-kontributor.md`](./sistem-kontributor.md) — fitur ini adalah lapisan *presentation* di atas Sistem Kontributor yang sudah ada.

---

## Latar Belakang

Sistem Kontributor sudah berjalan: pengguna ber-role `Kontributor` dapat menambahkan data (Majelis, Guru, Amalan/Wirid, Acara, Jadwal) yang melewati moderasi admin sebelum tayang. Namun di halaman publik, data yang sudah tayang **tidak menampilkan siapa kontributornya**, sehingga kontribusi tidak terlihat/diapresiasi, dan pengunjung tidak terdorong untuk ikut menjadi kontributor.

Fitur ini menambahkan dua hal yang saling melengkapi:

1. **Blok atribusi kontributor** pada halaman detail data publik — menampilkan siapa yang berkontribusi (atau fallback "Admin Syaikhuna" untuk data buatan admin), tertaut ke halaman profil publik kontributor.
2. **Tombol ajakan "Jadi Kontributor"** pada halaman publik tersebut — mengarah ke `kontributor.index` (halaman program + leaderboard).

Sebagai pendukung atribusi, fitur ini juga menambahkan **halaman profil publik kontributor** (baru) yang menampilkan identitas, badge, statistik, daftar kontribusi, dan tanggal bergabung.

---

## Tujuan

1. Menampilkan atribusi kontributor pada halaman detail Majelis, Guru, Manaqib Ulama, serta pada kartu Amalan.
2. Menyediakan halaman profil publik kontributor yang dirujuk oleh atribusi.
3. Mendorong akuisisi kontributor baru melalui tombol ajakan pada halaman publik.

---

## Non-Goals (Di Luar Scope)

- Tidak mengubah sistem poin khidmah, badge, threshold, atau alur moderasi (sudah didefinisikan di `sistem-kontributor.md`).
- Tidak menambahkan atribusi/ajakan ke entitas selain Majelis, Guru, Manaqib, dan Amalan (mis. Acara, Video, Pustaka, Tulisan, Artikel Ilmiah, Jadwal).
- **Tidak** membuat halaman detail Amalan baru — atribusi Amalan ditempatkan pada kartu di daftar amalan.
- Tidak mengubah area admin.
- Tidak menambahkan filter periode, statistik mendalam, atau fitur sosial (follow kontributor, dsb.) pada halaman profil.
- Tidak mengubah relasi/kolom kontribusi yang sudah ada (`contributor_user_id`, `user_id`, `contribution_status`).

---

## Definisi & Pemetaan Entitas

| Istilah Fitur | Model | Kolom Pembuat | Halaman Detail | Identifier Route |
|---|---|---|---|---|
| Majelis | `Assembly` | `user_id` (relasi `contributor()` / `user()`) | `pages/user/majelis/detail` | `majelis-detail` (`/majelis/{id}`) |
| Guru | `Teacher` | `contributor_user_id` (relasi `contributor()`) | `pages/user/guru/detail` | `guru-detail` (`/guru/{teacher}` — slug) |
| Manaqib Ulama | `Teacher` dengan `wafat_hijriah_day != null` | `contributor_user_id` | `pages/user/biography/detail` | `manaqib-detail` (`/manaqib/{slug}`) |
| Amalan | `Wirid` | `contributor_user_id` (relasi `contributor()`) | *(tidak ada — kartu di daftar)* | `wirid-list` (`/wirid`) |

> Catatan: Guru dan Manaqib adalah model yang sama (`Teacher`) dengan dua route/view berbeda. Atribusi diterapkan pada **kedua** view.

---

## Current Behavior

- Halaman detail Majelis (`MajelisController@detail`), Guru (`GuruController@detail`), Manaqib (`BiographyController@detail`), dan daftar Amalan (`WiridController@list` → Livewire `ListWirid` / view `list-wirid.blade.php`) **tidak menampilkan informasi pembuat** sama sekali.
- `KontributorController@index` menampilkan leaderboard (top 10) di `/kontributor`; `@daftar` memberikan role `Kontributor` via `assignRole` setelah profil lengkap.
- `User` memakai trait Jetstream `HasProfilePhoto` → accessor `profile_photo_url` selalu mengembalikan URL (foto asli atau fallback inisial). Kolom `badge_title` dan `total_khidmah_points` sudah ada.
- **Belum ada** kolom `username` maupun penanda waktu bergabung sebagai kontributor di tabel `users`.
- **Belum ada** halaman profil publik kontributor (hanya leaderboard).
- Tidak ada tombol ajakan menjadi kontributor di halaman detail publik.

---

## Expected Behavior

### Atribusi
- Setiap halaman detail Majelis, Guru, Manaqib, dan setiap kartu Amalan menampilkan **blok atribusi** berisi: avatar (`profile_photo_url`), nama, dan `badge_title` kontributor.
- Blok atribusi **selalu tampil**:
  - Jika ada pembuat ber-data kontributor → tampilkan identitasnya, **tertaut** ke halaman profil publik kontributor.
  - Jika tidak ada pembuat (data dibuat admin / kolom pembuat `NULL`) → tampilkan fallback **"Admin Syaikhuna"** (avatar default/logo, teks, **tanpa tautan**).
- Jika pembuat ada namun role `Kontributor`-nya telah dicabut → atribusi **tetap tampil dan tetap tertaut** ke profil (lihat ATB-04).

### Profil Publik Kontributor (baru)
- Dapat diakses publik di `/kontributor/profil/{username}`.
- Menampilkan: identitas & badge (nama, avatar, `badge_title`, `total_khidmah_points`, asal daerah), statistik ringkas (jumlah per jenis kontribusi), daftar kontribusi yang sudah tayang publik, dan tanggal bergabung (`kontributor_since`).
- Profil dapat diakses selama user memiliki `username` + `kontributor_since` (pernah/masih menjadi kontributor), meskipun role saat ini telah dicabut.

### Ajakan Menjadi Kontributor
- Tombol/CTA "Jadi Kontributor" tampil pada: detail Majelis, detail Guru, detail Manaqib, dan halaman daftar Amalan (`wirid-list`).
- CTA mengarah ke `route('kontributor.index')`.
- CTA **disembunyikan** untuk pengguna yang sudah ber-role `Kontributor`. Tampil untuk tamu (belum login) dan pengguna terautentikasi non-kontributor.

---

## Functional Requirements

### FR-A: Penentuan & Resolusi Kontributor
- **FR-A1:** Untuk setiap entitas, "kontributor" diambil dari relasi pembuat: `Assembly` → `user_id`; `Teacher` → `contributor_user_id`; `Wirid` → `contributor_user_id`.
- **FR-A2:** Jika relasi pembuat `null`, entitas dianggap dibuat admin → render fallback "Admin Syaikhuna".
- **FR-A3:** Resolusi kontributor harus eager-load relasi pembuat di controller/komponen untuk menghindari N+1 (khususnya pada daftar amalan).

### FR-B: Blok Atribusi (komponen reusable)
- **FR-B1:** Sediakan satu komponen Blade reusable, mis. `<x-kontributor.attribution :user="$creator" />`, yang menerima `?User $user` dan merender:
  - Jika `$user` ada: avatar (`profile_photo_url`), nama, `badge_title` (jika `badge_title` `null`, sembunyikan baris badge), dibungkus tautan ke `route('kontributor.profil', $user->username)`.
  - Jika `$user` `null`: avatar default/logo + teks "Admin Syaikhuna", tanpa tautan.
- **FR-B2:** Komponen menangani kasus `username` `null` pada user lama yang belum di-backfill: jika `username` kosong, tampilkan identitas **tanpa tautan** (defensif; backfill seharusnya mengisi seluruh kontributor — lihat FR-E).
- **FR-B3:** Nama kontributor di-escape sebagai teks biasa (bukan HTML).

### FR-C: Penempatan Atribusi
- **FR-C1:** Pasang komponen atribusi pada `pages/user/majelis/detail.blade.php` (kontributor = `$assembly->contributor`).
- **FR-C2:** Pasang pada `pages/user/guru/detail.blade.php` (kontributor = `$teacher->contributor`).
- **FR-C3:** Pasang pada `pages/user/biography/detail.blade.php` (kontributor = `$biography->contributor`).
- **FR-C4:** Pasang pada footer tiap kartu di `resources/views/livewire/list-wirid.blade.php` (kontributor = `$wirid->contributor`), dalam ukuran ringkas agar selaras dengan layout kartu.

### FR-D: Halaman Profil Publik Kontributor
- **FR-D1:** Route publik baru `GET /kontributor/profil/{username}` → `KontributorController@profil`, name `kontributor.profil`.
- **FR-D2:** Resolusi by `username`; jika user tidak ditemukan **atau** `kontributor_since` `null` → `404`.
- **FR-D3:** Menampilkan:
  - **Identitas & badge:** nama, avatar, `badge_title`, `total_khidmah_points`, asal daerah (relasi `city`/`province` bila ada).
  - **Statistik ringkas:** jumlah kontribusi tayang publik per jenis (Majelis, Guru, Amalan).
  - **Daftar kontribusi:** item Majelis/Guru/Amalan milik user yang berstatus tayang publik (`contribution_status IS NULL OR = 'approved'`), masing-masing tertaut ke halaman detailnya.
  - **Tanggal bergabung:** `kontributor_since` (format tanggal Indonesia).
- **FR-D4:** Hanya data yang tayang publik yang ditampilkan; submission `pending`/`rejected` tidak ditampilkan di profil publik.
- **FR-D5:** Query daftar/statistik harus efisien (hindari N+1; gunakan `withCount`/agregasi yang sesuai).

### FR-E: Data Model — `username` & `kontributor_since`
- **FR-E1:** Tambah kolom `users.username` (`string`, `nullable`, **unique**).
- **FR-E2:** Tambah kolom `users.kontributor_since` (`timestamp`, `nullable`).
- **FR-E3:** Generator username otomatis dari `name` + suffix unik (pola `Str::slug(name)` + suffix bila bentrok), mengikuti pola unik yang sudah ada di `Teacher` (lihat metode pembentuk slug unik di `Teacher`). Ditempatkan sebagai metode pada `User` (mis. `generateUniqueUsername()`).
- **FR-E4:** Saat user diberikan role `Kontributor` di `KontributorController@daftar`, isi `kontributor_since = now()` dan, bila `username` masih kosong, isi `username` via generator (FR-E3). Pengisian harus **idempoten** (tidak menimpa `username`/`kontributor_since` yang sudah terisi).
- **FR-E5:** Backfill data lama: untuk seluruh user yang **saat ini** ber-role `Kontributor` dan `username`/`kontributor_since` kosong, generate `username` dan set `kontributor_since = users.created_at`. Backfill dijalankan via migration data atau command artisan khusus (bukan mengubah migration lama).

### FR-F: CTA Ajakan Menjadi Kontributor (komponen reusable)
- **FR-F1:** Sediakan komponen Blade reusable, mis. `<x-kontributor.cta />`, berisi ajakan singkat + tombol ke `route('kontributor.index')`.
- **FR-F2:** Visibilitas: sembunyikan bila `auth()->check() && auth()->user()->hasRole('Kontributor')`. Selain itu tampil (tamu & non-kontributor).
- **FR-F3:** Pasang komponen pada: `majelis/detail`, `guru/detail`, `biography/detail`, dan `wirid/list` (halaman daftar amalan, di luar komponen Livewire agar tampil sekali per halaman).

---

## Authorization Rules

| Aksi | Syarat |
|---|---|
| Melihat blok atribusi | Publik (mengikuti visibilitas halaman) |
| Melihat halaman profil kontributor | Publik; user target harus punya `kontributor_since` (else 404) |
| Melihat tombol CTA | Tamu & pengguna non-`Kontributor`; disembunyikan untuk role `Kontributor` |
| Mengisi `username`/`kontributor_since` | Dilakukan sistem saat `assignRole('Kontributor')`; tidak ada endpoint user-editable di scope ini |

> Halaman profil hanya menampilkan data yang sudah tayang publik, jadi tidak ada kebocoran data `pending`/`rejected` (selaras BR-04/BR-05 di `sistem-kontributor.md`).

---

## Database Impact

### Perubahan Tabel `users` (migration baru, backward-compatible)

```
username           string  NULL  UNIQUE   -- slug publik kontributor
kontributor_since  timestamp NULL          -- diisi saat assignRole('Kontributor')
```

- Keduanya `nullable` → tidak memecah data lama.
- Indeks unik pada `username` (abaikan `NULL`).
- **Tanpa** perubahan pada `assemblies`, `teachers`, `wirids` (kolom pembuat & status sudah ada dari Sistem Kontributor).

### Backfill (tidak destruktif)
- Generate `username` + set `kontributor_since = created_at` untuk user ber-role `Kontributor` yang belum terisi. Tidak menghapus/menimpa data lain.

### Perubahan Model (bukan migration)
- `User`: tambahkan `username` ke `$fillable`, metode `generateUniqueUsername()`, dan (opsional) `getRouteKeyName()` **tidak** diubah global agar tidak memengaruhi binding lain — gunakan resolusi eksplisit by `username` di controller profil.
- Pastikan relasi `contributor()` tersedia & konsisten di `Assembly`, `Teacher`, `Wirid` (sudah ada).

---

## UI / Route Impact

### Route Baru
```php
// Public
GET /kontributor/profil/{username}  → KontributorController@profil   name: kontributor.profil
```

### Controller
- `KontributorController@profil($username)` — baru (menambah method pada controller publik yang sudah ada).
- `KontributorController@daftar` — diperluas untuk mengisi `username` + `kontributor_since` (idempoten).

### Komponen Blade Baru (reusable, anonymous component sesuai pola `x-*` proyek)
```
resources/views/components/kontributor/attribution.blade.php   -- <x-kontributor.attribution :user="..." />
resources/views/components/kontributor/cta.blade.php           -- <x-kontributor.cta />
```

### View yang Diubah
```
resources/views/pages/user/majelis/detail.blade.php      -- atribusi + CTA
resources/views/pages/user/guru/detail.blade.php         -- atribusi + CTA
resources/views/pages/user/biography/detail.blade.php    -- atribusi + CTA
resources/views/pages/user/wirid/list.blade.php          -- CTA (sekali per halaman)
resources/views/livewire/list-wirid.blade.php            -- atribusi per kartu
```

### Controller View yang Mungkin Disesuaikan (eager loading)
```
app/Http/Controllers/User/MajelisController.php@detail        -- with('contributor')
app/Http/Controllers/User/GuruController.php@detail           -- with('contributor')
app/Http/Controllers/User/BiographyController.php@detail       -- with('contributor')
app/Livewire/ListWirid.php                                    -- eager load contributor pada query wirid
```

### Halaman Baru
```
resources/views/pages/kontributor/profil.blade.php   -- profil publik kontributor
```

---

## Edge Cases

- **ATB-01:** Data dibuat admin (`user_id`/`contributor_user_id` = `NULL`) → fallback "Admin Syaikhuna", tanpa tautan.
- **ATB-02:** Kontributor `badge_title` `null`/kosong → tampilkan nama+avatar saja, baris badge disembunyikan.
- **ATB-03:** Avatar tidak diunggah → `profile_photo_url` Jetstream tetap mengembalikan fallback inisial (selalu ada URL).
- **ATB-04:** Role `Kontributor` pembuat dicabut → atribusi tetap tampil & tetap tertaut; halaman profil tetap dapat diakses (syarat: `kontributor_since` terisi).
- **ATB-05:** Pembuat memiliki `kontributor_since` namun `username` kosong (data anomali yang lolos backfill) → atribusi tampil tanpa tautan; tidak error.
- **ATB-06:** Akun pembuat dihapus → kolom pembuat menjadi `NULL` (ON DELETE SET NULL, sesuai EC-05 spec induk) → otomatis jatuh ke fallback "Admin Syaikhuna".
- **ATB-07:** Akses `/kontributor/profil/{username}` untuk user yang belum pernah jadi kontributor (`kontributor_since` `null`) atau username tidak ada → `404`.
- **ATB-08:** Bentrokan `username` saat generate (dua nama identik) → suffix unik menjamin keunikan; constraint DB unik sebagai pengaman terakhir.
- **ATB-09:** Pengguna sudah `Kontributor` membuka halaman detail → blok atribusi tetap tampil, namun CTA "Jadi Kontributor" disembunyikan.
- **ATB-10:** Profil kontributor tanpa kontribusi tayang publik (semua masih `pending`) → halaman tetap tampil identitas+badge, daftar kontribusi kosong dengan empty state, statistik 0.

---

## Compatibility Considerations

- **CC-A1:** Penambahan kolom `username`/`kontributor_since` `nullable` tidak memengaruhi alur registrasi/login/Jetstream yang ada.
- **CC-A2:** Tidak mengubah `getRouteKeyName()` global pada `User` (binding `User` di tempat lain tetap by `id`). Resolusi profil dilakukan eksplisit by `username`.
- **CC-A3:** Relasi pembuat berbeda antar model (`user_id` vs `contributor_user_id`) — komponen atribusi menerima objek `User` yang sudah diresolusi controller/komponen, sehingga komponen tidak bergantung pada nama kolom.
- **CC-A4:** Atribusi & profil hanya membaca data tayang publik; konsisten dengan scope visibilitas `contribution_status` di `sistem-kontributor.md` (BR-04/BR-05).
- **CC-A5:** Generator `username` mengikuti pola unik yang sudah dipakai `Teacher` agar konsisten, tidak membuat utilitas duplikat (Architecture Rules CLAUDE.md).

---

## Acceptance Criteria

### AC-A1: Atribusi Kontributor pada Detail Majelis
```
Given sebuah Majelis tayang publik dengan user_id menunjuk ke kontributor ber-username "abdullah-x1"
When pengunjung membuka /majelis/{id}
Then tampil blok atribusi berisi avatar, nama, dan badge kontributor
And blok tersebut tertaut ke /kontributor/profil/abdullah-x1
```

### AC-A2: Fallback Admin pada Data Buatan Admin
```
Given sebuah Guru tayang publik dengan contributor_user_id = NULL
When pengunjung membuka halaman detail guru
Then tampil blok atribusi bertuliskan "Admin Syaikhuna"
And blok tersebut tidak memiliki tautan
```

### AC-A3: Atribusi pada Manaqib Ulama
```
Given sebuah Teacher dengan wafat_hijriah_day != null, dibuat oleh seorang kontributor
When pengunjung membuka /manaqib/{slug}
Then tampil blok atribusi kontributor yang tertaut ke profil kontributor
```

### AC-A4: Atribusi pada Kartu Amalan
```
Given daftar amalan berisi Wirid yang dibuat kontributor dan Wirid yang dibuat admin
When pengunjung membuka /wirid
Then setiap kartu Wirid kontributor menampilkan atribusi tertaut ke profilnya
And setiap kartu Wirid buatan admin menampilkan "Admin Syaikhuna" tanpa tautan
And tidak terjadi query N+1 untuk relasi kontributor
```

### AC-A5: Halaman Profil Publik Kontributor
```
Given kontributor "abdullah-x1" memiliki 2 Majelis dan 1 Guru yang tayang publik, kontributor_since terisi
When pengunjung (tanpa login) membuka /kontributor/profil/abdullah-x1
Then tampil nama, avatar, badge, total poin, dan asal daerah
And statistik menunjukkan 2 Majelis dan 1 Guru
And daftar kontribusi menampilkan ketiga item tertaut ke detail masing-masing
And tanggal bergabung ditampilkan
```

### AC-A6: Profil Hanya Menampilkan Data Tayang Publik
```
Given kontributor memiliki 1 Majelis approved dan 1 Majelis pending
When pengunjung membuka profil kontributor tersebut
Then hanya Majelis approved yang tampil
And statistik Majelis menunjukkan 1
```

### AC-A7: Profil 404 untuk Non-Kontributor
```
Given seorang user biasa tanpa kontributor_since
When pengunjung membuka /kontributor/profil/{username user tersebut atau username acak}
Then server mengembalikan 404
```

### AC-A8: CTA Disembunyikan untuk Kontributor
```
Given pengguna login dengan role Kontributor
When ia membuka detail Majelis/Guru/Manaqib atau daftar Amalan
Then tombol "Jadi Kontributor" tidak ditampilkan
```

### AC-A9: CTA Tampil & Mengarah Benar untuk Non-Kontributor
```
Given pengunjung tamu (belum login) atau pengguna non-Kontributor
When ia membuka salah satu halaman target
Then tombol "Jadi Kontributor" tampil
And mengarah ke route kontributor.index
```

### AC-A10: Pengisian username & kontributor_since saat Daftar
```
Given pengguna memenuhi syarat dan menekan "Daftar Jadi Kontributor"
When role Kontributor diberikan
Then users.kontributor_since terisi waktu saat ini
And users.username terisi slug unik dari namanya (jika sebelumnya kosong)
And pemanggilan ulang tidak menimpa username/kontributor_since yang sudah ada
```

### AC-A11: Backfill Kontributor Lama
```
Given terdapat user ber-role Kontributor sebelum fitur ini dengan username/kontributor_since kosong
When backfill dijalankan
Then setiap user tersebut memiliki username unik
And kontributor_since = created_at masing-masing
And atribusi pada data mereka menjadi tertaut ke profilnya
```

---

## Testing and Verification

### Unit / Feature Test
- `UsernameGeneratorTest`: keunikan username untuk nama identik (suffix unik), idempotensi.
- `KontributorDaftarTest`: `assignRole('Kontributor')` mengisi `kontributor_since` + `username`; tidak menimpa nilai yang sudah ada.
- `KontributorProfilTest`:
  - 200 untuk username valid dengan `kontributor_since`.
  - 404 untuk non-kontributor / username tidak ada.
  - Hanya data `approved`/`NULL` yang tampil; `pending`/`rejected` tersembunyi.
- `AtribusiResolutionTest`: kontributor null → fallback "Admin Syaikhuna"; kontributor ada → tertaut.
- `CtaVisibilityTest`: tersembunyi untuk role `Kontributor`, tampil untuk tamu & non-kontributor.

### Manual Verification (End-to-End)
1. Buka detail Majelis buatan kontributor → blok atribusi tampil & klik mengarah ke `/kontributor/profil/{username}`.
2. Buka detail Majelis/Guru buatan admin → "Admin Syaikhuna" tanpa tautan.
3. Buka `/manaqib/{slug}` (Teacher wafat) buatan kontributor → atribusi tertaut.
4. Buka `/wirid` → kartu amalan menampilkan atribusi/fallback per item; cek tidak ada N+1 (Laravel Debugbar / query log).
5. Buka halaman profil kontributor → identitas, badge, statistik, daftar kontribusi (hanya tayang publik), tanggal bergabung benar.
6. Akses profil username acak / non-kontributor → 404.
7. Login sebagai Kontributor → CTA hilang di keempat lokasi; login sebagai user biasa / tamu → CTA tampil & mengarah ke `kontributor.index`.
8. Daftar kontributor baru → verifikasi `username` & `kontributor_since` terisi di DB; atribusi pada datanya langsung tertaut.
9. Jalankan backfill → verifikasi kontributor lama mendapat `username` + `kontributor_since`.
10. Cabut role salah satu kontributor → atribusi & profilnya tetap dapat diakses.

---

## Risks and Dependencies

| # | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| RA-01 | Query N+1 pada daftar amalan saat resolusi kontributor | Sedang | Eager load relasi `contributor` pada query Wirid di `ListWirid` |
| RA-02 | User lama ber-role Kontributor tanpa `username` → atribusi tak tertaut | Sedang | Backfill wajib dijalankan saat deploy (FR-E5) sebelum fitur dianggap selesai |
| RA-03 | Bentrokan `username` | Rendah | Generator suffix unik + unique constraint DB |
| RA-04 | Kebocoran data `pending`/`rejected` di profil publik | Tinggi | Filter `contribution_status IS NULL OR 'approved'` konsisten pada semua query profil/statistik |
| RA-05 | Perubahan binding `User` (jika `getRouteKeyName` diubah) memecah route lain | Sedang | Jangan ubah `getRouteKeyName` global; resolusi profil eksplisit by `username` |

### Dependensi
- Sistem Kontributor (`sistem-kontributor.md`) sudah terimplementasi: role `Kontributor`, kolom `contributor_user_id`/`user_id`/`contribution_status`, `badge_title`, `total_khidmah_points`.
- Migration `users` (username, kontributor_since) + backfill dijalankan sebelum verifikasi.

---

## Referensi File Proyek

| File | Relevansi |
|---|---|
| `app/Http/Controllers/KontributorController.php` | `index` (leaderboard), `daftar` (assignRole) — diperluas + method `profil` |
| `app/Models/User.php` | `HasProfilePhoto` (`profile_photo_url`), `badge_title`, `total_khidmah_points`, tambah `username`/`kontributor_since`/generator |
| `app/Models/Assembly.php` | Relasi `contributor()`/`user()` via `user_id`; scope visibilitas `contribution_status` |
| `app/Models/Teacher.php` | Relasi `contributor()` via `contributor_user_id`; `wafat_hijriah_day`; pola slug unik (referensi generator username) |
| `app/Models/Wirid.php` | Relasi `contributor()` via `contributor_user_id`; scope `contribution_status` |
| `app/Http/Controllers/User/MajelisController.php` | `detail` — eager load `contributor`, pasang atribusi |
| `app/Http/Controllers/User/GuruController.php` | `detail` — atribusi guru |
| `app/Http/Controllers/User/BiographyController.php` | `detail` — atribusi manaqib |
| `app/Http/Controllers/User/WiridController.php` + `app/Livewire/ListWirid.php` | Daftar amalan — atribusi per kartu + eager load |
| `resources/views/livewire/list-wirid.blade.php` | Footer kartu amalan — lokasi atribusi |
| `routes/web.php` | Registrasi route `kontributor.profil`; rujukan `kontributor.index` (baris 105) |
| `docs/specs/sistem-kontributor.md` | Spec induk: role, moderasi, kolom kontribusi, badge/XP |
```
