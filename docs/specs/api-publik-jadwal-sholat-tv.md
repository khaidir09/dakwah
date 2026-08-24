# API Publik untuk Aplikasi Jadwal Sholat Mesjid (Android TV)

**Status Dokumen:** Draft — menunggu persetujuan
**Tanggal:** 2026-07-21
**Author:** Muhammad Khaidir

---

## Latar Belakang

Aplikasi Jadwal Sholat Mesjid berbasis Android TV terpasang di layar-layar mesjid. Di luar jam sholat, layar itu praktis kosong — ruang tampilan yang sangat berharga dan saat ini tidak termanfaatkan.

Syaikhuna sudah memiliki tiga jenis data yang tepat untuk mengisi ruang itu: **jadwal majelis** di sekitar mesjid, **wirid/doa** harian, dan **haul guru yang sudah wafat**. Ketiganya hari ini hanya bisa diakses lewat web Syaikhuna dan tidak tersedia dalam bentuk yang dapat dikonsumsi mesin.

Fitur ini membuka ketiga data tersebut sebagai **API baca-saja (read-only)** untuk platform luar, dimulai dari satu konsumen: aplikasi Jadwal Sholat Mesjid.

Ini sejalan dengan visi Syaikhuna sebagai "Sistem Operasi Digital" masyarakat Banjar — data Syaikhuna hadir di layar mesjid tanpa jamaah perlu membuka aplikasi Syaikhuna.

Dalam penulisan spesifikasi ini ditemukan beberapa cacat pada kode yang ada. Sebagian **ikut masuk scope** karena API tidak bisa dibangun benar tanpanya; sebagian lagi dicatat sebagai temuan di luar scope (lihat [Temuan](#temuan-di-luar-scope)).

---

## Tujuan

1. Aplikasi pihak ketiga dapat mengambil **jadwal majelis** pada wilayah tertentu, sudah dalam bentuk **tanggal konkret** — klien TV tidak perlu memahami aturan recurrence maupun kalender Hijriah.
2. Aplikasi pihak ketiga dapat mengambil **wirid dan doa** dalam bentuk HTML maupun teks polos.
3. Aplikasi pihak ketiga dapat mengambil **daftar haul guru** yang mendekat, diurutkan berdasarkan kalender Hijriah.
4. Akses dapat **diidentifikasi per-partner, dibatasi, dan dicabut** tanpa deploy ulang.
5. **Tidak ada data non-publik yang bocor** — kontribusi pending/ditolak dan jadwal berakses khusus tidak boleh pernah muncul di response.

### Bukan Tujuan

- API tulis (write). Semua endpoint read-only.
- Menjadi API umum untuk semua entitas Syaikhuna (pustaka, tulisan, event, artikel ilmiah).
- Menggantikan atau mengubah halaman web publik yang ada.
- Menyediakan jadwal sholat itu sendiri — itu domain aplikasi konsumen.

---

## Perilaku Saat Ini

### Tidak ada API

`routes/api.php` masih berisi stub bawaan Laravel (`GET /api/user` dengan `auth:sanctum`). Tidak ada direktori `app/Http/Resources`. Sanctum terpasang (`config/sanctum.php`, migrasi `personal_access_tokens`) tapi tidak dipakai untuk melayani konsumen luar.

Middleware group `api` di `app/Http/Kernel.php:41-45` sudah memuat `ThrottleRequests:api`, dan limiter `api` terdaftar di `app/Providers/RouteServiceProvider.php:27` sebesar **60 request/menit** per user-id atau IP. Struktur proyek memakai skeleton gaya Laravel 10 (`app/Http/Kernel.php`, bukan `bootstrap/app.php`) meski framework-nya Laravel 11.41.

### Jadwal Majelis

**Tabel `schedules`** (`database/migrations/2025_11_08_201801_create_schedules_table.php` + ALTER berikutnya):

| Kolom | Tipe | Catatan |
|---|---|---|
| `nama_jadwal` | string(255) | |
| `deskripsi` | longText | HTML |
| `assembly_id` | FK cascade | |
| `teacher_id` | FK nullable | ditambah `2026_07_10_000001_add_teacher_id_to_schedules_table.php` |
| `waktu` | **dateTime** | hanya komponen jam yang bermakna; diformat lewat `getWaktuFormattedAttribute` |
| `hari` | string nullable | nama hari Indonesia: `Senin`…`Minggu` |
| `access` | string, default `Umum` | divalidasi `in:Umum,Ikhwan,Akhwat` di controller |
| `status` | enum | `Aktif` \| `Selesai` \| `Batal` \| `Libur Ramadhan` |
| `recurrence_type` | string, default `weekly` | |
| `calendar_system` | string, default `gregorian` | |
| `week_of_month`, `week_of_month_secondary` | string nullable | `1`–`4` \| `last` |
| `day_of_month` | tinyInt nullable | cast `integer` |
| `contributor_user_id`, `contribution_status`, `rejection_reason`, `moderated_at` | | moderasi |

`app/Models/Schedule.php` mendefinisikan `RECURRENCE_TYPES = ['weekly','monthly_weekday','monthly_date','semimonthly','hijri_first_week']`, `WEEKS_OF_MONTH = ['1','2','3','4','last']`, plus `recurrenceRules()`, `normalizeRecurrence()`, scope `weekly()`/`berkala()`/`publiclyVisible()`, dan accessor `recurrence_label`.

**Yang krusial: model hanya menyimpan _aturan_ dan merender _label manusia_ (`getRecurrenceLabelAttribute`). Tidak ada kode di codebase yang menghitung aturan itu menjadi tanggal konkret.** `docs/specs/jadwal-majelis-recurrence.md` juga tidak menspesifikasikan kalkulasi tanggal. Konsekuensinya, kemampuan inti yang dibutuhkan API ini belum ada dan harus dibangun.

**Rendering publik hari ini** (semuanya Livewire, provinsi di-hardcode ke `[62, 63, 64]`):

| Komponen | Query |
|---|---|
| `app/Livewire/HomeJadwalMajelis.php` | `weekly()` + `where('hari', hari-ini)` — **tanpa** `publiclyVisible()` |
| `app/Livewire/ListJadwalMajelis.php` | `weekly()` diurut `CASE hari` — **tanpa** `publiclyVisible()`; blok `berkalaSchedules` memakai `berkala()->publiclyVisible()` |
| `app/Http/Controllers/User/JadwalMajelisController.php` | `list()` mengembalikan **seluruh** schedule tanpa scope visibilitas maupun pagination |

### Wirid

**Tabel `wirids`** (`2025_12_19_182615_create_wirids_table.php` + ALTER): `kategori` (string default `wirid`, terindeks; nilai `wirid`/`doa` sejak `2026_02_03_000000_merge_doas_into_wirids.php`), `nama` (100), `deskripsi` (text nullable), `arab` (text, wajib), `arti` (text nullable), `jumlah` (int default 1), `likes` (int default 0, counter denormalisasi), `waktu` (string 100 nullable — label bebas seperti "Pagi", dipakai sebagai facet filter), plus kolom kontribusi.

`deskripsi`, `arab`, dan `arti` **dirender sebagai HTML** (`resources/views/livewire/list-wirid.blade.php:68,71,77` memakai `{!! !!}`).

`app/Models/Wirid.php` punya `publiclyVisible()`, `scopeWirid()`, `scopeDoa()`, dan relasi `likedByUsers()` (pivot `wirid_user`).

Rute publik `GET /wirid` (`routes/web.php:68`) hanya mengembalikan view; query sebenarnya di `app/Livewire/ListWirid.php` — `publiclyVisible()` + filter `kategori` + filter `waktu` + pencarian `nama`, `simplePaginate(10)`.

### Haul Guru

**Tabel `teachers`**: `wafat_masehi` (date), `wafat_hijriah` (string bebas, mis. "17 Syakban 1447"), `wafat_hijriah_day` (tinyInt nullable), `wafat_hijriah_month` (tinyInt nullable 1–12), `wafat_hijriah_year` (int nullable), `foto` (nullable), `slug` (route key), `biografi` (longText), kolom wilayah, dan kolom kontribusi. Kolom hari/bulan hijriah ditambahkan `2026_02_04_114459_add_hijri_death_date_columns_to_teachers.php` yang mem-backfill dengan regex atas `wafat_hijriah`.

`app/Livewire/HomeUpcomingHaul.php` menghitung haul mendekat:

1. Ambil tanggal Hijriah hari ini — **tidak memakai `HijriService`**, melainkan memanggil sendiri `https://api.myquran.com/v3/cal/today?m=islamic-civil&tz=Asia/Makassar` dengan cache 24 jam pada key `hijri_date_{Y-m-d}` (key yang sama dengan `HijriService`, jadi nilainya terbagi — duplikasi kode, bukan duplikasi request).
2. Regex-parse "17 Syakban 1447 H" menjadi `{day, month}` lewat peta nama bulan Indonesia yang di-hardcode.
3. `Teacher::where('wafat_hijriah_month', $month)->where('wafat_hijriah_day', '>=', $day)` digabung dengan bulan berikutnya (12 membungkus ke 1), `->take(6)`.

Tidak ada filter `publiclyVisible()`. Peta bulan di komponen memakai ejaan `ramadan`, sedangkan backfill migrasi memakai `ramadhan` — inkonsistensi yang sudah tercatat.

Pembeda manaqib vs guru hidup adalah `wafat_hijriah_year` (`ListBiography` mensyaratkan tidak-null, `ListGuru` mensyaratkan null).

### Gambar

Path relatif disimpan di DB, file berada di disk `public` (`storage/app/public`, URL `APP_URL/storage`). `Assembly` punya accessor `gambar_thumb_url`/`gambar_large_url`. **`Teacher` tidak punya accessor apa pun** — view memanggil `Storage::url($teacher->foto)` langsung, sebagian view legacy memakai `asset('storage/'.$foto)`.

---

## Perilaku yang Diharapkan

### Ringkasan Keputusan Desain

| Aspek | Keputusan |
|---|---|
| Autentikasi | API key per-partner, di-hash, header `X-API-Key` |
| Pengelolaan key | Artisan command saja, tanpa UI admin |
| Scoping data | Filter wilayah; **`city_code` wajib** pada endpoint jadwal |
| Recurrence | Server mengekspansi ke tanggal konkret |
| Payload haul | Ringkas + `foto_url`; tanpa biografi |
| Konten wirid | HTML sudah di-`clean()` **dan** varian teks polos |
| Caching | Cache server 15 menit + ETag/`If-None-Match` |
| Versioning | Prefix `/api/v1/` |

### Autentikasi dan Otorisasi

Tabel baru `api_clients`:

| Kolom | Tipe | Catatan |
|---|---|---|
| `id` | id | |
| `name` | string(100) | nama partner, mis. "Jadwal Sholat Mesjid TV" |
| `key_prefix` | string(12), unik, terindeks | 8 karakter awal key, untuk lookup tanpa membuka hash |
| `key_hash` | string | `Hash::make()` atas key penuh |
| `rate_limit_per_minute` | unsignedSmallInt, default 60 | |
| `last_used_at` | timestamp nullable | diperbarui paling sering sekali per menit |
| `revoked_at` | timestamp nullable | pencabutan lunak |
| timestamps | | |

Model `app/Models/ApiClient.php` dengan scope `active()` (`whereNull('revoked_at')`).

Middleware `app/Http/Middleware/AuthenticateApiClient.php` (alias `api.client`):

1. Baca header `X-API-Key`. Tidak ada → **401** `{"error":{"code":"unauthenticated","message":"..."}}`.
2. Ambil 8 karakter pertama sebagai `key_prefix`, cari `ApiClient` yang `active()`. Tidak ketemu → **401**.
3. `Hash::check()` key penuh terhadap `key_hash`. Gagal → **401**.
4. Sukses → simpan client pada request (`$request->attributes->set('api_client', $client)`), perbarui `last_used_at` bila lebih lama dari 1 menit.

Perbandingan hash dilakukan **selalu setelah lookup prefix berhasil**, sehingga key salah dan prefix salah tidak dibedakan dari sisi response.

**Rate limiting**: limiter bernama `api-public` di `RouteServiceProvider`, dibatasi per `api_client->id` (jatuh ke IP bila client tidak ada), memakai `rate_limit_per_minute` milik client. Limit terlampaui → **429** dengan header `Retry-After`.

Semua endpoint bersifat publik-dengan-key. Tidak ada konsep user, role, atau kepemilikan — Spatie Permission tidak terlibat.

**Pengelolaan key** — `php artisan api:client-create {name} {--limit=60}`:

- Membuat key acak 40 karakter (`Str::random(40)`), menyimpan `key_prefix` + `key_hash`, mencetak key penuh **satu kali** ke stdout dengan peringatan bahwa key tidak dapat ditampilkan lagi.
- `php artisan api:client-revoke {prefix}` mengisi `revoked_at`.

### Aturan Visibilitas (Wajib)

Setiap query API menerapkan filter berikut. Ini adalah **batas keamanan**, bukan preferensi tampilan.

**Jadwal** — ketiganya berlaku bersamaan:

1. `Schedule::publiclyVisible()` — `contribution_status` null atau `approved`.
2. `whereIn('access', ['Umum', 'Ikhwan', 'Akhwat'])` — **allowlist**, bukan blocklist. Nilai lain (termasuk `Khusus` dan nilai tak terduga di masa depan) tidak pernah keluar.
3. `where('status', 'Aktif')`.
4. Assembly induknya juga harus `Assembly::publiclyVisible()` — jadwal dari majelis yang belum dimoderasi tidak boleh bocor lewat pintu belakang.

`status = 'Libur Ramadhan'` **tidak** dikembalikan sebagai jadwal aktif. Sebagai gantinya, response menyertakan flag global `meta.is_ramadhan` dari `HijriService::isRamadhan()` agar klien dapat menampilkan keterangan.

**Wirid**: `Wirid::publiclyVisible()`.

**Haul**: `Teacher::publiclyVisible()` **dan** `whereNotNull('wafat_hijriah_year')` — hanya guru yang sudah wafat, konsisten dengan definisi manaqib di `ListBiography`.

### Endpoint

Prefix `/api/v1`, middleware `['api', 'api.client', 'throttle:api-public']`.

**Kebijakan versi:**

- **v1 adalah kontrak yang stabil.** Selama v1 hidup, hanya perubahan **additive** yang diperbolehkan: menambah field baru pada response, menambah parameter query opsional, menambah endpoint baru di bawah `/api/v1/`.
- **Perubahan breaking** — menghapus atau mengganti nama field, mengubah tipe/format nilai, mengubah arti sebuah field, memperketat aturan visibilitas, atau membuat parameter yang tadinya opsional menjadi wajib — **tidak boleh masuk ke v1**. Perubahan seperti itu memerlukan `/api/v2/` yang berjalan berdampingan.
- Klien **wajib mengabaikan field yang tidak dikenal**. Ini dinyatakan eksplisit di `docs/api-partner.md`; tanpa itu, penambahan field pun bisa merusak klien yang parsing-nya kaku.
- Setiap response menyertakan header `X-Api-Version: 1` agar versi yang benar-benar melayani sebuah request dapat dipastikan dari log klien.
- Path tanpa versi (`/api/jadwal-majelis` dan sejenisnya) **tidak didaftarkan** — tidak ada alias, tidak ada redirect. Klien yang lupa menyertakan versi mendapat 404, bukan diam-diam terikat ke versi terbaru.
- Kebijakan penghentian: sebuah versi baru boleh dimatikan setelah `last_used_at` pada seluruh `api_client` yang memakainya menunjukkan tidak ada lagi lalu lintas, atau setelah pemberitahuan yang disepakati dengan partner. Struktur controller/resource memakai namespace `Api\V1\` sejak awal agar v2 tidak perlu memindahkan file v1.

#### `GET /api/v1/jadwal-majelis`

| Parameter | Wajib | Aturan |
|---|---|---|
| `city_code` | **ya** | string, harus ada di tabel `cities` |
| `district_code` | tidak | string, mempersempit |
| `days` | tidak | integer 1–30, default **7** — jendela ekspansi mulai hari ini |
| `tipe` | tidak | `Majelis` \| `Mesjid` \| `Langgar` \| `Musholla` |

`province_code` sengaja tidak disediakan: mewajibkan `city_code` menjaga jumlah hasil tetap kecil dan kunci cache tetap sedikit. Tanpa `city_code` → **422**.

Response mengembalikan **occurrence**, bukan schedule. Satu schedule mingguan dalam jendela 7 hari menghasilkan satu occurrence; schedule dua-mingguan bisa menghasilkan nol.

```json
{
  "data": [
    {
      "id": 812,
      "schedule_id": 145,
      "tanggal": "2026-07-22",
      "hari": "Rabu",
      "waktu": "19:30",
      "nama_jadwal": "Pengajian Kitab Sifat Dua Puluh",
      "recurrence_label": "Setiap Rabu",
      "access": "Umum",
      "majelis": {
        "id": 31,
        "nama": "Majelis Ta'lim Ar-Raudhah",
        "tipe": "Majelis",
        "alamat": "Jl. ...",
        "maps": "https://maps.app.goo.gl/...",
        "city_code": "6371",
        "district_code": "637101",
        "gambar_url": "https://syaikhuna.id/storage/majelis/thumb/....webp"
      },
      "guru": { "id": 7, "nama": "KH. ...", "slug": "kh-...", "foto_url": "https://..." },
      "url": "https://syaikhuna.id/jadwal-majelis/145"
    }
  ],
  "meta": { "city_code": "6371", "days": 7, "from": "2026-07-21", "to": "2026-07-27", "is_ramadhan": false }
}
```

Catatan field:

- `id` occurrence bersifat sintetis dan **tidak stabil**; klien harus memakai `schedule_id` + `tanggal` sebagai identitas.
- `waktu` berformat `HH:mm` 24 jam (bukan `isoFormat('LT')` yang dipakai web) — lebih mudah diparse mesin.
- `deskripsi` **tidak** disertakan; berupa HTML panjang dan tidak dipakai di layar TV.
- Semua URL gambar **absolut**.
- Diurutkan `tanggal` lalu `waktu` menaik.
- Tanpa pagination: jendela dibatasi 30 hari dan lingkupnya satu kota, jadi hasil tetap terbatas.

#### `GET /api/v1/wirid`

| Parameter | Wajib | Aturan |
|---|---|---|
| `kategori` | tidak | `wirid` \| `doa`; tanpa parameter = keduanya |
| `waktu` | tidak | string, cocok persis dengan facet `wirids.waktu` |
| `per_page` | tidak | integer 1–50, default 20 |
| `page` | tidak | integer |

```json
{
  "data": [
    {
      "id": 12,
      "kategori": "wirid",
      "nama": "Istighfar",
      "waktu": "Pagi",
      "jumlah": 100,
      "arab_html": "<p>أَسْتَغْفِرُ اللهَ</p>",
      "arab_text": "أَسْتَغْفِرُ اللهَ",
      "arti_html": "<p>Aku memohon ampun kepada Allah</p>",
      "arti_text": "Aku memohon ampun kepada Allah",
      "deskripsi_html": "<p>...</p>",
      "deskripsi_text": "...",
      "url": "https://syaikhuna.id/wirid"
    }
  ],
  "meta": { "current_page": 1, "per_page": 20, "total": 143, "last_page": 8 }
}
```

Varian `*_html` melewati `clean()` (mews/purifier) **saat serialisasi**, bukan saat simpan — konten lama di DB belum tentu bersih. Varian `*_text` = `html_entity_decode(strip_tags(...))` dengan whitespace dirapikan.

#### `GET /api/v1/haul`

| Parameter | Wajib | Aturan |
|---|---|---|
| `limit` | tidak | integer 1–50, default 10 |

```json
{
  "data": [
    {
      "id": 7,
      "nama": "KH. Muhammad Zaini bin Abdul Ghani",
      "slug": "kh-muhammad-zaini-bin-abdul-ghani",
      "wafat_hijriah_label": "5 Rajab 1426",
      "wafat_hijriah_day": 5,
      "wafat_hijriah_month": 7,
      "wafat_hijriah_year": 1426,
      "wafat_masehi": "2005-08-10",
      "haul_berikutnya_masehi": "2026-12-20",
      "hari_menuju_haul": 152,
      "foto_url": "https://syaikhuna.id/storage/guru/....webp",
      "url": "https://syaikhuna.id/guru/kh-muhammad-zaini-bin-abdul-ghani"
    }
  ],
  "meta": { "hijri_today": { "day": 6, "month": 2, "year": 1448 } }
}
```

`wafat_hijriah_label` **dirangkai dari kolom numerik**, bukan diambil dari kolom `teachers.wafat_hijriah`. Kolom string itu ada di migration tetapi **sudah tidak ada di database produksi**, sehingga mengekspornya akan selalu menghasilkan `null`. Peta nama bulan di sini dipakai untuk keluaran saja — API tetap tidak pernah mem-parse nama bulan berbahasa Indonesia, jadi tidak terpapar inkonsistensi `ramadan`/`ramadhan`.

`haul_berikutnya_masehi` dan `hari_menuju_haul` dihitung dengan mengonversi `(wafat_hijriah_day, wafat_hijriah_month)` pada tahun Hijriah berjalan ke tanggal Masehi; bila sudah lewat, dipakai tahun Hijriah berikutnya. Diurutkan menaik berdasarkan `hari_menuju_haul`. `biografi` tidak disertakan (keputusan wawancara).

#### Format error

Seragam untuk semua endpoint:

```json
{ "error": { "code": "validation_failed", "message": "city_code wajib diisi.", "details": { "city_code": ["..."] } } }
```

Kode: `unauthenticated` (401), `validation_failed` (422), `not_found` (404), `rate_limited` (429), `server_error` (500). Handler di `app/Exceptions/Handler.php` dibatasi hanya pada request dengan prefix path `api/` agar tidak mengubah perilaku web. Prefix yang dipakai handler tetap `api/` (bukan `api/v1/`) supaya versi mendatang otomatis ikut terformat, dan supaya 404 atas versi yang salah pun berupa JSON, bukan halaman HTML.

### Ekspansi Recurrence — `ScheduleOccurrenceService`

Kemampuan ini belum ada dan merupakan bagian paling substansial dari fitur ini. Service baru `app/Services/ScheduleOccurrenceService.php`:

```php
/** @return array<int, array{schedule: Schedule, date: CarbonImmutable}> */
public function expand(Collection $schedules, CarbonImmutable $from, CarbonImmutable $to): array
```

Aturan per tipe, dievaluasi untuk setiap tanggal dalam `[from, to]`:

| `recurrence_type` | Cocok bila |
|---|---|
| `weekly` | nama hari Indonesia tanggal tersebut == `hari` |
| `monthly_weekday` | nama hari cocok **dan** posisi pekan tanggal tersebut dalam bulan == `week_of_month` |
| `semimonthly` | nama hari cocok **dan** posisi pekan == `week_of_month` **atau** `week_of_month_secondary` |
| `monthly_date` | `$date->day == day_of_month` |
| `hijri_first_week` | nama hari cocok **dan** tanggal Hijriah-nya berada di 1–7 |

Posisi pekan dihitung `intdiv($date->day - 1, 7) + 1`; `last` berarti `$date->day > $date->daysInMonth - 7`.

**Konversi Hijriah** memakai `IntlCalendar::createInstance('Asia/Makassar', 'en@calendar=islamic-civil')` — deterministik, offline, dan memakai kalender `islamic-civil` yang **sama** dengan parameter `m=islamic-civil` pada `HijriService`, sehingga tidak menimbulkan tanggal Hijriah kedua yang berbeda dari yang tampil di web. Diverifikasi: 1 Syakban 1447 → 2026-01-20.

Konversi dibungkus `app/Services/HijriConverter.php` (`toHijri(CarbonImmutable): array{day,month,year}` dan `toGregorian(int $day, int $month, int $year): CarbonImmutable`) agar `ScheduleOccurrenceService` dan endpoint haul memakai satu implementasi.

`ext-intl` **ditambahkan ke `require` pada `composer.json`** — saat ini dipakai secara implisit tanpa dideklarasikan.

Zona waktu perhitungan: `Asia/Makassar` (WITA), konsisten dengan `HijriService`.

### Caching

Setiap endpoint membungkus hasil dengan `Cache::remember($key, 900, ...)`. Key disusun dari nama endpoint + seluruh parameter query yang tervalidasi dan ternormalisasi (urut alfabet) + tanggal berjalan WITA — sehingga pergantian hari otomatis membatalkan cache jadwal dan haul.

ETag = `md5` atas body JSON, dikirim pada setiap response. Bila request membawa `If-None-Match` yang cocok → **304** tanpa body. Header `Cache-Control: public, max-age=900`.

Cache **tidak** dikunci per-`api_client` — datanya identik untuk semua partner, jadi memasukkan client id ke key hanya akan memperbanyak entri tanpa manfaat.

---

## File yang Terlibat

### Baru

| File | Peran |
|---|---|
| `database/migrations/2026_07_21_000000_create_api_clients_table.php` | tabel `api_clients` |
| `database/migrations/2026_07_21_000001_reconcile_teachers_hijri_year.php` | menambahkan `wafat_hijriah_year` yang ada di produksi tanpa migration (idempoten, tidak menghapus kolom) |
| `app/Models/ApiClient.php` | model + scope `active()` |
| `app/Http/Middleware/AuthenticateApiClient.php` | validasi `X-API-Key` |
| `app/Console/Commands/ApiClientCreate.php` | `api:client-create` |
| `app/Console/Commands/ApiClientRevoke.php` | `api:client-revoke` |
| `app/Http/Controllers/Api/V1/JadwalMajelisApiController.php` | endpoint jadwal |
| `app/Http/Controllers/Api/V1/WiridApiController.php` | endpoint wirid |
| `app/Http/Controllers/Api/V1/HaulApiController.php` | endpoint haul |
| `app/Http/Requests/Api/V1/JadwalMajelisRequest.php` | validasi parameter |
| `app/Http/Requests/Api/V1/WiridRequest.php` | validasi parameter |
| `app/Http/Requests/Api/V1/HaulRequest.php` | validasi parameter |
| `app/Http/Resources/Api/V1/ScheduleOccurrenceResource.php` | serialisasi occurrence |
| `app/Http/Resources/Api/V1/WiridResource.php` | serialisasi wirid |
| `app/Http/Resources/Api/V1/HaulResource.php` | serialisasi haul |
| `app/Services/ScheduleOccurrenceService.php` | ekspansi recurrence → tanggal |
| `app/Services/HijriConverter.php` | konversi Hijriah↔Masehi via `ext-intl` |
| `app/Http/Middleware/SetsEtag.php` | ETag + penanganan 304 |
| `app/Http/Middleware/AddApiVersionHeader.php` | menambahkan `X-Api-Version` sesuai grup versi |
| `routes/api/v1.php` | definisi route v1, di-`require` dari `routes/api.php` |
| `docs/api-partner.md` | dokumentasi untuk partner |
| `tests/Feature/Api/*`, `tests/Unit/*` | lihat bagian Testing |

### Diubah

| File | Perubahan |
|---|---|
| `routes/api.php` | `require` grup `v1`; stub `/user` dibiarkan di luar versi |
| `app/Http/Kernel.php` | alias middleware `api.client`, `etag`, `api.version` |
| `app/Providers/RouteServiceProvider.php` | limiter bernama `api-public` |
| `app/Exceptions/Handler.php` | format error JSON, dibatasi pada path `api/` |
| `composer.json` | tambah `ext-intl` ke `require` |
| `app/Models/Teacher.php` | tambah accessor `foto_url` (dan `foto_bersama_url`) — belum ada sama sekali |
| `config/cors.php` | pastikan path `api/*` mengizinkan origin yang diperlukan |

### Interface yang Digunakan Kembali (tidak diubah)

`Schedule::publiclyVisible()`, `Schedule::RECURRENCE_TYPES`, `recurrence_label`, `Assembly::publiclyVisible()`, `Assembly::gambar_thumb_url`, `Wirid::publiclyVisible()`, `Teacher::publiclyVisible()`, `HijriService::isRamadhan()`, helper `clean()`, `Storage::url()`.

---

## Yang Tidak Termasuk Scope

1. **Perbaikan kebocoran visibilitas pada Livewire web.** `HomeJadwalMajelis` dan `ListJadwalMajelis` tidak menerapkan `publiclyVisible()`; `JadwalMajelisController@list` tidak menerapkan scope maupun pagination; `HomeUpcomingHaul` dan `ListGuru@list` (`Teacher::all()`) juga tidak. **API tidak akan mewarisi cacat ini** — semua filter di atas dibangun ulang di lapisan API. Perbaikan sisi web adalah pekerjaan terpisah (lihat Temuan).
2. **Refactor `HomeUpcomingHaul` agar memakai `HijriService`/`HijriConverter`.** Duplikasi tetap dibiarkan; API memakai jalur barunya sendiri.
3. **Inkonsistensi `ramadan` vs `ramadhan`.** API tidak mem-parse nama bulan berbahasa Indonesia sama sekali — ia memakai kolom numerik `wafat_hijriah_month` dan `IntlCalendar`, sehingga tidak terpapar bug ini.
4. **UI admin untuk kelola API key.** Artisan command saja.
5. **Endpoint untuk entitas lain** (pustaka, tulisan, event, artikel ilmiah, mitra).
6. **Endpoint tulis, webhook, atau push ke partner.**
7. **`/api/v2/` dan mekanisme deprecation otomatis.** Hanya v1 yang dibangun. Struktur namespace `Api\V1\` dan `routes/api/v1.php` disiapkan agar v2 dapat ditambahkan tanpa memindahkan file, tetapi tidak ada kode v2, tidak ada header `Sunset`/`Deprecation`, dan tidak ada negosiasi versi lewat header `Accept`.
8. **Perubahan skema `schedules`, `wirids`, atau `teachers`.** Hanya satu tabel baru yang ditambahkan.
9. **Aplikasi Android TV-nya sendiri.**

---

## Edge Cases

| # | Kasus | Perilaku yang Diharapkan |
|---|---|---|
| E-1 | `X-API-Key` tidak ada / salah format / prefix tak dikenal | 401 `unauthenticated`, response identik untuk ketiganya |
| E-2 | Key sudah dicabut (`revoked_at` terisi) | 401 — sama seperti key tak dikenal, tanpa membocorkan bahwa key pernah valid |
| E-3 | `city_code` tidak dikirim | 422 `validation_failed` |
| E-4 | `city_code` tidak ada di tabel `cities` | 422, bukan 200-kosong — membedakan salah ketik dari benar-benar kosong |
| E-5 | Kota valid tapi tidak ada jadwal | 200 dengan `data: []` |
| E-6 | `days=31` atau `days=0` | 422 |
| E-7 | Jadwal `hari` bernilai null pada tipe non-`monthly_date` | Dilewati oleh service, tidak menghasilkan occurrence, tidak error |
| E-8 | `week_of_month = 'last'` pada Februari 28 vs 29 hari | `day > daysInMonth - 7` — Sabtu tanggal 22 Feb 2027 (28 hari) termasuk `last`; pada Februari 29 hari tidak |
| E-9 | `monthly_date` dengan `day_of_month = 31` di bulan 30 hari | Tidak ada occurrence bulan itu. Tidak digeser ke tanggal 30 — kalender majelis mengikuti tanggal literal |
| E-10 | `hijri_first_week` dan jendela 7 hari melintasi pergantian bulan Hijriah | Setiap tanggal dievaluasi independen; occurrence bisa muncul di kedua sisi pergantian |
| E-11 | `ext-intl` tidak terpasang di server produksi | Deploy gagal di `composer install` karena `ext-intl` kini dideklarasikan — gagal saat deploy, bukan saat runtime |
| E-12 | `HijriService::isRamadhan()` gagal (API myquran down) | Fallback string; `is_ramadhan` menjadi `false`. Endpoint jadwal **tidak** ikut gagal — `meta.is_ramadhan` bukan data kritis |
| E-13 | Ekspansi recurrence tidak bergantung pada API myquran | Benar — `IntlCalendar` bersifat offline, jadi jadwal tetap keluar meski jaringan luar mati |
| E-14 | `teachers.foto` null | `foto_url: null`, bukan string kosong atau URL rusak |
| E-15 | Guru punya `wafat_hijriah_year` tapi `wafat_hijriah_day`/`month` null | Dikecualikan dari `/api/v1/haul` — tanggal haul tak dapat dihitung |
| E-16 | `wafat_hijriah_month = 12`, haul berikutnya melewati pergantian tahun Hijriah | Konversi memakai tahun Hijriah berikutnya; `hari_menuju_haul` tetap positif |
| E-17 | Guru wafat 30 Zulhijjah pada tahun Hijriah yang hanya punya 29 hari | Digeser ke hari terakhir bulan tersebut |
| E-18 | Wirid `arab` berisi HTML berbahaya dari data lama | `clean()` saat serialisasi menghapusnya; `arab_text` bebas tag |
| E-19 | Wirid `arti`/`deskripsi` null | `*_html` dan `*_text` bernilai `null`, bukan string kosong |
| E-20 | `If-None-Match` cocok | 304 tanpa body; tetap memakai kuota rate limit |
| E-21 | Jadwal diedit admin di tengah jendela cache 15 menit | Perubahan tampak paling lambat 15 menit — diterima dan didokumentasikan ke partner |
| E-22 | `status = 'Libur Ramadhan'` | Tidak muncul di `data`; `meta.is_ramadhan` memberi konteks |
| E-23 | Assembly `publiclyVisible()` false tapi schedule-nya approved | Tidak muncul — filter join wajib |
| E-24 | Rate limit terlampaui | 429 + `Retry-After` |
| E-25 | `per_page=999` pada wirid | 422 |
| E-26 | `page` melebihi `last_page` | 200 dengan `data: []` dan meta yang benar |
| E-27 | Klien memanggil path tanpa versi (`/api/jadwal-majelis`) | 404 berformat JSON `not_found` — tidak dialiaskan diam-diam ke v1 |
| E-28 | Klien memanggil versi yang tidak ada (`/api/v2/jadwal-majelis`) | 404 berformat JSON, bukan halaman HTML |
| E-29 | Response sukses maupun error | Selalu membawa header `X-Api-Version: 1` |

---

## Kompatibilitas

- **Web publik tidak berubah.** Tidak ada route web, controller web, Livewire component, atau view yang disentuh. Satu-satunya perubahan model adalah **penambahan** accessor `foto_url` pada `Teacher` — additive, tidak menimpa apa pun (`Teacher` belum punya accessor).
- **Handler exception** hanya berubah untuk request ber-path `api/`; halaman error web tetap sama.
- **Middleware group `api`** sudah memuat `throttle:api`; limiter `api-public` ditambahkan berdampingan, tidak menggantikan.
- **Database**: hanya satu tabel baru. Tidak ada kolom yang dihapus, diubah tipe, atau di-backfill. Migrasi backward-compatible dan reversible.
- **Skema produksi pernah menyimpang dari migrasi** pada tabel `teachers` (diedit manual). Fitur ini tidak mengubah `teachers`, jadi risikonya nihil — namun verifikasi harus dijalankan terhadap DB produksi, bukan hasil `migrate:fresh`.
- **Kontrak API ke partner**: v1 hanya menerima perubahan additive. Penghapusan/penggantian nama field, perubahan tipe atau arti nilai, dan pengetatan aturan visibilitas adalah **breaking** — semuanya masuk `/api/v2/` yang berjalan berdampingan, bukan mengubah v1. Karena versi ada di URL, klien lama tetap berfungsi tanpa perlu di-upgrade.
- `ext-intl` sudah aktif di mesin dev (terverifikasi). Perlu dikonfirmasi di server produksi sebelum deploy.

---

## Testing

### Unit — `tests/Unit/ScheduleOccurrenceServiceTest.php`

| Test | Isi |
|---|---|
| `weekly` | Jendela 7 hari menghasilkan tepat 1 occurrence pada hari yang benar |
| `weekly` jendela 14 hari | Menghasilkan 2 occurrence berjarak 7 hari |
| `monthly_weekday` pekan ke-2 | Hanya tanggal 8–14 yang cocok |
| `monthly_weekday` `last` | Cocok di pekan terakhir pada bulan 28, 30, dan 31 hari (E-8) |
| `semimonthly` | Kedua pekan menghasilkan occurrence; pekan lain tidak |
| `monthly_date` = 31 di bulan 30 hari | Nol occurrence (E-9) |
| `hijri_first_week` | Cocok hanya saat tanggal Hijriah 1–7, dengan tanggal Masehi tetap (`Carbon::setTestNow`) |
| `hari` null | Dilewati tanpa exception (E-7) |
| Urutan hasil | Menaik berdasarkan tanggal lalu waktu |

### Unit — `tests/Unit/HijriConverterTest.php`

Konversi dua arah dengan nilai yang sudah diverifikasi (1 Syakban 1447 ↔ 2026-01-20), pembungkusan tahun, dan E-17 (30 Zulhijjah pada tahun 29 hari).

### Feature — per endpoint

`tests/Feature/Api/JadwalMajelisApiTest.php`, `WiridApiTest.php`, `HaulApiTest.php`:

- 200 dengan struktur JSON persis (`assertJsonStructure`) untuk masing-masing endpoint.
- Parameter wajib hilang → 422 (E-3); kode wilayah tak dikenal → 422 (E-4); wilayah kosong → 200 `data: []` (E-5).
- Batas `days`, `limit`, `per_page` (E-6, E-25) dan pagination melewati batas (E-26).
- URL gambar absolut; `foto_url` null saat foto kosong (E-14).
- `arab_text` bebas tag sementara `arab_html` mempertahankan markup aman (E-18); field null tetap null (E-19).
- Haul: urutan berdasarkan `hari_menuju_haul`, pembungkusan tahun (E-16), pengecualian day/month null (E-15).
- ETag: request kedua dengan `If-None-Match` → 304 (E-20).

### Feature — kebocoran data, `tests/Feature/Api/ApiDataLeakTest.php`

Test regresi keamanan. Untuk tiap kasus, buat record yang seharusnya tersembunyi lalu tegaskan `assertJsonMissing` atas idnya:

- Schedule `contribution_status = pending` dan `rejected`.
- Schedule `access = 'Khusus'` **dan** nilai `access` di luar daftar yang dikenal (menguji allowlist, bukan blocklist).
- Schedule `status` = `Selesai`, `Batal`, `Libur Ramadhan` (E-22).
- Schedule approved milik Assembly yang `contribution_status = pending` (E-23).
- Wirid pending/rejected.
- Teacher pending/rejected, dan teacher tanpa `wafat_hijriah_year` (guru masih hidup) tidak muncul di `/api/v1/haul`.
- Response jadwal tidak memuat key `deskripsi`, `contributor_user_id`, `rejection_reason`, atau `user_id`.

### Feature — versioning, `tests/Feature/Api/ApiVersioningTest.php`

Path tanpa versi → 404 JSON (E-27); versi tak dikenal → 404 JSON (E-28); response sukses maupun error membawa `X-Api-Version: 1` (E-29).

### Feature — auth & rate limit, `tests/Feature/Api/ApiAuthTest.php`

Tanpa key → 401; key salah → 401; prefix tak dikenal → 401; key dicabut → 401 (E-2); key valid → 200; `last_used_at` diperbarui; melampaui `rate_limit_per_minute` → 429 dengan `Retry-After` (E-24).

### Catatan menjalankan test

Test memakai SQLite in-memory (`phpunit.xml`). PHP CLI di mesin ini **tidak memuat `pdo_sqlite`** secara default — jalankan dengan konfigurasi PHP yang mengaktifkannya. Cache harus di-`array` selama test agar cache 15 menit tidak mencemari test antar-kasus.

---

## Acceptance Criteria

1. `GET /api/v1/jadwal-majelis?city_code=6371` dengan `X-API-Key` valid mengembalikan 200 berisi occurrence bertanggal konkret untuk 7 hari ke depan, terurut menaik.
2. Endpoint yang sama tanpa `city_code` mengembalikan 422 dengan format error yang ditentukan.
3. Kelima tipe recurrence menghasilkan tanggal yang benar, dibuktikan unit test — termasuk `hijri_first_week` yang tidak bergantung pada jaringan.
4. `GET /api/v1/wirid` mengembalikan varian `*_html` (sudah di-`clean()`) dan `*_text` (bebas tag), mendukung filter `kategori` dan `waktu`, serta pagination.
5. `GET /api/v1/haul` mengembalikan guru wafat terurut berdasarkan haul terdekat, lengkap dengan `haul_berikutnya_masehi`, `hari_menuju_haul`, dan `foto_url` absolut.
6. Request tanpa key, dengan key salah, atau dengan key dicabut mengembalikan 401 dan response yang tidak dapat dibedakan satu sama lain.
7. Melampaui limit per-client mengembalikan 429 beserta `Retry-After`.
8. **Tidak satu pun** record pending, rejected, `status` non-`Aktif`, `access` di luar allowlist, atau milik Assembly non-publik yang muncul di response mana pun — dibuktikan `ApiDataLeakTest`.
9. Request kedua yang identik dengan `If-None-Match` mengembalikan 304.
10. `php artisan api:client-create "Nama"` mencetak key satu kali; key tersebut berhasil mengautentikasi; `api:client-revoke` membuatnya berhenti bekerja.
11. Seluruh URL gambar bersifat absolut dan dapat dibuka.
12. Seluruh test lulus, `./vendor/bin/pint` bersih, dan `docs/api-partner.md` mendokumentasikan setiap endpoint, parameter, kode error, kebijakan cache 15 menit, kebijakan versi, serta kewajiban klien mengabaikan field yang tidak dikenal.
13. Halaman web publik tidak berubah perilakunya — `git diff` tidak menyentuh view, route web, atau Livewire component mana pun.
14. Semua endpoint berada di bawah `/api/v1/`; memanggilnya tanpa versi mengembalikan 404 JSON; setiap response membawa `X-Api-Version: 1`.

---

## Verifikasi End-to-End

Dijalankan terhadap database yang menyerupai produksi (**bukan** `migrate:fresh` — lihat catatan Kompatibilitas).

1. `php artisan migrate` — tabel `api_clients` terbentuk; tabel lain tidak berubah (`git diff` skema kosong selain tabel baru).
2. `php artisan api:client-create "Jadwal Sholat Mesjid TV"` — catat key yang tercetak.
3. `curl -i https://.../api/v1/jadwal-majelis?city_code=6371` **tanpa** key → 401 berformat JSON, disertai `X-Api-Version: 1`.
4. Ulangi **dengan** `-H "X-API-Key: <key>"` → 200. Verifikasi manual bahwa setiap `tanggal` benar-benar jatuh pada `hari` yang tertera, dan bandingkan isinya dengan halaman `/jadwal-majelis` untuk kota yang sama.
5. Di admin, ubah satu jadwal menjadi `status = Batal`; tunggu hingga cache lewat (atau `php artisan cache:clear`); pastikan jadwal itu hilang dari response.
6. Buat satu jadwal kontribusi berstatus `pending`; pastikan **tidak** muncul.
7. Ubah satu jadwal ke `access = Khusus` lewat DB; pastikan **tidak** muncul.
8. `curl -i` dua kali; ambil `ETag` dari response pertama dan kirim sebagai `If-None-Match` pada yang kedua → 304.
9. Panggil endpoint 70× berturut-turut dengan client berlimit 60 → 429 muncul setelah panggilan ke-60, disertai `Retry-After`.
10. `curl .../api/v1/wirid?kategori=doa` → verifikasi `arab_html` merender benar di klien dan `arab_text` tidak mengandung tag.
11. `curl .../api/v1/haul` → verifikasi `haul_berikutnya_masehi` guru yang sudah dikenal (mis. Abah Guru Sekumpul) terhadap kalender Hijriah, dan `foto_url`-nya dapat dibuka di browser.
11b. `curl -i .../api/jadwal-majelis?city_code=6371` (tanpa versi) dan `.../api/v2/jadwal-majelis` → keduanya 404 berformat JSON (E-27, E-28).
12. `php artisan api:client-revoke <prefix>`; ulangi langkah 4 → 401.
13. Buka `/jadwal-majelis`, `/wirid`, `/guru`, dan halaman depan di browser — pastikan tidak ada perubahan tampilan maupun regresi.
14. `php artisan test` dan `./vendor/bin/pint --test` lulus.

---

## Risiko dan Trade-off

| # | Risiko | Dampak | Mitigasi |
|---|---|---|---|
| R-1 | **API menerapkan visibilitas lebih ketat daripada web.** Web hari ini menampilkan jadwal pending (`HomeJadwalMajelis`, `ListJadwalMajelis`) sedangkan API tidak. | Partner melihat jadwal lebih sedikit daripada yang tampil di web dan melaporkannya sebagai bug. | Disengaja — API benar, web bocor. Didokumentasikan di `docs/api-partner.md`. Perbaikan web dijadwalkan terpisah. |
| R-2 | **Klien tidak bisa dipaksa upgrade** setelah terpasang di ribuan TV. | Perubahan kontrak berpotensi merusak instalasi lapangan. | Versi ada di URL (`/api/v1/`), sehingga breaking change masuk ke v2 yang berjalan berdampingan dan v1 tetap hidup. `last_used_at` memungkinkan mengukur siapa yang masih memakai sebelum mematikan sebuah versi. |
| R-11 | **Biaya merawat banyak versi.** Begitu v2 lahir, perbaikan bug dan perubahan aturan visibilitas harus diterapkan di dua tempat. | Beban perawatan berlipat, dan aturan visibilitas bisa menyimpang antar versi — ini risiko keamanan, bukan sekadar kerapian. | Logika domain (`ScheduleOccurrenceService`, `HijriConverter`) dan scope visibilitas model **tidak diberi versi** — hanya lapisan controller/resource yang diversikan. Dengan begitu perbaikan keamanan otomatis berlaku di semua versi. |
| R-3 | **Ketergantungan `ext-intl`.** | Tanpa ekstensi, ekspansi Hijriah dan endpoint haul mati. | Dideklarasikan di `composer.json` sehingga gagal saat deploy (bukan saat runtime); dikonfirmasi ada di dev. Verifikasi produksi masuk checklist. |
| R-4 | **Cache 15 menit** menunda perubahan jadwal. | Layar TV bisa menampilkan jadwal usang hingga 15 menit. | Diterima — jadwal majelis jarang berubah mendadak. Didokumentasikan ke partner. `cache:clear` tersedia untuk keadaan mendesak. |
| R-5 | **Kalender `islamic-civil` bersifat aritmatis**, tidak mengikuti rukyat. | Tanggal haul atau `hijri_first_week` bisa meleset 1 hari dari penetapan setempat. | Konsisten dengan `HijriService` yang sudah dipakai web, sehingga API dan web **tidak akan saling bertentangan** — dan itu yang lebih penting daripada akurasi mutlak. Didokumentasikan. |
| R-6 | **Key hanya ditampilkan sekali**, tanpa UI admin. | Key hilang berarti harus dibuat ulang dan partner harus deploy ulang. | Diterima demi scope kecil. `api:client-create` bisa dijalankan lagi kapan saja; key lama dicabut. |
| R-7 | **Duplikasi logika visibilitas** antara lapisan API dan Livewire. | Aturan bisa menyimpang seiring waktu. | Filter dibangun di atas scope model yang sudah ada (`publiclyVisible()`), bukan kondisi yang ditulis ulang. `ApiDataLeakTest` mengunci perilakunya. |
| R-8 | **Ekspansi occurrence lebih mahal daripada listing biasa** (iterasi per tanggal × per schedule). | Beban CPU pada jendela 30 hari. | `city_code` wajib menjaga jumlah schedule kecil; hasil di-cache 15 menit; `days` dibatasi maksimal 30. |
| R-9 | **`ScheduleOccurrenceService` menjadi sumber kebenaran kedua** di samping `recurrence_label` bikinan manusia. | Label dan tanggal terhitung bisa bertentangan bila salah satu diubah. | Keduanya dikembalikan bersamaan sehingga perbedaan langsung terlihat; unit test mengunci ekspansi. Menyatukan keduanya adalah pekerjaan lanjutan. |
| R-10 | **Membuka data ke pihak ketiga** memungkinkan scraping seluruh basis data majelis. | Kompetitor dapat menyalin data yang dikumpulkan dengan susah payah. | Data ini memang sudah publik di web. Key per-partner + rate limit + `last_used_at` membuat penyalahgunaan terdeteksi dan dapat dicabut. |

---

## Temuan di Luar Scope

Dicatat agar tidak hilang; masing-masing layak menjadi tugas tersendiri.

1. **`HomeJadwalMajelis` dan `ListJadwalMajelis` tidak menerapkan `publiclyVisible()`** pada daftar mingguan — jadwal kontribusi pending/ditolak tampil ke publik di web. Blok `berkalaSchedules` menerapkannya, jadi ini kemungkinan besar kelalaian, bukan keputusan.
2. **`User\JadwalMajelisController@list` memanggil `->get()`** tanpa scope visibilitas dan tanpa pagination — bocor sekaligus berpotensi lambat seiring data bertambah.
3. **`ListGuru@list` memakai `Teacher::all()`** tanpa scope.
4. **`HomeUpcomingHaul` menduplikasi `HijriService`** dengan panggilan HTTP dan parsing sendiri, tanpa `publiclyVisible()`, serta memakai ejaan `ramadan` sementara backfill migrasi memakai `ramadhan`.
5. **`ext-intl` dipakai tanpa dideklarasikan** di `composer.json` (ikut diperbaiki dalam fitur ini).
6. **`Teacher` tidak punya accessor URL foto** sehingga view memakai `Storage::url()` dan `asset('storage/'.$foto)` secara campur aduk (accessor ditambahkan dalam fitur ini; migrasi view di luar scope).
7. **Skema `teachers` masih menyimpang dari migration.** Ditemukan saat implementasi: `wafat_hijriah_year` ada di produksi tanpa migration apa pun (ikut direkonsiliasi dalam fitur ini), dan sebaliknya kolom string `wafat_hijriah` dibuat migration tetapi **sudah tidak ada di produksi** — kemungkinan di-drop manual. Kolom string itu tidak direkonsiliasi karena tidak ada yang memakainya; keputusan untuk men-drop-nya lewat migration resmi diserahkan ke tugas terpisah.
