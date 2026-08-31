# Poster Acara dengan AI (Gemini "Nano Banana")

**Status Dokumen:** Draft — menunggu persetujuan
**Tanggal:** 2026-08-30
**Author:** Muhammad Khaidir

---

## Latar Belakang

Pemilik majelis dan kontributor wajib mengunggah poster saat menambahkan acara (`events.image`). Kenyataannya sebagian besar pengurus majelis tidak punya desainer maupun perangkat desain, sehingga acara sering dikirim tanpa poster sama sekali atau dengan foto seadanya. Akibatnya kartu acara di beranda dan halaman daftar acara tampil kosong/berantakan, dan pengurus enggan menyebarkan tautan acara ke grup WhatsApp jamaah.

Fitur ini menambahkan tombol **"Buat Poster dengan AI"** pada form tambah dan edit acara. Sistem menyusun prompt dari data acara yang sedang diisi ditambah **gaya visual pilihan** dari daftar tetap, mengirimkannya ke model gambar Gemini, lalu menyimpan hasilnya sebagai poster acara. Pemakaian dibatasi kuota bulanan per pengguna yang dapat diubah admin.

---

## Tujuan

1. Pemilik majelis dan kontributor dapat menghasilkan poster acara langsung dari form tambah/edit acara tanpa alat desain apa pun.
2. Poster yang dihasilkan berformat potret 9:16 sehingga siap disebar sebagai status WhatsApp — kanal penyebaran undangan majelis yang paling umum.
3. Biaya API terkendali lewat kuota bulanan per pengguna yang dikonfigurasi admin.
4. Tidak ada alur moderasi baru: poster mengikuti moderasi acara yang sudah berjalan.

---

## Perilaku Saat Ini

### Form dan controller

Ada **dua** jalur input acara yang keduanya punya field poster:

| Jalur | Controller | View | Pemilik |
|---|---|---|---|
| Kelola acara majelis | `app/Http/Controllers/User/ManageEventController.php` | `resources/views/pages/user/kelola-acara/{tambah-acara,edit-acara}.blade.php` | pemilik `Assembly` |
| Kontribusi acara | `app/Http/Controllers/User/KontribusiAcaraController.php` | `resources/views/pages/kontributor/acara/{create,edit}.blade.php` | role `Kontributor` |

Keduanya memakai `<input type="file" name="image">` dan memproses gambar **inline** (bukan lewat trait):

```php
// ManageEventController.php:70-83
$filename = Str::uuid() . '.webp';
$thumb = Image::read($file)->scaleDown(800)->toWebp(80);
Storage::disk('public')->put('events/' . $filename, $thumb);
$dataToCreate['image'] = 'events/' . $filename;
```

Path yang tersimpan di `events.image` berbentuk **flat**: `events/{uuid}.webp`. Tidak ada varian thumb.

### Tampilan poster

| Lokasi | Baris | Cara render |
|---|---|---|
| Beranda | `resources/views/livewire/home-event.blade.php:14` | `<img class="w-full h-full" src="{{ Storage::url($event->image) }}">` |
| Daftar acara | `resources/views/livewire/list-event.blade.php:40` | idem |
| Detail acara | `resources/views/pages/user/events/detail.blade.php:50` | idem |
| Detail majelis | `resources/views/pages/user/majelis/detail.blade.php:191` | `object-cover` (satu-satunya yang benar) |
| Meta OG | `resources/views/pages/user/events/detail.blade.php:13` | `$seo->image($event->image, 'acara')` |

**Cacat yang sudah ada:** `home-event` dan `list-event` memakai `w-full h-full` **tanpa** `object-fit`, sehingga default CSS `object-fit: fill` — gambar diregangkan mengikuti kotak. Dengan poster arbitrer 800px hal ini sudah terlihat, dan dengan poster 9:16 akan jauh lebih parah. Perbaikan ini **masuk scope** (lihat [Temuan yang Ikut Diperbaiki](#temuan-yang-ikut-diperbaiki)).

### Moderasi

`ManageEventController@store` menandai `moderated_at = now()` hanya untuk Super Admin (baris 65-67); selain itu acara berstatus `pending`. `KontribusiAcaraController@store` menetapkan `status = 'pending'` secara eksplisit. Visibilitas publik ditentukan `Event::scopePubliclyVisible()` (`app/Models/Event.php:78-85`).

### Integrasi eksternal yang sudah ada

`app/Services/OneSignalService.php` dan `app/Services/HijriService.php` adalah pola acuan: class di `app/Services`, konfigurasi dibaca dari `config('services.*')`, `Http::` dengan header, `Log` pada kegagalan. `HijriService.php:30` memakai `Http::timeout(2)` — **`OpenNotebookService` tidak memakai timeout dan pola itu jangan ditiru.**

### Konfigurasi yang diubah admin

Pola single-row: `RewardSetting::current()` (`app/Models/RewardSetting.php:22-30`) dengan `RewardSettingController` (`app/Http/Controllers/Admin/RewardSettingController.php`) pada route `/admin/pengaturan/reward` (`routes/web.php:246-247`).

---

## Expected Behavior

### Skenario 1 — Pemilik majelis membuat poster di form tambah acara

1. User membuka `/kelola-acara-majelis/create`.
2. Mengisi **Nama Acara**, **Kategori**, **Tanggal & Waktu**. Field poster kini punya dua pilihan: unggah berkas, atau blok "Buat Poster dengan AI".
3. Memilih satu **Gaya Visual** dari daftar tetap, lalu menekan **Buat Poster**.
4. Tombol menjadi non-aktif, muncul indikator memuat beserta teks *"Sedang membuat poster, mohon tunggu hingga 1 menit…"*.
5. Setelah berhasil, pratinjau poster 9:16 tampil di dalam form, disertai sisa kuota (*"Sisa kuota bulan ini: 4 dari 5"*) dan tombol **Buat Ulang**.
6. User menekan **Simpan**. Acara tersimpan dengan `events.image` menunjuk poster hasil AI, status `pending` seperti biasa.

### Skenario 2 — Buat ulang

User menekan **Buat Poster** lagi (dengan gaya sama atau berbeda). Poster lama dari sesi yang sama **dihapus dari storage**, poster baru menggantikan pratinjau, kuota berkurang satu lagi.

### Skenario 3 — Form tidak jadi disimpan

User membuat poster lalu menutup halaman tanpa menekan Simpan. Baris riwayat tertinggal dengan `event_id = NULL`. Pada panggilan generate berikutnya oleh user yang sama, riwayat yatim miliknya yang berumur lebih dari 24 jam dibersihkan beserta filenya. Kuota **tetap terhitung terpakai** — pembangkitan sudah menghabiskan biaya API.

### Skenario 4 — Poster di form edit acara

Sama seperti Skenario 1, tetapi `event_id` sudah diketahui. Poster lama acara (baik hasil unggah maupun AI) **dihapus** saat poster baru disimpan lewat tombol **Simpan**, bukan saat generate.

### Skenario 5 — Kontributor

Kontributor mendapat blok yang sama pada `pages/kontributor/acara/create.blade.php` dan `edit.blade.php`. Kuota dihitung per **user**, bukan per majelis, sehingga kontributor yang mengelola banyak majelis tetap memakai satu kuota.

### Skenario 6 — Kuota habis

Blok AI tetap tampil tetapi tombol **Buat Poster** non-aktif, dengan teks: *"Kuota pembuatan poster bulan ini sudah habis (5 dari 5). Kuota diperbarui setiap awal bulan. Anda tetap dapat mengunggah poster sendiri."* Permintaan yang tetap dipaksakan ke endpoint dijawab `422` dan tidak memanggil Gemini.

### Skenario 7 — Gemini gagal, lambat, atau menolak

- Timeout, error jaringan, HTTP non-2xx, atau respons tanpa data gambar → baris riwayat dicatat `status = 'failed'` beserta ringkasan error, **kuota tidak terpotong**.
- User melihat alert merah: *"Pembuatan poster gagal. Silakan coba lagi beberapa saat atau unggah poster sendiri."*
- Seluruh isian form tetap utuh (nama, kategori, tanggal tidak hilang).

### Skenario 8 — Poster AI dan unggahan manual bersamaan

Jika user sudah membuat poster AI lalu **juga** memilih berkas di input unggah, **berkas unggahan menang**. Poster AI yang tidak terpakai menjadi yatim dan dibersihkan sesuai Skenario 3.

### Skenario 9 — Moderasi

Tidak ada perubahan. Admin melihat poster hasil AI di antrean moderasi acara persis seperti poster unggahan. Tidak ada penanda AI yang tampil ke publik; penandanya hanya ada di tabel riwayat untuk audit internal.

---

## Role & Authorization

| Aktor | Boleh generate? | Kena kuota? |
|---|---|---|
| Pemilik majelis (punya baris `assemblies` dengan `user_id` = dirinya) | Ya | Ya |
| Role `Kontributor` | Ya | Ya |
| User terverifikasi tanpa majelis dan tanpa role Kontributor | Tidak (`403`) | — |
| Super Admin lewat `/admin/*` | Tidak — di luar scope | — |
| Tamu / belum verifikasi | Tidak (middleware `auth:sanctum` + `verified`) | — |

Pemeriksaan kelayakan dilakukan di satu tempat, mengikuti pola pengecekan manual yang sudah dipakai controller lain:

```php
private function assertEligible(User $user): void
{
    $punyaMajelis = Assembly::where('user_id', $user->id)->exists();

    if (! $punyaMajelis && ! $user->hasRole('Kontributor')) {
        abort(403, 'Fitur poster AI hanya untuk pengurus majelis dan kontributor.');
    }
}
```

**Otorisasi saat melampirkan poster ke acara:** `generation_id` yang dikirim form divalidasi harus (a) milik `Auth::id()`, (b) berstatus `success`, dan (c) `event_id` masih `NULL` atau sama dengan acara yang sedang diedit. Tanpa ini, user lain dapat mencantumkan `generation_id` orang lain dan mencuri posternya.

---

## Data Model

### Tabel baru — `event_poster_generations`

Migration baru, tidak mengubah tabel lama.

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigIncrements | |
| `user_id` | foreignId → `users`, cascade | pemilik generate, dasar perhitungan kuota |
| `event_id` | foreignId nullable → `events`, nullOnDelete | `NULL` sampai form disubmit |
| `assembly_id` | foreignId nullable → `assemblies`, nullOnDelete | konteks majelis, untuk audit |
| `style` | string(50) | kunci gaya, mis. `kaligrafi_emas` |
| `prompt` | text | prompt final yang dikirim ke Gemini (audit) |
| `model` | string(100) | id model yang dipakai, disalin dari config |
| `image_path` | string(255) nullable | path `large`, mis. `events/large/{uuid}.webp` |
| `status` | string(20) | `success` \| `failed` |
| `error_message` | text nullable | ringkasan kegagalan (maks 1000 karakter saat disimpan) |
| `timestamps` | | `created_at` dipakai untuk jendela kuota bulanan |

Index: `['user_id', 'status', 'created_at']` untuk query kuota, dan `['event_id']`.

### Tabel baru — `poster_settings` (single-row)

Mengikuti pola `reward_settings`.

| Kolom | Tipe | Default | Keterangan |
|---|---|---|---|
| `id` | bigIncrements | | |
| `monthly_quota` | unsignedInteger | `5` | generate sukses per user per bulan |
| `is_active` | boolean | `true` | matikan fitur tanpa deploy |
| `timestamps` | | | |

```php
// app/Models/PosterSetting.php
public static function current(): self
{
    return static::firstOrCreate([], [
        'monthly_quota' => 5,
        'is_active' => true,
    ]);
}
```

### Tabel `events` — tidak berubah

Tidak ada kolom baru. `events.image` tetap satu-satunya sumber poster. Path poster AI berbentuk `events/large/{uuid}.webp`, berbeda dari path unggahan manual yang flat `events/{uuid}.webp`. Perbedaan ini diserap oleh accessor pada model, bukan oleh kolom baru — sekaligus menjaga backward compatibility untuk acara lama.

```php
// app/Models/Event.php — accessor baru
public function getImageThumbUrlAttribute(): ?string
{
    if (! $this->image) {
        return null;
    }

    // Poster AI disimpan dua varian (large/thumb); unggahan lama hanya satu file flat.
    return Storage::url(
        str_contains($this->image, '/large/')
            ? str_replace('/large/', '/thumb/', $this->image)
            : $this->image
    );
}

public function getImageLargeUrlAttribute(): ?string
{
    return $this->image ? Storage::url($this->image) : null;
}
```

### Perhitungan kuota

```php
$terpakai = EventPosterGeneration::where('user_id', $userId)
    ->where('status', 'success')
    ->where('created_at', '>=', Carbon::now()->startOfMonth())
    ->count();
```

`Carbon::now()` mengikuti `config('app.timezone') = 'Asia/Makassar'` (`config/app.php:73`), jadi batas bulan mengikuti WITA tanpa konfigurasi tambahan.

---

## Integrasi Gemini

### Konfigurasi

`.env.example` (nilai sebenarnya hanya di `.env`, tidak di-commit):

```
# Gemini image generation ("Nano Banana") untuk poster acara
GEMINI_API_KEY=
GEMINI_IMAGE_MODEL=gemini-2.5-flash-image
GEMINI_TIMEOUT=60
```

`config/services.php` — blok baru mengikuti gaya blok `open_notebook` yang sudah ada:

```php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),
    'timeout' => (int) env('GEMINI_TIMEOUT', 60),
],
```

### Service baru — `app/Services/GeminiPosterService.php`

Bentuk permintaan (Generative Language API, autentikasi API key sederhana):

```
POST https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent
Header: x-goog-api-key: {GEMINI_API_KEY}
Body:
{
  "contents": [{"parts": [{"text": "<prompt>"}]}],
  "generationConfig": {
    "responseModalities": ["IMAGE"],
    "imageConfig": {"aspectRatio": "9:16"}
  }
}
```

Gambar diambil dari `candidates[0].content.parts[*].inlineData.data` (base64) dengan `mimeType` berupa `image/png`.

> **Perlu diverifikasi saat implementasi.** Nama model (`gemini-2.5-flash-image`), dukungan `generationConfig.imageConfig.aspectRatio`, dan bentuk `responseModalities` harus dicek terhadap dokumentasi Google yang berlaku saat implementasi — API gambar Gemini masih berubah. Karena itu **rasio dan dimensi akhir tidak boleh bergantung pada API**: apa pun yang dikembalikan Gemini, server tetap melakukan `cover(1080, 1920)` sehingga keluaran selalu tepat 1080×1920. Jika `imageConfig` ternyata tidak didukung, permintaan tetap valid tanpa blok itu dan rasio dijaga oleh crop server.

Aturan panggilan:

```php
$response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
    ->timeout($this->timeout)      // wajib — jangan tiru OpenNotebookService yang tanpa timeout
    ->connectTimeout(10)
    ->post($url, $payload);
```

Kegagalan **tidak melempar exception ke atas sebagai error 500**; service mengembalikan hasil bermakna (`null` atau objek hasil dengan pesan error) dan controller menerjemahkannya menjadi alert Indonesia. Semua kegagalan dicatat via `Log::warning` mengikuti `OneSignalService`. **API key tidak boleh ikut tercatat di log.**

### Penyusunan prompt

Prompt **selalu disusun server**; pengguna tidak pernah mengirim teks prompt. Daftar gaya adalah konstanta:

```php
public const STYLES = [
    'kaligrafi_emas'  => 'Gaya Kaligrafi Emas',
    'klasik_banjar'   => 'Gaya Klasik Banjar',
    'minimalis_hijau' => 'Gaya Minimalis Hijau',
    'hitam_elegan'    => 'Gaya Hitam Elegan',
];
```

Kunci gaya divalidasi dengan `Rule::in(array_keys(GeminiPosterService::STYLES))`. Prompt digabung dari template sistem berbahasa Indonesia + nama acara + kategori + tanggal (diformat WITA) + nama majelis + lokasi, dengan pagar berikut:

- Setiap nilai dari pengguna di-`strip_tags`, dibuang baris barunya, dan dipotong maksimal 120 karakter sebelum masuk prompt. Ini mencegah pengguna menyelipkan instruksi lewat "nama acara".
- Template sistem menyatakan secara eksplisit: poster acara keagamaan Islam bergaya sopan, tanpa gambar makhluk bernyawa, tanpa wajah manusia, tanpa simbol agama lain.
- Prompt final disimpan di kolom `prompt` sehingga admin dapat mengaudit poster yang bermasalah.

### Penyimpanan hasil

```php
// Gemini -> base64 -> Intervention Image -> dua varian webp
$binary = base64_decode($base64, true);

$large = Image::read($binary)->cover(1080, 1920)->toWebp(80);
$thumb = Image::read($binary)->cover(360, 640)->toWebp(80);

Storage::disk('public')->put("events/large/{$filename}", (string) $large);
Storage::disk('public')->put("events/thumb/{$filename}", (string) $thumb);
```

Trait `HandlesImageUploads` **tidak dipakai** karena tanda tangannya menerima `UploadedFile`, sedangkan sumber di sini adalah string biner. Logika dua-varian ditulis di dalam `GeminiPosterService` agar tidak menambah cara keempat melakukan upload gambar di codebase ini.

---

## Implementasi

### File yang Dibuat

| File | Isi |
|---|---|
| `app/Services/GeminiPosterService.php` | konstanta gaya, penyusun prompt, panggilan HTTP, konversi + simpan dua varian |
| `app/Models/EventPosterGeneration.php` | model riwayat, konstanta status, scope `successThisMonth()` |
| `app/Models/PosterSetting.php` | single-row `current()` mengikuti `RewardSetting` |
| `app/Http/Controllers/User/EventPosterController.php` | endpoint `store` (generate) |
| `app/Http/Controllers/Admin/PosterSettingController.php` | `index` + `update` pengaturan kuota |
| `database/migrations/xxxx_create_event_poster_generations_table.php` | tabel riwayat |
| `database/migrations/xxxx_create_poster_settings_table.php` | tabel pengaturan |
| `resources/views/components/poster-ai-generator.blade.php` | blok UI yang dipakai keempat form |
| `resources/views/pages/admin/poster/index.blade.php` | form pengaturan kuota admin |
| `tests/Feature/EventPosterGenerationTest.php` | feature test |

### File yang Diubah

| File | Perubahan |
|---|---|
| `config/services.php` | blok `gemini` |
| `.env.example` | tiga variabel `GEMINI_*` |
| `routes/web.php` | 1 route user + 2 route admin |
| `app/Models/Event.php` | accessor `image_thumb_url` dan `image_large_url`; relasi `posterGenerations()` |
| `app/Http/Controllers/User/ManageEventController.php` | `store()` dan `update()` menerima `generation_id` |
| `app/Http/Controllers/User/KontribusiAcaraController.php` | idem |
| `resources/views/pages/user/kelola-acara/tambah-acara.blade.php` | sisipkan komponen blok AI |
| `resources/views/pages/user/kelola-acara/edit-acara.blade.php` | idem |
| `resources/views/pages/kontributor/acara/create.blade.php` | idem |
| `resources/views/pages/kontributor/acara/edit.blade.php` | idem |
| `resources/views/livewire/home-event.blade.php` | pakai `image_thumb_url` + `object-cover` |
| `resources/views/livewire/list-event.blade.php` | idem |
| `resources/views/pages/user/events/detail.blade.php` | pakai `image_large_url` |
| `resources/views/components/app/sidebar.blade.php` | menu "Pengaturan Poster AI" |

### Route

```php
// routes/web.php — di dalam grup ['noindex', 'auth:sanctum', 'verified']
Route::post('/poster-acara/generate', [EventPosterController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('poster-acara.generate');

// di dalam grup admin (is_admin), berdampingan dengan /pengaturan/reward
Route::get('/pengaturan/poster', [PosterSettingController::class, 'index'])->name('admin.poster-settings.index');
Route::put('/pengaturan/poster', [PosterSettingController::class, 'update'])->name('admin.poster-settings.update');
```

`throttle:5,1` adalah pagar teknis terhadap klik beruntun dan berlaku **di atas** kuota bulanan, bukan menggantikannya.

### Alur generate (endpoint `POST /poster-acara/generate`)

1. Middleware `auth:sanctum` + `verified` + `throttle:5,1`.
2. `assertEligible()` — pemilik majelis atau Kontributor, selain itu `403`.
3. `PosterSetting::current()->is_active` — jika `false`, `422` dengan pesan fitur sedang nonaktif.
4. Validasi: `nama` (required, max 255), `kategori` (required, max 255), `tanggal` (required, date), `gaya` (required, in daftar gaya), `assembly_id` (nullable, exists), `event_id` (nullable, exists).
5. Bersihkan riwayat yatim milik user ini yang berumur > 24 jam (baris + file). Dilakukan **oportunistik di sini**, bukan lewat scheduler, karena tidak ada bukti cron/worker berjalan di produksi.
6. Hitung kuota terpakai bulan ini; jika ≥ `monthly_quota`, `422` beserta angka terpakai/kuota.
7. Susun prompt, panggil `GeminiPosterService::generate()`.
8. **Gagal** → simpan baris `status = 'failed'` + `error_message`, kembalikan `502` dengan pesan Indonesia. Kuota tidak berkurang karena hanya baris `success` yang dihitung.
9. **Sukses** → simpan dua varian webp, simpan baris `status = 'success'` dengan `image_path` = path `large`, kembalikan JSON `{ generation_id, preview_url, sisa_kuota }`.

### Alur melampirkan poster ke acara

Blok UI menaruh `<input type="hidden" name="generation_id">` yang terisi setelah generate berhasil. Di `ManageEventController@store` / `@update` dan `KontribusiAcaraController@store` / `@update`, **setelah** penanganan `$request->hasFile('image')` yang sudah ada:

```php
// Berkas unggahan selalu menang atas poster AI (Skenario 8).
if (! $request->hasFile('image') && $request->filled('generation_id')) {
    $generation = EventPosterGeneration::where('id', $request->input('generation_id'))
        ->where('user_id', Auth::id())
        ->where('status', EventPosterGeneration::STATUS_SUCCESS)
        ->first();

    if ($generation && ($generation->event_id === null || $generation->event_id === $event->id)) {
        $data['image'] = $generation->image_path;
        $generation->event_id = $event->id;
        $generation->save();
    }
}
```

Pada `update()`, poster lama dihapus dengan aturan yang sama seperti sekarang, ditambah penghapusan varian `thumb` bila path mengandung `/large/`.

### Blok UI (`resources/views/components/poster-ai-generator.blade.php`)

Komponen Blade biasa dengan Alpine.js — **bukan** komponen Livewire, karena form tambah/edit acara adalah form HTML biasa dan menyisipkan Livewire ke dalamnya akan mencampur dua model state.

- Alpine mengambil nilai `nama`, `kategori`, `tanggal` dari input yang sedang diisi, mengirim `fetch()` ber-CSRF ke endpoint generate.
- Selama menunggu: tombol `disabled`, spinner, teks *"Sedang membuat poster, mohon tunggu hingga 1 menit…"*.
- Sukses: `<img>` pratinjau rasio 9:16, isi hidden `generation_id`, perbarui teks sisa kuota, tampilkan tombol **Buat Ulang**.
- Gagal: alert merah, tombol aktif kembali, isian form tidak tersentuh.
- Kuota habis saat halaman dimuat: tombol sudah `disabled` dengan pesan Skenario 6.

---

## Temuan yang Ikut Diperbaiki

Dua hal berikut menghalangi fitur ini mencapai tujuannya, sehingga masuk scope:

1. **`object-fit` hilang** di `livewire/home-event.blade.php:13` dan `livewire/list-event.blade.php:39`. Tanpa `object-cover`, poster 9:16 akan diregangkan menjadi kotak dan hasilnya lebih buruk daripada tanpa poster. Perbaikan: tambahkan `object-cover` dan ganti sumber ke `$event->image_thumb_url`.
2. **Beranda memuat gambar ukuran penuh.** Setelah poster 9:16 masuk, kartu acara di beranda akan menarik file 1080×1920. Mengarahkan kedua daftar ke varian `thumb` menyelesaikannya, dan accessor tetap mengembalikan file flat untuk acara lama sehingga tidak ada regresi.

---

## Yang Tidak Termasuk Scope

- **Poster untuk Super Admin** di `/admin/*` (`app/Http/Controllers/EventController.php`). Admin tetap mengunggah manual.
- **Poster untuk entitas selain acara** — majelis, guru/manaqib, jadwal, wirid, tulisan tidak mendapat fitur ini.
- **Prompt bebas**. Pengguna tidak dapat mengetik prompt; hanya memilih gaya. Field catatan tambahan tidak dibuat.
- **Beberapa varian sekali generate.** Satu klik = satu poster = satu kuota.
- **Antrean/queue dan job asinkron.** Generate berjalan sinkron. Tidak ada `app/Jobs` baru, tidak ada ketergantungan pada `queue:work`.
- **Editor poster.** Tidak ada pemindahan teks, penggantian warna, atau penambahan logo setelah gambar diterima.
- **Watermark dan label "dibuat dengan AI" yang tampil publik.** Penandaan hanya di tabel riwayat untuk audit internal.
- **Moderasi terpisah untuk poster.** Poster ikut moderasi acara yang sudah ada.
- **Perubahan alur moderasi, XP Khidmah, atau `Contribution`.** Membuat poster tidak memberi XP.
- **Backfill poster untuk acara lama.** Tidak ada data lama yang disentuh.
- **OG image khusus rasio 1.91:1.** `$seo->image()` tetap memakai `events.image` apa adanya. Poster 9:16 akan dipotong oleh WhatsApp/Facebook saat dibagikan — diterima sebagai konsekuensi dan dicatat sebagai pekerjaan lanjutan.
- **Perbaikan IDOR pada `app/Livewire/User/AcaraMajelis.php:24-40`.** Cacat nyata (siapa pun yang login dapat menghapus acara mana pun), tetapi tidak berkaitan dengan fitur ini. ~~Harus ditangani sebagai perbaikan keamanan tersendiri.~~ **Sudah diperbaiki terpisah pada 2026-08-30** — lihat [Perbaikan Keamanan Terkait](#perbaikan-keamanan-terkait).

---

## Acceptance Criteria

- [ ] **AC-1**: Pemilik majelis pada `/kelola-acara-majelis/create` dapat memilih gaya, menekan **Buat Poster**, dan melihat pratinjau poster 9:16 tanpa meninggalkan halaman.
- [ ] **AC-2**: Menekan **Simpan** setelah generate menyimpan acara dengan `events.image` menunjuk poster hasil AI, dan baris riwayat terkait terisi `event_id` acara tersebut.
- [ ] **AC-3**: Berkas poster tersimpan dalam dua varian — `events/large/{uuid}.webp` berukuran tepat 1080×1920 dan `events/thumb/{uuid}.webp` berukuran 360×640.
- [ ] **AC-4**: Role `Kontributor` mendapat blok yang sama pada form kontribusi acara dan dapat menyelesaikan alur yang sama.
- [ ] **AC-5**: User terverifikasi yang tidak memiliki majelis dan bukan Kontributor menerima `403` saat memanggil endpoint generate.
- [ ] **AC-6**: Setelah generate sukses sebanyak `monthly_quota` kali dalam bulan berjalan, tombol non-aktif dan permintaan langsung ke endpoint dijawab `422` **tanpa** memanggil Gemini.
- [ ] **AC-7**: Generate yang gagal (timeout / HTTP error / respons tanpa gambar) tidak mengurangi kuota, mencatat baris `status = 'failed'`, dan menampilkan alert Indonesia tanpa menghilangkan isian form.
- [ ] **AC-8**: Admin dapat mengubah `monthly_quota` dan `is_active` di `/admin/pengaturan/poster`; nilai baru langsung berlaku pada generate berikutnya.
- [ ] **AC-9**: Saat `is_active = false`, endpoint menolak dengan `422` dan blok UI tidak menawarkan tombol generate.
- [ ] **AC-10**: `generation_id` milik user lain yang dikirim pada form tidak diterima; `events.image` tidak berubah dan tidak ada error 500.
- [ ] **AC-11**: Berkas unggahan manual mengalahkan poster AI bila keduanya dikirim dalam satu submit.
- [ ] **AC-12**: Acara lama dengan path flat `events/{uuid}.webp` tetap tampil normal di beranda, daftar acara, dan halaman detail — accessor mengembalikan path flat, bukan `/thumb/` yang tidak ada.
- [ ] **AC-13**: Poster hasil AI mengikuti moderasi acara: acara berstatus `pending` sampai admin menyetujui, dan tidak ada label AI yang tampil ke pengunjung publik.
- [ ] **AC-14**: `GEMINI_API_KEY` tidak pernah muncul di log, pesan error, maupun respons endpoint.
- [ ] **AC-15**: Semua panggilan HTTP ke Gemini memakai `timeout` dan `connectTimeout` eksplisit.

---

## Test Plan (`tests/Feature/EventPosterGenerationTest.php`)

Cakupan **inti** sesuai keputusan: jalur sukses, otorisasi, kuota. Skenario error API diverifikasi manual pada langkah E2E. Mengikuti pola `tests/Feature/LibraryPurchaseTest.php` — `RefreshDatabase`, `Storage::fake('public')`, `Http::fake()`. **Tidak ada panggilan API sungguhan.**

| Test | Skenario |
|---|---|
| `test_pemilik_majelis_dapat_generate_poster` | `Http::fake` mengembalikan base64 gambar valid → assert `200`, dua file ada di disk `public`, baris `success` tercatat |
| `test_kontributor_dapat_generate_poster` | user role `Kontributor` tanpa majelis → assert `200` |
| `test_user_tanpa_majelis_dan_bukan_kontributor_ditolak` | assert `403`, `Http::assertNothingSent()` |
| `test_generate_ditolak_saat_kuota_habis` | seed `monthly_quota` baris `success` bulan ini → assert `422`, `Http::assertNothingSent()` |
| `test_baris_gagal_tidak_mengurangi_kuota` | `Http::fake` mengembalikan `500` → assert `502`, baris `failed`, generate berikutnya tetap diizinkan |
| `test_poster_terlampir_ke_acara_saat_submit` | generate lalu POST `kelola-acara-majelis.store` dengan `generation_id` → assert `events.image` = path `large`, `event_id` terisi |
| `test_generation_id_milik_user_lain_diabaikan` | assert `events.image` tidak terisi dari generation itu, tidak ada `500` |
| `test_accessor_thumb_mengembalikan_path_flat_untuk_acara_lama` | `events.image = 'events/abc.webp'` → assert URL tidak mengandung `/thumb/` |

Catatan lingkungan: PHP CLI di mesin pengembangan ini tidak memuat `pdo_sqlite`; jalankan test dengan konfigurasi PHP yang memuat ekstensi tersebut.

---

## Verifikasi End-to-End

Prasyarat: `GEMINI_API_KEY` terisi di `.env`, `php artisan config:clear`, `php artisan migrate`, `npm run build`.

1. Login sebagai Super Admin, buka `/admin/pengaturan/poster`. Verifikasi nilai awal `monthly_quota = 5`, `is_active = true`. Ubah kuota menjadi **2**, simpan, verifikasi pesan sukses.
2. Login sebagai pemilik majelis. Buka `/kelola-acara-majelis/create`.
3. Isi Nama Acara, Kategori, Tanggal (≥ 7 hari ke depan). Pilih gaya **Kaligrafi Emas**, tekan **Buat Poster**.
4. Verifikasi: tombol non-aktif dan spinner tampil; setelah selesai muncul pratinjau potret, teks sisa kuota berbunyi *"1 dari 2"*.
5. Periksa `storage/app/public/events/large/` dan `.../thumb/` — dua berkas baru ada. Buka berkas `large`, verifikasi dimensinya tepat 1080×1920.
6. Tekan **Simpan**. Verifikasi redirect ke daftar acara dengan pesan sukses, dan poster tampil pada kartu acara **tanpa teregang** (proporsional, terpotong rapi).
7. Buka halaman edit acara tersebut, generate poster baru dengan gaya berbeda, simpan. Verifikasi poster berganti dan berkas `large` + `thumb` yang lama terhapus dari storage.
8. Ulangi generate sekali lagi. Verifikasi kuota kini habis: tombol non-aktif dengan pesan *"Kuota pembuatan poster bulan ini sudah habis (2 dari 2)…"*.
9. Dengan DevTools, kirim `POST /poster-acara/generate` secara langsung. Verifikasi respons `422` dan tidak ada berkas baru di storage.
10. Login sebagai user biasa (bukan pemilik majelis, bukan Kontributor), kirim permintaan yang sama. Verifikasi `403`.
11. Login sebagai Kontributor, buka form kontribusi acara, generate dan simpan. Verifikasi acara masuk dengan status `pending` dan poster terpasang.
12. Login sebagai Super Admin, buka antrean moderasi acara. Verifikasi poster AI tampil sama seperti poster unggahan, tanpa label khusus. Setujui acara.
13. Buka halaman detail acara sebagai pengunjung anonim. Verifikasi poster tampil penuh dan **tidak ada** label "dibuat dengan AI".
14. **Uji kegagalan:** ubah sementara `GEMINI_IMAGE_MODEL` ke nilai yang tidak ada, `php artisan config:clear`, lalu generate. Verifikasi alert merah muncul, isian form tetap utuh, dan sisa kuota **tidak berkurang**. Kembalikan nilai konfigurasi.
15. **Uji regresi acara lama:** buka beranda dan `/event`. Verifikasi acara lama yang posternya berpath flat tetap tampil (tidak ada gambar rusak).
16. Periksa `storage/logs/laravel.log`. Verifikasi kegagalan langkah 14 tercatat dan **`GEMINI_API_KEY` tidak muncul di dalamnya**.

---

## Risiko & Trade-off

| Risiko | Dampak | Mitigasi |
|---|---|---|
| **Teks di dalam gambar salah** — model gambar kerap salah mengeja bahasa Indonesia dan sangat sering merusak tulisan Arab | Poster tersebar dengan nama guru/majelis salah eja, atau lafaz Arab yang tidak bermakna — masalah kredibilitas serius untuk platform dakwah | Tidak ada mitigasi teknis pada pendekatan "poster jadi dari AI". Yang tersedia: moderasi admin sebelum acara publik, dan peringatan di UI agar pengguna memeriksa ejaan sebelum menyimpan. **Ini trade-off utama yang diterima secara sadar**; pendekatan overlay teks di server adalah jalan keluarnya jika masalah ini terbukti mengganggu |
| **Tanggal pada poster tidak sesuai** | Jamaah datang di hari yang salah | Sama seperti di atas: pemeriksaan manual oleh pembuat + moderasi admin |
| **Request PHP tertahan 60 detik** (mode sinkron) | Pada VPS < 4 GB RAM, beberapa generate bersamaan dapat menghabiskan worker PHP-FPM dan memperlambat seluruh situs | `throttle:5,1` per user, kuota bulanan yang ketat, `timeout` + `connectTimeout` eksplisit. Perlu dipastikan `max_execution_time` PHP > `GEMINI_TIMEOUT`, jika tidak proses mati sebelum timeout HTTP tercapai |
| **Biaya API di luar dugaan** | Tagihan Google membengkak | Kuota per user + `is_active` sebagai sakelar mati darurat tanpa deploy. Belum ada plafon biaya global lintas-user — pertimbangkan menambahkannya bila jumlah pengguna tumbuh |
| **Prompt injection lewat nama acara** | Poster berisi konten menyimpang | Nilai pengguna di-`strip_tags`, dibuang baris barunya, dipotong 120 karakter; prompt sistem menetapkan pagar konten; poster tetap melewati moderasi acara |
| **API Gemini berubah** — nama model dan bentuk `imageConfig` masih bergerak | Fitur mati total setelah perubahan di sisi Google | Model dibaca dari env; crop server menjamin dimensi akhir tanpa bergantung pada `imageConfig`; kegagalan tercatat dan tidak memotong kuota |
| **Berkas yatim menumpuk** | Storage terisi poster yang tidak pernah dipakai | Pembersihan oportunistik > 24 jam pada tiap panggilan generate. Tidak sempurna untuk user yang tidak pernah kembali — jika perlu, tambahkan command artisan terjadwal kemudian |
| **Poster 9:16 sebagai OG image** | Pratinjau tautan di WhatsApp/Facebook terpotong | Diterima; perbaikan OG khusus dicatat sebagai pekerjaan lanjutan |
| **`$guarded = []` pada `Event`** | Kolom apa pun dapat diisi dari request | Fitur ini sengaja **tidak** menambah kolom pada `events`, sehingga tidak memperbesar permukaan risiko |
| **`monthly_quota` dihitung per user, bukan per majelis** | Kontributor pengelola banyak majelis merasa kuotanya sempit | Keputusan sadar demi kesederhanaan; admin dapat menaikkan kuota global |

---

## Catatan Implementasi (2026-08-30)

Implementasi selesai. Penyimpangan dari rancangan di atas, beserta alasannya:

1. **`assertEligible()` tidak jadi ditulis di controller.** Kelayakan (`isEligible()`), ringkasan kuota (`quotaFor()`), pengambilan poster untuk dilampirkan (`claimGeneration()`), dan penghapusan poster (`deletePoster()`) semuanya berada di `GeminiPosterService`. Alasannya: keempat hal itu dibutuhkan oleh **tiga** controller dan **empat** view; menaruhnya di controller berarti menyalin logika yang sama ke `ManageEventController`, `KontribusiAcaraController`, dan `EventPosterController`. Satu rumah untuk fitur ini lebih aman daripada tiga salinan.
2. **Halaman detail acara memakai `object-contain`, bukan sekadar ganti sumber gambar.** Markup lama (`max-h-[28rem] object-cover`) memotong poster potret menjadi pita horizontal — poster 9:16 menjadi tidak terbaca. Diubah menjadi `max-h-[36rem] object-contain` dengan latar abu, sehingga poster tampil utuh dan poster lanskap lama tetap wajar.
3. **Dua test tambahan di luar tabel Test Plan**: `test_form_tambah_acara_menampilkan_blok_poster_ai` dan `test_form_tambah_acara_menyembunyikan_blok_untuk_user_tidak_berhak`. Keduanya melindungi AC-1 dan AC-5 pada lapisan render, yang tidak tersentuh oleh test endpoint. Ditambahkan pula `test_generate_ditolak_saat_fitur_dinonaktifkan` untuk AC-9.
4. **Fixture test majelis harus mengisi `guru`, `maps`, dan `status`.** Skema SQLite yang dibangun dari migration memiliki kolom `assemblies.guru` NOT NULL yang tidak ada di skema MySQL produksi. Ini bukan masalah fitur ini, melainkan gejala penyimpangan skema yang sudah diketahui.
5. **Kuota diperiksa setelah pembersihan berkas yatim**, bukan sebelumnya, agar berkas yang sudah kedaluwarsa tidak sempat menghalangi.

Hasil pemeriksaan:

| Pemeriksaan | Hasil |
|---|---|
| `phpunit --filter=EventPosterGenerationTest` | 11 test, 38 assertion, **lolos** |
| Seluruh suite sebelum perubahan | 444 test — 3 error, 14 failure |
| Seluruh suite setelah perubahan | 453 test — 3 error, 14 failure (**nama kegagalan identik; nol regresi**) |
| `pint --test` pada seluruh berkas baru | lolos |
| `npm run build` | lolos |
| `php artisan view:cache` | seluruh Blade ter-compile |
| `php artisan migrate` | dua tabel baru dibuat (aditif) |

Kegagalan yang tersisa (`ScheduleNoteTest`, `TeacherTest`, `AuthenticationTest`, `KontributorAtribusiTest`, `LibraryPodcastTest`, dll.) sudah ada sebelum fitur ini dan tidak berkaitan dengannya.

**Belum diverifikasi:** langkah [Verifikasi End-to-End](#verifikasi-end-to-end) membutuhkan `GEMINI_API_KEY` sungguhan. Nama model dan dukungan `generationConfig.imageConfig.aspectRatio` baru dapat dipastikan pada panggilan nyata pertama; bila `imageConfig` ditolak, hapus blok itu dari `GeminiPosterService::generate()` — rasio akhir tetap dijamin oleh `cover(1080, 1920)`.

---

## Perbaikan Keamanan Terkait (2026-08-30)

Dikerjakan terpisah dari fitur poster, atas permintaan langsung.

**Masalah.** `App\Livewire\User\AcaraMajelis::deleteEvent()` memanggil `Event::find($this->event_id_to_delete)->delete()`. `event_id_to_delete` diisi dari `confirmDelete($eventId)` yang dipanggil dari klien, sehingga siapa pun yang login dan dapat memuat halaman `/kelola-acara-majelis` mampu menghapus acara majelis mana pun dengan mengirim id sembarang — termasuk acara yang sudah disetujui dan tayang publik. `render()` sudah men-scope daftarnya ke majelis milik pengguna, jadi tampilan dan penghapusan memakai dua definisi kepemilikan yang berbeda.

**Perbaikan.** Satu definisi "acara milik saya" dipakai untuk menampilkan **dan** menghapus:

```php
private function ownedEvents()
{
    return Event::whereHas('assembly', function ($assemblyQuery) {
        $assemblyQuery->where('user_id', Auth::id());
    });
}
```

`deleteEvent()` kini memanggil `$this->ownedEvents()->find(...)`; `render()` memakai method yang sama, menghilangkan duplikasi closure yang sebelumnya ditulis dua kali.

**`app/Livewire/Acara.php` sengaja tidak diubah.** Komponen itu hanya dirender dari `admin/event` yang berada di balik middleware `is_admin`, dan Super Admin memang berwenang menghapus acara siapa pun — menambahkan scope kepemilikan di sana justru akan merusak fungsinya.

**Test:** `tests/Feature/AcaraMajelisDeleteTest.php` — 4 test. Diverifikasi bermakna: dua test kepemilikan **gagal** ketika dijalankan terhadap versi rentan, dan lolos setelah perbaikan.

| Test | Skenario |
|---|---|
| `test_pemilik_majelis_dapat_menghapus_acaranya_sendiri` | jalur normal tetap berfungsi |
| `test_pengguna_tidak_dapat_menghapus_acara_majelis_lain` | pemilik majelis A mengirim id acara majelis B → acara tetap ada |
| `test_pengguna_tanpa_majelis_tidak_dapat_menghapus_acara_siapa_pun` | user tanpa majelis → acara tetap ada |
| `test_daftar_hanya_memuat_acara_milik_sendiri` | daftar dan `events_count` ter-scope |

**Catatan berkas poster.** Menghapus acara tidak menghapus berkas posternya — perilaku lama yang tidak diubah. Untuk poster hasil AI, `event_poster_generations.event_id` menjadi `NULL` karena `nullOnDelete`, sehingga berkasnya ikut terbersihkan oleh pembersihan yatim (> 24 jam) pada panggilan generate berikutnya.

---

## Pekerjaan Lanjutan (di luar scope, dicatat agar tidak hilang)

1. OG image rasio 1.91:1 khusus untuk acara berposter potret.
2. Plafon biaya global lintas-user untuk API Gemini.
3. Pertimbangkan pendekatan overlay teks di server bila kesalahan ejaan pada poster terbukti sering terjadi.
4. Hapus berkas poster saat acara dihapus, alih-alih menunggu pembersihan yatim.
