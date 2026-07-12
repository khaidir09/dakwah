# Foto Bersama Guru pada Manaqib Kontributor

**Status Dokumen:** Draft — menunggu persetujuan
**Tanggal:** 2026-07-12
**Author:** Muhammad Khaidir

---

## Latar Belakang

Kontributor yang menulis manaqib seorang guru sering kali pernah bertemu langsung dengan almarhum. Foto pertemuan itu adalah bukti paling kuat bahwa penulis punya kedekatan nyata dengan sang guru — bukan sekadar menyalin dari internet. Saat ini tidak ada tempat untuk menaruh foto tersebut: form kontributor hanya punya satu field `foto` yang dipakai sebagai foto profil/hero guru.

Fitur ini menambahkan **satu foto bersama + keterangan wajib** pada data guru yang dikirim kontributor, ditampilkan publik di halaman manaqib dan halaman guru sebagai penguat kredibilitas.

Sekaligus, field **biografi pada form kontributor dinaikkan dari `<textarea>` polos menjadi editor WYSIWYG** seperti yang sudah dipakai admin — manaqib adalah tulisan panjang, dan tanpa paragraf, penebalan, serta daftar, hasilnya tidak terbaca.

Dalam proses penulisan spesifikasi ini ditemukan beberapa cacat yang menghalangi tujuan fitur, sehingga **ikut masuk scope** (lihat [Temuan yang Ikut Diperbaiki](#temuan-yang-ikut-diperbaiki)).

---

## Tujuan

1. Kontributor dapat melampirkan satu foto kebersamaannya dengan guru yang ia tulis manaqibnya, disertai keterangan.
2. Pembaca melihat foto itu di halaman manaqib/guru sebagai penanda bahwa penulisnya memang mengenal sang guru.
3. Admin dapat menilai foto sebelum menyetujui, dan dapat mencabut foto yang bermasalah tanpa harus menolak seluruh manaqib.
4. Kontributor dapat menulis manaqib berformat (paragraf, tebal/miring, daftar, tautan) lewat editor WYSIWYG, dengan hasil yang **tidak hilang** saat dirender publik.

---

## Perilaku Saat Ini

### Model dan tabel

`biographies` sudah di-merge ke `teachers` (`database/migrations/2026_02_04_000000_merge_biographies_into_teachers.php`). **Manaqib dan Guru adalah model yang sama** — `App\Models\Teacher` — hanya dibedakan oleh filter tampilan:

| Halaman | Komponen | Filter |
|---|---|---|
| `/manaqib` | `app/Livewire/ListBiography.php:33` | `publiclyVisible()` + `wafat_hijriah_year != null` |
| `/guru` | `app/Livewire/ListGuru.php:68` | `publiclyVisible()` + `wafat_hijriah_year == null` |

Kolom foto yang ada hanya satu: `teachers.foto`, satu file `guru/{uuid}.webp`, di-crop `cover 600×600` lewat `App\Services\ImageService::upload()`.

### Alur kontribusi guru

`app/Http/Controllers/User/KontribusiGuruController.php`:

- `store()` — validasi (name, biografi, foto, maps, tahun_lahir, wilayah), set `contribution_status = 'pending'`, `contributor_user_id = Auth::id()`, buat record `Contribution`.
- `update()` — di-scope ke pemilik (`Teacher::where('contributor_user_id', Auth::id())->findOrFail($id)`). **Hanya mengembalikan status ke `pending` jika status saat ini `rejected`.** Edit pada manaqib yang sudah `approved` langsung tayang tanpa moderasi ulang.

Form kontributor (`resources/views/pages/kontributor/guru/_form.blade.php`) berisi: nama, tahun lahir, foto, link maps, biografi, domisili. **Tidak ada field tanggal wafat** dan **tidak ada field `source[]`** — keduanya hanya ada di form admin (`app/Http/Controllers/GuruController.php`).

### Editor biografi

Form admin (`resources/views/pages/guru/create.blade.php`, `edit.blade.php`) memakai **TipTap v2.6.6** yang di-`import` langsung dari CDN `https://esm.sh/@tiptap/*` di dalam `<script type="module">`, dengan toolbar Flowbite yang ditulis inline. Editor menulis balik ke `<textarea id="biografi" class="hidden" name="biografi">` lewat `onUpdate` (`guru/create.blade.php:457`, `:740`).

Markup + script ini **di-copy-paste ke 6 file blade** tanpa komponen reusable:
`pages/post/create.blade.php`, `pages/post/edit.blade.php`, `pages/user/kelola-foundation/tambah-artikel.blade.php`, `pages/user/kelola-foundation/edit-artikel.blade.php`, `pages/guru/create.blade.php`, `pages/guru/edit.blade.php`.

Form kontributor memakai `<textarea name="biografi" rows="8">` polos (`_form.blade.php:43`) — hasilnya HTML mentah tanpa satu pun tag paragraf.

### Moderasi

`app/Http/Controllers/Admin/ModerasiController.php::moderasiTeacher()` → `KhidmahService::approve()/reject()`. Tabel moderasi di `resources/views/livewire/guru.blade.php` (tab "Perlu Moderasi", baris 73–117) hanya menampilkan **nama, kontributor, desa, aksi** — admin tidak melihat foto apa pun sebelum menekan "Setujui".

---

## Temuan yang Ikut Diperbaiki

### Temuan 1 — Konten `pending`/`rejected` bocor lewat URL langsung

Halaman **list** memakai scope `publiclyVisible()`, tapi halaman **detail** tidak:

- `app/Http/Controllers/User/BiographyController.php:17` — `Teacher::with('contributor')->where('slug', $slug)->firstOrFail()`
- `app/Http/Controllers/User/GuruController.php:18` — route-model binding `Teacher $teacher` tanpa scope

Akibatnya siapa pun yang tahu slug-nya bisa membuka manaqib berstatus `pending` atau `rejected`. Tanpa perbaikan ini, aturan "foto baru ditahan sampai dimoderasi ulang" (D-4) **tidak memberi perlindungan apa pun** — foto yang belum disetujui tetap bisa diakses publik.

### Temuan 2 — Kontribusi guru tidak pernah bisa jadi manaqib

`ListBiography` hanya menampilkan Teacher yang `wafat_hijriah_year`-nya terisi, sedangkan form kontributor tidak punya field wafat sama sekali. Artinya kontributor hari ini **tidak dapat mengirim manaqib** — kirimannya selalu jatuh ke daftar `/guru` (guru hidup). Fitur foto bersama tidak akan pernah terlihat di `/manaqib` tanpa field wafat.

### Temuan 3 — XP dobel saat disetujui ulang

`KhidmahService::approve()` (`app/Services/KhidmahService.php:46`) memanggil `$user->increment('total_khidmah_points', $points)` setiap kali dipanggil untuk entitas yang belum `approved`. Dengan D-4 (edit foto → balik ke `pending`), alur `approved → edit → pending → approve` akan **memberi XP dua kali**. Ini membuka celah farming XP dengan mengedit foto berulang kali. Celah yang sama sudah ada untuk majelis/jadwal/amalan lewat jalur `rejected → edit → pending → approve`.

### Temuan 4 — Separuh toolbar WYSIWYG admin dibuang oleh `clean()`

`config/purifier.php:28` menetapkan `HTML.Allowed` = `div,b,strong,i,em,u,a[href|title],ul,ol,li,p[style],br,span[style],img[width|height|alt|src]`.

Heading (`h1`–`h6`), blockquote, horizontal rule, highlight (`mark`), inline code, strikethrough (`s`), dan embed YouTube (`iframe`) yang dapat dihasilkan toolbar TipTap admin **semuanya dibuang** saat dirender lewat `{!! clean($biography->biografi) !!}` (`pages/user/biography/detail.blade.php:83`, `pages/user/guru/detail.blade.php:110`). Admin menekan tombol "Heading 2", tersimpan di DB, lalu hilang di halaman publik tanpa pesan apa pun.

Konsekuensi untuk spec ini: toolbar kontributor **tidak boleh menyalin toolbar admin apa adanya**. Toolbar dibatasi hanya ke format yang bertahan melewati `clean()` (D-14). Memperbaiki toolbar admin atau memperlebar `HTML.Allowed` **di luar scope** (lihat R-8).

### Temuan 5 — Tidak ada `clean()` sebelum persist

Tidak satu pun controller memanggil `clean()` (dicek: `app/Http/Controllers/**`). Sanitasi hanya terjadi saat render. Selama biografi kontributor berupa textarea polos, dampaknya kecil. Begitu kontributor diberi WYSIWYG, **HTML sembarang dari kontributor tersimpan mentah di DB** — dan CLAUDE.md secara eksplisit mensyaratkan `clean()` sebelum persist (proyek ini sudah pernah kena XSS di konten post dan deskripsi guru). Karena itu `clean()` ditambahkan pada jalur kontributor (D-15).

---

### Temuan 6 — Skema `teachers` menyimpang dari migration (ditemukan saat implementasi)

Tabel `teachers` di database yang berjalan **tidak punya kolom `domisili`**, `foto`-nya **nullable**, dan punya kolom wilayah `province_code`/`city_code`/`district_code`/`village_code` — padahal **tidak ada satu pun migration** yang membuat perubahan itu (`create_teachers_table` mendefinisikan `domisili` dan `foto` sebagai NOT NULL, dan tak pernah menambah kolom wilayah). Skema produksi jelas pernah diubah dengan tangan.

Akibatnya `migrate:fresh` — termasuk database test SQLite — menghasilkan tabel yang berbeda dari produksi, dan `KontribusiGuruController` (yang menulis `province_code` dkk. dan tidak pernah mengisi `domisili`) **tidak akan jalan di sana**. Ini yang membuat jalur kontribusi guru mustahil diuji sampai sekarang.

Ditangani oleh migration rekonsiliasi `2026_07_12_000001_reconcile_teachers_schema.php`: menambah kolom wilayah bila belum ada, melonggarkan `foto` dan `domisili` menjadi nullable. Setiap langkah dijaga `Schema::hasColumn` sehingga aman di database yang sudah terlanjur benar, dan **tidak ada kolom yang dihapus**.

## Keputusan (Hasil Wawancara)

| ID | Keputusan |
|---|---|
| **D-1** | Foto **tampil publik** di halaman manaqib dan guru. Bukan bukti internal untuk moderator saja. |
| **D-2** | **Satu foto** per guru, disimpan sebagai **kolom baru di `teachers`** (bukan tabel galeri). |
| **D-3** | Yang boleh mengunggah: **hanya kontributor pemilik data guru itu** (`contributor_user_id = Auth::id()`). Admin **tidak** mengunggah foto bersama. |
| **D-4** | Moderasi **ikut status kontribusi guru** (`contribution_status`). Menambah/mengganti foto atau caption pada manaqib `approved` → status kembali ke **`pending`** dan konten hilang dari publik sampai disetujui ulang. |
| **D-5** | **Caption wajib** diisi selama ada foto. |
| **D-6** | **Menghapus** foto **tidak** memicu moderasi ulang — manaqib tetap tayang. |
| **D-7** | Admin dapat **menghapus foto tanpa menolak manaqib**, dan **melihat foto di tabel moderasi** sebelum menyetujui. |
| **D-8** | Field **tanggal wafat (masehi + hijriah)** ditambahkan ke form kontributor (memperbaiki Temuan 2). |
| **D-9** | Guard di `KhidmahService::approve()` — **XP hanya diberikan sekali seumur entitas**. Berlaku untuk semua jenis kontribusi, bukan hanya guru. |
| **D-10** | Batas file: **maks 8 MB**, diproses `scaleDown` lebar **1600px** (tanpa crop, komposisi foto pertemuan dipertahankan). |
| **D-11** | **Tidak ada** checkbox pernyataan hak/keaslian foto. Mengandalkan moderasi admin. |
| **D-12** | Penamaan: kolom `foto_bersama` + `foto_bersama_caption`, label UI **"Foto Bersama Guru"**. |
| **D-13** | Field **biografi** pada form kontributor memakai **WYSIWYG TipTap**, sama seperti admin. Diekstrak jadi **komponen Blade reusable** `<x-wysiwyg-editor>` — bukan salinan ke-7 dari markup inline. |
| **D-14** | Toolbar kontributor **dibatasi** pada format yang bertahan melewati `clean()`: paragraf, tebal, miring, garis bawah, tautan, daftar berpoin, daftar bernomor, rata kiri/tengah/kanan. **Tanpa** heading, blockquote, HR, highlight, code, gambar-lewat-URL, dan embed YouTube (Temuan 4). |
| **D-15** | `clean()` dipanggil pada `biografi` **sebelum disimpan** di `KontribusiGuruController::store()` dan `update()`. |

---

## Expected Behavior

### Skenario 1 — Kontributor mengirim guru baru dengan foto bersama
Kontributor mengisi form di `/kontributor/saya/guru/create`, mengunggah foto bersama + caption. Data tersimpan dengan `contribution_status = 'pending'`. Manaqib **belum** tampil di publik (list maupun detail). Kontributor sendiri masih bisa membuka halaman detailnya sebagai pratinjau.

### Skenario 2 — Admin menyetujui
Di tab "Perlu Moderasi" (`/admin/guru`), admin melihat thumbnail foto bersama + caption pada baris data. Setelah "Setujui": `contribution_status = 'approved'`, XP diberikan, manaqib + foto bersama tampil publik.

### Skenario 3 — Caption kosong
Kontributor mengunggah foto tanpa mengisi caption → validasi gagal, kembali ke form dengan pesan *"Keterangan foto bersama wajib diisi."*, input lain tetap terisi.

### Skenario 4 — Kontributor mengganti foto pada manaqib yang sudah approved
Status kembali ke `pending`. Manaqib **hilang dari `/manaqib`, `/guru`, dan halaman detailnya** (404 untuk publik) sampai admin menyetujui ulang. Flash message setelah submit: *"Data guru berhasil diperbarui. Karena foto bersama berubah, manaqib menunggu moderasi ulang dan sementara tidak tampil di publik."*

### Skenario 5 — Kontributor menyunting biografi/nama saja (tanpa menyentuh foto bersama)
Status **tidak berubah** — perilaku `update()` yang ada dipertahankan. Manaqib `approved` tetap tayang. (Lihat [Risiko](#risiko--trade-off) R-2.)

### Skenario 6 — Kontributor menghapus foto bersamanya
Mencentang "Hapus foto bersama" → file dihapus dari storage, `foto_bersama` dan `foto_bersama_caption` di-null-kan. `contribution_status` **tidak berubah** (D-6). Manaqib `approved` tetap tayang tanpa foto.

### Skenario 7 — Admin menghapus foto bermasalah
Admin membuka `/admin/guru/{id}/edit`, menekan "Hapus Foto Bersama". File dihapus, kedua kolom di-null-kan, `contribution_status` **tidak berubah** — manaqib `approved` tetap tayang.

### Skenario 8 — Disetujui ulang setelah edit foto
`KhidmahService::approve()` mendeteksi `Contribution.points_earned > 0` → status berubah ke `approved`, **XP tidak ditambah lagi**, tidak ada notifikasi `KontribusiDisetujui` (karena tidak ada XP baru).

### Skenario 9 — Kontributor menulis biografi dengan WYSIWYG
Di form kontributor, field biografi tampil sebagai editor kaya teks dengan toolbar terbatas (D-14). Kontributor menebalkan nama guru, membuat daftar karya, dan menautkan sumber. Saat submit, HTML dari editor melewati `clean()` lalu disimpan. Halaman manaqib menampilkan format tersebut utuh — tidak ada tag yang hilang, karena toolbar hanya menghasilkan tag yang lolos whitelist purifier.

### Skenario 10 — Editor dikosongkan
TipTap menyisakan `<p></p>` pada editor kosong, yang lolos rule `required|string` biasa. Validasi harus menolaknya: submit dengan editor kosong gagal dengan pesan *"Biografi wajib diisi."*

### Skenario 11 — Approve setelah revoke
`revoke()` menyetel `points_earned = 0` dan memotong XP. Jika kemudian di-approve lagi, `points_earned` sudah 0 → **XP diberikan kembali**. Perilaku ini benar dan tidak boleh ikut terblokir oleh guard D-9.

---

## Role & Authorization

| Aksi | Kontributor pemilik | Kontributor lain / jamaah | Super Admin |
|---|---|---|---|
| Unggah/ganti foto bersama | Ya | Tidak | Tidak (D-3) |
| Hapus foto bersama | Ya | Tidak | Ya |
| Lihat foto di detail publik | Ya | Ya (hanya jika `approved`) | Ya |
| Lihat manaqib `pending`/`rejected` via URL | Ya (pratinjau miliknya) | **Tidak (404)** | Ya |
| Setujui/tolak | Tidak | Tidak | Ya |

Scoping kepemilikan memakai pola yang sudah ada: `Teacher::where('contributor_user_id', Auth::id())->findOrFail($id)` → non-pemilik dapat **404**, bukan 403 (tidak membocorkan keberadaan record).

---

## Data Model

### Migration baru: `database/migrations/2026_07_12_000000_add_foto_bersama_to_teachers_table.php`

```php
Schema::table('teachers', function (Blueprint $table) {
    $table->string('foto_bersama')->nullable()->after('foto');
    $table->string('foto_bersama_caption', 255)->nullable()->after('foto_bersama');
});
```

`down()` melakukan `dropColumn` atas kedua kolom tersebut. Additive dan backward-compatible — semua baris lama bernilai `NULL`.

**Tidak ada perubahan pada kolom wafat** (D-8 hanya mengekspos kolom `wafat_masehi`, `wafat_hijriah_day/month/year` yang **sudah ada** ke form kontributor).

### Penyimpanan file

- Direktori: `guru/bersama/{uuid}.webp` pada disk `public`.
- Diproses dengan `ImageService::upload($file, 'guru/bersama', 'scaleDown', 1600, null, 80, $oldPath)` — `scaleDown` mempertahankan rasio, `$oldPath` menghapus file lama saat diganti.
- Penghapusan: `ImageService::delete($path)`.

---

## Implementasi

### File yang Berubah

| File | Perubahan |
|---|---|
| `database/migrations/2026_07_12_000000_add_foto_bersama_to_teachers_table.php` | **Baru** — dua kolom nullable |
| `database/migrations/2026_07_12_000001_reconcile_teachers_schema.php` | **Baru** — menyamakan skema migration dengan skema produksi (Temuan 6) |
| `app/Models/Teacher.php` | Tambah method `isVisibleTo(?User $user): bool` |
| `app/Http/Controllers/User/KontribusiGuruController.php` | Validasi + upload/hapus `foto_bersama`, aturan re-pending, field wafat (D-8), `clean()` biografi (D-15) |
| `resources/views/components/wysiwyg-editor.blade.php` | **Baru** — komponen anonim TipTap toolbar terbatas (D-13, D-14) |
| `app/Http/Controllers/User/BiographyController.php` | Terapkan gate visibilitas pada `detail()` |
| `app/Http/Controllers/User/GuruController.php` | Terapkan gate visibilitas pada `detail()` |
| `app/Http/Controllers/GuruController.php` | Tambah `destroyFotoBersama(int $id)` |
| `app/Services/KhidmahService.php` | Guard XP sekali seumur entitas (D-9) |
| `routes/web.php` | Route `DELETE /admin/guru/{id}/foto-bersama` |
| `resources/views/pages/kontributor/guru/_form.blade.php` | Field foto bersama + caption + checkbox hapus + field wafat; textarea biografi → `<x-wysiwyg-editor>` |
| `resources/views/pages/user/biography/detail.blade.php` | Blok foto bersama + banner pratinjau pemilik |
| `resources/views/pages/user/guru/detail.blade.php` | Blok foto bersama |
| `resources/views/pages/guru/edit.blade.php` | Pratinjau foto bersama + tombol hapus (admin) |
| `resources/views/livewire/guru.blade.php` | Kolom thumbnail foto bersama di tabel moderasi |

### `Teacher::isVisibleTo()`

Satu sumber kebenaran untuk gate visibilitas, dipakai oleh kedua controller detail. Melengkapi `scopePubliclyVisible()` yang sudah ada (dipertahankan apa adanya untuk query list).

```php
public function isVisibleTo(?User $user): bool
{
    if (in_array($this->contribution_status, [null, 'approved'], true)) {
        return true;
    }

    if (! $user) {
        return false;
    }

    return $user->id === $this->contributor_user_id || $user->hasRole('Super Admin');
}
```

### Gate di controller detail

```php
// BiographyController::detail() dan User\GuruController::detail()
abort_unless($teacher->isVisibleTo(Auth::user()), 404);
```

Pada `biography/detail.blade.php`, jika `contribution_status !== 'approved' && $biography->contribution_status !== null`, tampilkan banner di atas konten:

> *"Manaqib ini sedang menunggu moderasi admin. Hanya Anda yang dapat melihat halaman ini."*

### Validasi kontributor (`store()` dan `update()`)

Aturan baru yang ditambahkan ke array validasi yang sudah ada:

```php
'foto_bersama'         => 'nullable|image|mimes:jpeg,jpg,png,webp|max:8192',
'foto_bersama_caption' => [
    Rule::requiredIf(fn () => $request->hasFile('foto_bersama')
        || ($guru?->foto_bersama && ! $request->boolean('hapus_foto_bersama'))),
    'nullable', 'string', 'max:255',
],
'hapus_foto_bersama'   => 'nullable|boolean',

// D-8 — samakan dengan validasi admin di GuruController
'wafat_masehi'        => 'nullable|date',
'wafat_hijriah_day'   => 'nullable|integer|min:1|max:30',
'wafat_hijriah_month' => 'nullable|integer|min:1|max:12',
'wafat_hijriah_year'  => 'nullable|integer|min:1|max:9999',
```

Rule `biografi` yang **sudah ada** (`required|string`) diperketat agar editor kosong tertolak (Skenario 10) — TipTap mengirim `<p></p>` yang lolos `required`:

```php
'biografi' => [
    'required', 'string', 'max:65000',
    function ($attribute, $value, $fail) {
        if (trim(strip_tags($value)) === '') {
            $fail('Biografi wajib diisi.');
        }
    },
],
```

Pesan kustom: `'foto_bersama_caption.required' => 'Keterangan foto bersama wajib diisi.'`

Pada `store()`, `$guru` belum ada → `requiredIf` cukup bergantung pada `hasFile`.

### Sanitasi biografi (D-15)

Pada `store()` **dan** `update()`, setelah validasi dan sebelum `Teacher::create()`/`update()`:

```php
$validated['biografi'] = clean($validated['biografi']);
```

Memakai config purifier `default` (`config/purifier.php`) — tidak ada preset baru, tidak ada perubahan config. Karena toolbar dibatasi ke whitelist yang sama (D-14), `clean()` tidak akan membuang apa pun yang sengaja diketik kontributor; ia hanya menghapus HTML jahat yang disuntikkan lewat DevTools atau request manual.

`GuruController` (admin) **tidak diubah** — lihat R-9.

### Komponen WYSIWYG (D-13, D-14)

Komponen anonim baru: `resources/views/components/wysiwyg-editor.blade.php`.

```blade
@props(['name', 'value' => '', 'id' => null])
@php($editorId = $id ?? $name)
```

Struktur yang dihasilkan mengikuti pola yang sudah dipakai admin — sebuah `<div id="{{ $editorId }}-editor">` sebagai kanvas TipTap, plus `<textarea id="{{ $editorId }}" name="{{ $name }}" class="hidden">{{ $value }}</textarea>` sebagai pembawa nilai ke server. Semua `id` diturunkan dari prop, **tidak di-hardcode** seperti `wysiwyg-example`/`toggleBoldButton` pada file admin, sehingga komponen aman dipakai lebih dari sekali di satu halaman.

Toolbar terbatas (D-14) — tombol yang disediakan **hanya**:

| Tombol | Command TipTap | Tag hasil | Lolos `HTML.Allowed`? |
|---|---|---|---|
| Tebal | `toggleBold()` | `<strong>` | Ya |
| Miring | `toggleItalic()` | `<em>` | Ya |
| Garis bawah | `toggleUnderline()` | `<u>` | Ya |
| Tautan | `toggleLink({href})` | `<a href>` | Ya |
| Hapus tautan | `unsetLink()` | — | — |
| Daftar berpoin | `toggleBulletList()` | `<ul><li>` | Ya |
| Daftar bernomor | `toggleOrderedList()` | `<ol><li>` | Ya |
| Rata kiri/tengah/kanan | `setTextAlign()` | `<p style="text-align:…">` | Ya (`p[style]` + `text-align`) |

Extension TipTap yang dimuat: `StarterKit` (dengan `heading: false`, `blockquote: false`, `horizontalRule: false`, `code: false`, `codeBlock: false`, `strike: false`), `Underline`, `Link`, `TextAlign`. **Tidak memuat** `Highlight`, `Image`, `YouTube`, `TextStyle`, `Color`, `FontFamily` — semuanya menghasilkan markup yang dibuang `clean()` atau tidak diinginkan dari kontributor.

Script di-`@push('scripts')` dari dalam komponen, memakai `<script type="module">` dengan `import` dari `https://esm.sh/@tiptap/*@2.6.6` — **versi dan sumber persis sama dengan form admin**, agar tidak memperkenalkan dependency baru dan tidak menyentuh `package.json`. `DashboardLayout` sudah menyediakan `@stack('scripts')` (dipakai oleh script wilayah di `_form.blade.php` saat ini).

Toolbar tidak memakai dropdown Flowbite (tidak ada typography/font-size/color picker), sehingga komponen **tidak bergantung pada `FlowbiteInstances`** — berbeda dari script admin.

Pemakaian di `_form.blade.php`, menggantikan `<textarea name="biografi">` di baris 43:

```blade
<x-wysiwyg-editor name="biografi" :value="old('biografi', $guru?->biografi)" />
@error('biografi')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
```

Nilai `old()`/`$guru->biografi` berisi HTML dan dimasukkan ke dalam `<textarea>` sebagai teks — Blade `{{ }}` meng-escape-nya, dan browser meng-unescape saat membaca `.value`. Itu benar dan aman; **jangan** memakai `{!! !!}` di sini.

Keenam file blade yang sudah memuat TipTap inline **tidak disentuh** — lihat [Yang Tidak Termasuk Scope](#yang-tidak-termasuk-scope).

### Aturan upload/hapus dan re-pending (`update()`)

Urutan yang harus diikuti (unggahan baru menang atas centang hapus):

```php
$fotoBersamaBerubah = false;

if ($request->hasFile('foto_bersama')) {
    $validated['foto_bersama'] = $this->imageService->upload(
        $request->file('foto_bersama'), 'guru/bersama', 'scaleDown', 1600, null, 80, $guru->foto_bersama
    );
    $fotoBersamaBerubah = true;
} elseif ($request->boolean('hapus_foto_bersama')) {
    $this->imageService->delete($guru->foto_bersama);
    $validated['foto_bersama'] = null;
    $validated['foto_bersama_caption'] = null;
    // D-6: penghapusan TIDAK menandai perubahan → tidak memicu moderasi ulang
} else {
    unset($validated['foto_bersama']);

    if ($guru->foto_bersama
        && ($validated['foto_bersama_caption'] ?? null) !== $guru->foto_bersama_caption) {
        $fotoBersamaBerubah = true; // caption berubah pada foto yang tayang
    }
}

if ($guru->contribution_status === 'rejected') {
    $validated['contribution_status'] = 'pending';
    $validated['rejection_reason'] = null;
} elseif ($guru->contribution_status === 'approved' && $fotoBersamaBerubah) {
    $validated['contribution_status'] = 'pending';
}
```

Flash message dibedakan: jika status berubah ke `pending` karena foto, pakai pesan Skenario 4; selain itu pakai pesan sukses yang ada.

### Hapus foto oleh admin (`GuruController::destroyFotoBersama`)

```php
Route::delete('/guru/{id}/foto-bersama', [GuruController::class, 'destroyFotoBersama'])
    ->name('admin.guru.foto-bersama.destroy');
```

Ditempatkan di grup `/admin` (middleware `IsAdmin`) di `routes/web.php`, **sebelum** `Route::resource('/guru', GuruController::class)` agar tidak tertangkap oleh route resource.

Method menghapus file lewat `ImageService::delete()`, meng-`update()` kedua kolom jadi `null`, **tidak menyentuh `contribution_status`**, lalu `redirect()->back()->with('message', 'Foto bersama berhasil dihapus.')`.

Di `resources/views/pages/guru/edit.blade.php`, form hapus ini harus diletakkan **di luar** `<form>` utama (nested form adalah HTML invalid dan tidak akan tersubmit) — mis. sebagai card terpisah di bawah form edit.

### Guard XP (`KhidmahService::approve`)

Ambil `Contribution` **sebelum** menghitung poin:

```php
$contribution = Contribution::where('contributable_id', $entity->id)
    ->where('contributable_type', get_class($entity))
    ->where('user_id', $userId)
    ->first();

$sudahPernahDapatXp = ($contribution?->points_earned ?? 0) > 0;
$points = $sudahPernahDapatXp ? 0 : KontribusiXpSetting::pointsFor($contributionType);

$entity->contribution_status = 'approved';
$entity->moderated_at = now();
$entity->rejection_reason = null;
$entity->save();

if ($points === 0) {
    return; // persetujuan ulang: status saja, tanpa XP, tanpa notifikasi
}

// ... sisa alur existing: update points_earned, increment, badge, notifikasi
```

Setelah `revoke()` (`points_earned` di-set 0), `$sudahPernahDapatXp` bernilai false → XP diberikan lagi saat approve berikutnya. Sesuai Skenario 11.

### Tampilan publik (kedua halaman detail)

Ditempatkan **tepat di bawah komponen `<x-kontributor.attribution>`** di `biography/detail.blade.php` (baris 79) — foto adalah penguat kredibilitas kontributor, jadi harus berdampingan dengan atribusinya.

```blade
@if($biography->foto_bersama)
    <figure class="mb-6 border border-gray-100 dark:border-gray-700/60 rounded-xl overflow-hidden">
        <img src="{{ Storage::url($biography->foto_bersama) }}"
             alt="{{ $biography->foto_bersama_caption }}"
             loading="lazy"
             class="w-full h-auto object-contain bg-gray-50 dark:bg-gray-900">
        <figcaption class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 italic">
            {{ $biography->foto_bersama_caption }}
            <span class="not-italic">— dokumentasi {{ $biography->contributor?->name }}</span>
        </figcaption>
    </figure>
@endif
```

Caption di-render dengan `{{ }}` (escaped), **bukan** `{!! !!}` — caption adalah teks biasa, tidak boleh menerima HTML. Tidak perlu `clean()` karena tidak ada HTML yang diizinkan.

### Tabel moderasi (`resources/views/livewire/guru.blade.php`)

Tambah satu kolom `<th>Foto Bersama</th>` pada `<thead>` tabel moderasi (baris ~77) dan sel yang sesuai pada `<tbody>`: thumbnail `w-16 h-16 object-cover` yang menaut ke file penuh (`target="_blank"`), dengan caption sebagai `title`. Jika kosong, tampilkan tanda "—". `colspan` pada baris "Tidak ada data" (baris 113) dinaikkan dari `5` menjadi `6`.

---

## Yang Tidak Termasuk Scope

- **Galeri multi-foto.** Satu foto per guru (D-2). Kalau nanti butuh banyak foto, migrasinya ke tabel `teacher_photos` adalah pekerjaan terpisah.
- **Foto bersama yang diunggah admin.** Admin hanya melihat dan menghapus (D-3, D-7).
- **Kontribusi foto oleh jamaah lain** yang bukan pemilik data guru. Tidak ada entry point baru di halaman detail publik.
- **Moderasi per-foto** (status/alasan tolak khusus foto). Foto ikut `contribution_status` guru.
- **Lightbox / zoom / carousel.** Foto ditampilkan inline; klik membuka file asli di tab baru pada tabel moderasi saja.
- **Watermark, EXIF stripping, deteksi duplikat/reverse image search.**
- **Checkbox pernyataan hak cipta** (D-11).
- **Field `source[]` di form kontributor.** Tetap admin-only, di luar scope.
- **Migrasi 6 file blade yang sudah memuat TipTap inline** (`post/create`, `post/edit`, `kelola-foundation/tambah-artikel`, `kelola-foundation/edit-artikel`, `guru/create`, `guru/edit`) ke `<x-wysiwyg-editor>`. Komponen baru **hanya** dipakai form kontributor guru. Merapikan keenamnya adalah refactor terpisah (CLAUDE.md: dilarang refactor di luar scope) — dicatat sebagai follow-up.
- **Memperbaiki toolbar admin yang menghasilkan markup terbuang** (Temuan 4) dan **memperlebar `HTML.Allowed` di `config/purifier.php`**. Keduanya berdampak ke seluruh konten (post, artikel ilmiah, biografi lama) dan butuh keputusan tersendiri. Lihat R-8.
- **`clean()` pada `GuruController` (admin)** dan controller lain. Hanya jalur kontributor guru yang disentuh. Lihat R-9.
- **Memindahkan TipTap dari CDN esm.sh ke bundle Vite.** Komponen memakai sumber & versi yang sama dengan yang sudah berjalan.
- **Filter `ListBiography`** (`wafat_hijriah_year != null`) tidak diubah — konsekuensinya lihat R-4.
- **Backfill data lama.** Semua Teacher yang ada bernilai `NULL` pada kolom baru.
- **`User\GuruController::list()`** yang memakai `Teacher::all()` tanpa scope — variabel `$teachers` di sana tidak dipakai oleh Livewire `ListGuru` yang merender daftarnya. Tidak disentuh.
- **Template debris admin** (`/ecommerce`, `/community`, dll.) — tidak disentuh, sesuai CLAUDE.md.

---

## Edge Cases

| # | Kasus | Perilaku yang diharapkan |
|---|---|---|
| E-1 | Unggah foto baru **dan** centang "hapus" bersamaan | Unggahan menang; foto lama dihapus dari storage, foto baru dipakai, status → `pending` jika sebelumnya `approved`. |
| E-2 | Centang hapus padahal tidak ada foto | No-op; tidak error, status tidak berubah. |
| E-3 | Ganti caption saja pada manaqib `approved` yang punya foto | Dihitung sebagai perubahan → status `pending` (caption adalah bagian dari klaim kredibilitas). |
| E-4 | Ganti caption pada manaqib yang **tidak** punya foto | Caption di-null-kan/diabaikan; status tidak berubah. |
| E-5 | File > 8 MB | Validasi `max:8192` gagal → kembali ke form dengan pesan. **Prasyarat: `upload_max_filesize` ≥ 8M dan `post_max_size` ≥ 10M di php.ini** — kalau tidak, PHP membuang request sebelum Laravel sempat memvalidasi dan user melihat error kosong/500. |
| E-6 | File bukan gambar / HEIC dari iPhone | `mimes:jpeg,jpg,png,webp` menolak; pesan validasi harus menyebut format yang didukung. HEIC **tidak** didukung (Intervention/GD tidak membacanya). |
| E-7 | Manaqib `rejected` yang punya foto, lalu diedit | Aturan lama menang: status → `pending`, `rejection_reason` dikosongkan (tidak ada perubahan perilaku). |
| E-8 | Kontributor membuka detail manaqib miliknya yang `pending` | Boleh (200) + banner pratinjau. |
| E-9 | Kontributor A membuka manaqib `pending` milik kontributor B | 404. |
| E-10 | Admin menghapus foto pada manaqib `pending` | Foto hilang, status tetap `pending` (belum disetujui). |
| E-11 | `contributor_user_id` null (data lama/admin-created) dengan `contribution_status` null | `isVisibleTo()` mengembalikan `true` — data legacy tetap publik. Tidak ada regresi. |
| E-12 | Approve → revoke → edit foto → approve | `points_earned` sudah 0 saat revoke, jadi approve terakhir memberi XP lagi. Benar. |
| E-13 | Editor WYSIWYG dikosongkan (TipTap mengirim `<p></p>`) | Validasi `strip_tags` menolak → *"Biografi wajib diisi."* (Skenario 10) |
| E-14 | Kontributor menempel (paste) teks kaya dari Word/web yang mengandung `<script>`, `<h2>`, atau style aneh | TipTap membuang tag yang tidak punya extension saat paste; sisanya disaring `clean()` saat persist. Tidak ada HTML jahat yang tersimpan. |
| E-15 | Request POST manual (tanpa browser) berisi `biografi=<img src=x onerror=alert(1)>` | `clean()` pada `store()`/`update()` membuang atribut `onerror`; hanya `<img src>` yang tersisa. Tidak ada XSS tersimpan. |
| E-16 | Kontributor mengedit manaqib lama yang biografinya masih teks polos (tanpa tag) | TipTap membungkusnya jadi `<p>…</p>` saat dimuat. Tersimpan sebagai HTML. Tidak ada kehilangan konten. Perubahan biografi saja **tidak** memicu re-moderasi (Skenario 5). |
| E-17 | JavaScript gagal dimuat (CDN esm.sh terblokir/offline) | `<textarea>` pembawa nilai tetap ada di DOM tapi ber-`class="hidden"` → kontributor tidak bisa mengetik apa pun dan tidak sadar kenapa. Lihat R-10. |
| E-18 | Kontributor mengisi `wafat_masehi` tapi tidak mengisi tahun hijriah | Guru tetap tidak muncul di `/manaqib` (filter `ListBiography` memakai `wafat_hijriah_year`), dan **hilang** dari daftar pilihan guru pada form majelis/jadwal (yang memfilter `where('wafat_masehi', null)`). Form harus menjelaskan hal ini — lihat R-4. |

---

## Compatibility

- **Migration additive**, dua kolom nullable. Tidak ada penghapusan tabel/kolom/data. Aman untuk environment lain.
- **`Teacher` memakai `$guarded = []`** — kolom baru langsung mass-assignable tanpa perubahan model.
- **Gate visibilitas mengubah perilaku publik** untuk Teacher berstatus `pending`/`rejected`: sebelumnya 200, sekarang 404 bagi publik. Data lama (`contribution_status = null`) tidak terpengaruh. `tests/Feature/UserBiographyTest.php` membuat Teacher tanpa `contribution_status` → tetap lulus.
- **Guard XP mengubah `KhidmahService` untuk semua jenis kontribusi** (majelis, jadwal, amalan, catatan, guru). Ini disengaja (D-9). Tidak ada pengurangan XP retroaktif — hanya mencegah penambahan ganda ke depan.
- **Field wafat di form kontributor** membuat guru yang ditandai wafat oleh kontributor **keluar** dari daftar pilihan guru pada `KontribusiMajelisController` dan `KontribusiJadwalController` (keduanya memfilter `where('wafat_masehi', null)`). Secara semantik benar (guru wafat tidak mengajar), tapi perlu disadari.
- PHP: butuh `memory_limit` ≥ 256M untuk memproses JPEG 8 MB beresolusi tinggi lewat Intervention.

---

## Test Plan

Semua test memakai `RefreshDatabase` + `Storage::fake('public')` + `UploadedFile::fake()->image()`.

### `tests/Feature/Kontributor/FotoBersamaGuruTest.php` (baru)

| Test | Skenario |
|---|---|
| `test_kontributor_dapat_mengunggah_foto_bersama_dengan_caption` | store() dengan foto + caption → kolom terisi, file ada di disk, status `pending` |
| `test_caption_wajib_saat_foto_diunggah` | store() dengan foto tanpa caption → error validasi `foto_bersama_caption` |
| `test_caption_wajib_saat_foto_lama_masih_ada` | update() tanpa file baru, caption dikosongkan, foto lama ada → error validasi |
| `test_foto_melebihi_8mb_ditolak` | file 9 MB → error validasi |
| `test_mengganti_foto_pada_manaqib_approved_mengembalikan_status_ke_pending` | approved + upload foto baru → `pending`; file lama terhapus dari disk |
| `test_mengubah_caption_saja_pada_manaqib_approved_mengembalikan_status_ke_pending` | approved + caption berubah → `pending` |
| `test_menghapus_foto_tidak_mengubah_status_approved` | approved + `hapus_foto_bersama=1` → status tetap `approved`, kolom null, file terhapus |
| `test_menyunting_biografi_saja_tidak_mengubah_status_approved` | approved + hanya biografi berubah → tetap `approved` (regresi perilaku lama) |
| `test_unggahan_baru_menang_atas_centang_hapus` | E-1 |
| `test_bukan_pemilik_tidak_dapat_mengedit_guru` | kontributor lain PUT ke `kontributor.guru.update` → 404 |

### `tests/Feature/Kontributor/BiografiWysiwygTest.php` (baru)

| Test | Skenario |
|---|---|
| `test_biografi_html_disimpan_setelah_disanitasi` | POST `biografi` = `<p>Halo <strong>Guru</strong></p><script>alert(1)</script>` → DB menyimpan `<p>` dan `<strong>`, **tanpa** `<script>` (E-15) |
| `test_atribut_event_handler_dibuang` | `<img src=x onerror=alert(1)>` → `onerror` tidak ada di DB |
| `test_biografi_kosong_dari_editor_ditolak` | `biografi` = `<p></p>` → error validasi `biografi` (E-13) |
| `test_biografi_dengan_hanya_spasi_ditolak` | `biografi` = `<p>&nbsp;</p>` atau `<p>   </p>` → error validasi |
| `test_form_kontributor_merender_komponen_wysiwyg` | GET `kontributor.guru.create` → `assertSee('id="biografi"', false)` dan menampilkan kanvas editor |
| `test_biografi_valid_dengan_daftar_dan_tautan_tersimpan_utuh` | `<ul><li>…</li></ul><a href="https://…">` → bertahan melewati `clean()` (bukti D-14 selaras dengan whitelist purifier) |

### `tests/Feature/TeacherVisibilityTest.php` (baru)

| Test | Skenario |
|---|---|
| `test_publik_tidak_dapat_membuka_manaqib_pending` | guest GET `manaqib-detail` status `pending` → 404 |
| `test_publik_tidak_dapat_membuka_manaqib_rejected` | → 404 |
| `test_publik_tidak_dapat_membuka_guru_pending` | guest GET `guru-detail` status `pending` → 404 |
| `test_pemilik_dapat_melihat_manaqib_pending_miliknya` | 200 + melihat banner pratinjau |
| `test_kontributor_lain_tidak_dapat_melihat_manaqib_pending` | 404 |
| `test_super_admin_dapat_melihat_manaqib_pending` | 200 |
| `test_manaqib_approved_tetap_dapat_dibuka_publik` | 200 |
| `test_manaqib_legacy_tanpa_contribution_status_tetap_publik` | `contribution_status = null` → 200 (E-11) |

### `tests/Feature/KhidmahXpTest.php` (baru)

| Test | Skenario |
|---|---|
| `test_approve_memberi_xp_sekali` | approve → `total_khidmah_points` bertambah sesuai `KontribusiXpSetting` |
| `test_approve_ulang_setelah_edit_tidak_menambah_xp` | approve → pending → approve → XP tetap sama; tidak ada notifikasi `KontribusiDisetujui` kedua (`Notification::fake()`) |
| `test_approve_setelah_revoke_memberi_xp_lagi` | approve → revoke → approve → XP kembali bertambah (E-12) |

### Menjalankan test di mesin ini

PHP CLI Laragon tidak meng-enable `pdo_sqlite`, dan `php artisan test` tidak mewariskan flag `-d` ke subprosesnya:

```
php -d extension=php_pdo_sqlite.dll vendor/phpunit/phpunit/phpunit --filter=FotoBersamaGuruTest
```

---

## Acceptance Criteria

- [ ] **AC-1** Kontributor pemilik dapat mengunggah satu foto bersama + caption saat membuat dan mengedit data guru.
- [ ] **AC-2** Caption wajib selama ada foto; submit tanpa caption gagal dengan pesan *"Keterangan foto bersama wajib diisi."* dan input lain tetap terisi.
- [ ] **AC-3** File > 8 MB atau bukan jpeg/jpg/png/webp ditolak validasi.
- [ ] **AC-4** Foto disimpan sebagai WebP di `guru/bersama/`, di-scaleDown ke lebar maks 1600px, **tanpa crop**.
- [ ] **AC-5** Foto bersama + caption tampil di `/manaqib/{slug}` dan `/guru/{slug}` (hanya untuk konten `approved`/legacy), tepat di bawah atribusi kontributor.
- [ ] **AC-6** Menambah/mengganti foto **atau mengubah caption** pada guru `approved` mengembalikan `contribution_status` ke `pending`, dan halaman detailnya menjadi 404 bagi publik sampai disetujui ulang.
- [ ] **AC-7** Menghapus foto bersama **tidak** mengubah `contribution_status`.
- [ ] **AC-8** Halaman detail manaqib/guru berstatus `pending`/`rejected` mengembalikan **404** untuk guest dan kontributor lain, **200** untuk pemiliknya (dengan banner pratinjau) dan Super Admin.
- [ ] **AC-9** Tabel moderasi guru (`/admin/guru`, tab "Perlu Moderasi") menampilkan thumbnail foto bersama + caption sebelum tombol Setujui/Tolak.
- [ ] **AC-10** Super Admin dapat menghapus foto bersama dari `/admin/guru/{id}/edit` tanpa mengubah `contribution_status`.
- [ ] **AC-11** Menyetujui ulang entitas yang XP-nya sudah pernah diberikan **tidak** menambah `total_khidmah_points`, dan tidak mengirim notifikasi XP kedua.
- [ ] **AC-12** Approve setelah `revoke()` tetap memberi XP kembali.
- [ ] **AC-13** Form kontributor guru punya field tanggal wafat (masehi + hijriah day/month/year), dan guru yang diisi tahun hijriah wafatnya muncul di `/manaqib` setelah disetujui.
- [ ] **AC-14** Data Teacher lama (`contribution_status = null`) tetap dapat diakses publik seperti sebelumnya.
- [ ] **AC-15** Field biografi pada form kontributor guru (create **dan** edit) tampil sebagai editor WYSIWYG dengan toolbar: tebal, miring, garis bawah, tautan, hapus tautan, daftar berpoin, daftar bernomor, rata kiri/tengah/kanan — **tanpa** heading, blockquote, HR, highlight, code, gambar, dan YouTube.
- [ ] **AC-16** Format yang dibuat kontributor di editor **tampil utuh** di `/manaqib/{slug}` dan `/guru/{slug}` — tidak ada tag yang hilang setelah `clean()`.
- [ ] **AC-17** Biografi disanitasi dengan `clean()` **sebelum disimpan**; `<script>` dan atribut `on*` tidak pernah masuk ke kolom `teachers.biografi` lewat jalur kontributor.
- [ ] **AC-18** Editor kosong (`<p></p>`) ditolak validasi dengan pesan *"Biografi wajib diisi."*
- [ ] **AC-19** Editor dibuat sebagai komponen `<x-wysiwyg-editor>`; keenam blade yang sudah memuat TipTap inline tidak berubah satu baris pun.
- [ ] **AC-20** Semua test pada [Test Plan](#test-plan) lulus; `./vendor/bin/pint` bersih.

---

## Verifikasi End-to-End

**Persiapan:** `php artisan migrate`, `php artisan storage:link` sudah pernah dijalankan, MySQL Laragon menyala.

1. Login sebagai kontributor (non-admin). Buka `/kontributor/saya/guru/create`.
   → **Verifikasi:** field Biografi tampil sebagai **editor kaya teks** dengan toolbar (tebal, miring, garis bawah, tautan, daftar, perataan) — bukan textarea polos. Tidak ada tombol heading/blockquote/gambar/YouTube.
1b. Langsung submit dengan editor biografi **kosong**.
   → **Verifikasi:** gagal dengan pesan *"Biografi wajib diisi."* (bukan lolos gara-gara `<p></p>`).
2. Isi nama, tulis biografi memakai **paragraf, teks tebal, satu daftar berpoin, dan satu tautan**, isi **tanggal wafat hijriah (hari/bulan/tahun)**, unggah foto profil dan **foto bersama tanpa caption**. Submit.
   → **Verifikasi:** gagal, muncul pesan *"Keterangan foto bersama wajib diisi."*, field lain masih terisi **termasuk isi editor beserta formatnya** (`old()` berfungsi untuk WYSIWYG).
3. Isi caption (mis. *"Bersama beliau di Sekumpul, 2004"*). Submit.
   → **Verifikasi:** redirect ke `/kontributor/saya` dengan pesan sukses. Cek `storage/app/public/guru/bersama/` — ada satu file `.webp`, ukurannya jauh lebih kecil dari file asli, **rasio aslinya utuh (tidak jadi kotak)**.
4. Sebagai **guest** (browser incognito), buka `/manaqib/{slug}` guru tadi.
   → **Verifikasi:** **404**. Buka juga `/manaqib` → guru itu tidak ada di daftar.
5. Kembali sebagai kontributor pemilik, buka `/manaqib/{slug}` yang sama.
   → **Verifikasi:** **200**, muncul banner *"menunggu moderasi admin"*, foto bersama + caption tampil di bawah atribusi.
6. Login sebagai Super Admin, buka `/admin/guru`, klik tab **Perlu Moderasi**.
   → **Verifikasi:** baris guru tadi menampilkan thumbnail foto bersama. Klik thumbnail → file penuh terbuka di tab baru. Klik **Setujui**.
7. Cek XP kontributor di `/kontributor/saya` → catat angkanya (mis. **X**).
8. Sebagai guest, buka `/manaqib/{slug}`.
   → **Verifikasi:** **200**, foto bersama + caption tampil. Guru juga muncul di daftar `/manaqib`. **Paragraf, teks tebal, daftar berpoin, dan tautan yang ditulis di langkah 2 semuanya tampil utuh** — tidak ada yang hilang. Cek juga `/guru/{slug}`.
8b. Cek kolom `teachers.biografi` di DB untuk baris tadi.
   → **Verifikasi:** berisi HTML (`<p>`, `<strong>`, `<ul>`), **tanpa** `<script>` atau atribut `on*`. Uji juga dengan mengirim POST manual berisi `<script>alert(1)</script>` di `biografi` → tersimpan sudah bersih.
9. Sebagai kontributor pemilik, buka form edit guru, **ganti foto bersama** dengan file lain. Submit.
   → **Verifikasi:** pesan sukses menyebut moderasi ulang. Sebagai guest, `/manaqib/{slug}` kembali **404**. File foto bersama yang lama sudah **hilang** dari `storage/app/public/guru/bersama/`.
10. Sebagai admin, setujui ulang di tab Perlu Moderasi.
    → **Verifikasi:** manaqib tayang lagi. **XP kontributor tetap X, tidak bertambah.** Tidak ada notifikasi "kontribusi disetujui" kedua di lonceng notifikasi kontributor.
11. Sebagai kontributor, buka form edit, centang **"Hapus foto bersama"**. Submit.
    → **Verifikasi:** manaqib **tetap tayang** untuk guest (tidak jadi 404), foto hilang dari halaman, file hilang dari disk, status tetap `approved`.
12. Sebagai kontributor, unggah lagi foto bersama + caption, minta admin menyetujui.
13. Sebagai admin, buka `/admin/guru/{id}/edit`, klik **Hapus Foto Bersama**.
    → **Verifikasi:** foto hilang, pesan sukses, dan manaqib **tetap tayang** untuk guest dengan status `approved`.
14. Login sebagai kontributor **lain**, coba PUT ke `/kontributor/saya/guru/{id}` milik orang lain (mis. lewat form yang dimodifikasi).
    → **Verifikasi:** **404**.

---

## Risiko & Trade-off

- **R-1 — Manaqib menghilang dari publik saat kontributor menyunting fotonya (D-4).** Pembaca yang sedang membuka tautan manaqib bisa menemukan 404 selama menunggu moderasi. Ini konsekuensi yang dipilih secara sadar demi menutup celah "unggah foto layak dulu, ganti dengan foto tak layak setelah disetujui". Mitigasi: admin perlu memoderasi cepat; UI kontributor harus memperingatkan sebelum submit. **Follow-up yang disarankan:** notifikasi/badge ke admin saat ada re-moderasi.
- **R-2 — Celah yang sama masih terbuka untuk teks biografi.** Kontributor bisa menyunting `biografi` pada manaqib `approved` dan langsung tayang tanpa moderasi ulang (Skenario 5, perilaku existing). Spec ini **hanya** menutupnya untuk foto. Menutup jalur teks juga akan membuat setiap koreksi typo memicu moderasi ulang — perlu keputusan produk tersendiri (mis. hanya kalau perubahan > N karakter). **Diterima sebagai risiko terbuka.**
- **R-3 — Guard XP menyentuh semua jenis kontribusi.** `KhidmahService` dipakai majelis, jadwal, amalan, catatan, dan guru. Perubahannya kecil dan menutup celah farming yang sudah ada, tapi cakupannya lebih luas dari fitur foto. Test regresi XP wajib ada sebelum merge.
- **R-4 — Semantik "wafat" jadi kendali kontributor (D-8).** Kontributor yang salah mengisi tanggal wafat akan memindahkan guru dari daftar `/guru` (hidup) ke `/manaqib` (wafat), **dan** mengeluarkannya dari pilihan guru pada form majelis/jadwal. Karena semuanya melewati moderasi admin, dampaknya tertahan sebelum tayang — tapi form harus memberi keterangan jelas: *"Isi hanya jika guru sudah wafat. Tahun hijriah wajib agar masuk daftar manaqib."*
- **R-5 — Beban storage & memori.** Batas 8 MB per unggahan (D-10) memerlukan `upload_max_filesize`, `post_max_size`, dan `memory_limit` yang memadai di server produksi. Kalau `post_max_size` lebih kecil dari batas validasi, request dibuang PHP sebelum Laravel dan user melihat error yang membingungkan. **Cek konfigurasi produksi sebelum rilis.**
- **R-6 — Tanpa pernyataan hak cipta (D-11).** Platform sepenuhnya bergantung pada moderasi manual untuk foto hasil comot/hoax. Volume kontribusi yang naik akan membebani admin. Pertimbangkan checkbox pernyataan di iterasi berikutnya.
- **R-7 — Tidak ada lightbox.** Foto ditampilkan inline dengan `object-contain`; foto portrait beresolusi tinggi akan tampil panjang di mobile. Kalau jadi masalah, tambahkan pembatas tinggi + klik-untuk-perbesar sebagai follow-up.
- **R-8 — Toolbar kontributor lebih miskin daripada toolbar admin, dan itu disengaja.** Kontributor tidak dapat membuat heading atau kutipan blok, padahal admin bisa (walaupun hasilnya dibuang saat render — Temuan 4). Ini akan terasa sebagai "fitur admin lebih lengkap". Pilihannya bukan antara kaya vs miskin, melainkan antara **jujur** (kontributor hanya melihat tombol yang benar-benar berfungsi) vs **menipu** (tombol yang hasilnya diam-diam hilang). Spec memilih yang jujur. **Follow-up yang disarankan:** perlebar `HTML.Allowed` di `config/purifier.php` agar mencakup `h2,h3,blockquote,hr,s,mark`, lalu samakan toolbar keduanya — pekerjaan terpisah karena berdampak ke seluruh konten (post, artikel ilmiah, biografi lama yang sudah tersimpan).
- **R-9 — `clean()` hanya ditambahkan di jalur kontributor.** `GuruController` (admin) dan controller lain tetap menyimpan HTML mentah, mengandalkan `clean()` saat render. Admin adalah `Super Admin` (tepercaya), jadi risikonya rendah, tapi ini menyimpang dari perintah CLAUDE.md "always use `clean()` before persisting". Menyeragamkannya ke semua controller adalah pekerjaan terpisah — **dicatat sebagai follow-up**, bukan diselundupkan ke dalam scope ini.
- **R-10 — Editor bergantung pada CDN pihak ketiga.** TipTap di-`import` dari `https://esm.sh` saat runtime — sama seperti yang sudah berjalan di 6 halaman lain. Jika esm.sh tidak dapat dijangkau (jaringan kantor, blokir, CDN down), editor tidak muncul dan `<textarea>` pembawa nilainya `hidden`, sehingga **kontributor tidak bisa mengetik biografi sama sekali** (E-17). Mitigasi minimal yang harus ikut diimplementasikan: kalau editor gagal diinisialisasi dalam beberapa detik, **lepas class `hidden` dari textarea** agar kontributor tetap bisa menulis teks polos. Solusi sebenarnya (bundel TipTap lewat Vite) di luar scope.
- **R-12 — Skema produksi pernah diubah di luar migration (Temuan 6).** Migration rekonsiliasi menutup celah untuk `teachers`, tapi tidak ada jaminan tabel lain tidak mengalami hal serupa. Selama itu terjadi, `migrate:fresh` tidak bisa dipercaya untuk menyiapkan environment baru. **Follow-up:** bandingkan skema produksi dengan hasil `migrate:fresh` untuk seluruh tabel.
- **R-13 — Urutan hari memakai `CASE`, bukan `FIELD()`.** `User\GuruController::detail` semula mengurutkan jadwal dengan `FIELD()` yang hanya ada di MySQL, sehingga halaman `/guru/{slug}` tidak dapat diuji sama sekali. Diganti ke ekspresi `CASE` yang portabel dengan urutan identik. Pemakaian `FIELD()` di tempat lain (mis. `ListJadwalMajelis`) tidak disentuh.
- **R-11 — Biografi lama berupa teks polos.** Data manaqib yang sudah ada tersimpan tanpa tag. Saat dibuka di editor, TipTap membungkusnya jadi paragraf; baris-baris yang dulu dipisah newline bisa menyatu jadi satu paragraf panjang. Tidak merusak data (kontributor bisa merapikan), tapi perlu dicek pada satu-dua record lama saat verifikasi.
