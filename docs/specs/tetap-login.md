# Spesifikasi: Tetap Login (Persistent Session / "Remember Me")

Status: **Terimplementasi** — menunggu verifikasi manual §11 sebelum deploy
Tanggal: 2026-08-31
Penulis: hasil wawancara kebutuhan dengan pemilik produk

> **Catatan revisi setelah implementasi.** Rencana §5.3 semula memakai
> `config('jetstream.auth_session')`. Test membuktikan pendekatan itu **rusak** pada konfigurasi
> aplikasi ini: pengguna yang mengganti passwordnya sendiri ikut ter-logout dari perangkatnya
> sendiri. Diganti dengan middleware sendiri, `App\Http\Middleware\AuthenticateWebSession`. Alasan
> lengkap ada di §5.3; ini persis jenis kegagalan yang §10.2 sebut paling mahal, dan test-lah yang
> menangkapnya sebelum sampai ke pengguna.

---

## 1. Ringkasan

Pengguna Syaikhuna saat ini otomatis ter-logout setelah **2 jam tidak aktif**, karena aplikasi
tidak pernah menerbitkan cookie _remember me_ pada jalur login mana pun. Spesifikasi ini membuat
sesi login **bertahan lama tanpa perlu centang apa pun**, berlaku untuk semua jalur masuk
(email+password, Google OAuth, dan registrasi baru), untuk **semua role termasuk Super Admin**.

Sebagai penyeimbang keamanan, **mengganti atau me-reset password akan mencabut sesi di semua
perangkat lain**, dan cookie sesi dikeraskan (`Secure`) di produksi.

Latar belakang: **antisipasi akuisisi pengguna** — menurunkan friksi masuk, bukan merespons
keluhan spesifik. Aplikasi dipasang sebagai PWA (`public/site.webmanifest`, `display: standalone`),
sehingga pengguna memperlakukannya seperti aplikasi HP yang wajar tidak minta login berulang.

---

## 2. Perilaku Saat Ini (terverifikasi di kode)

| Aspek                             | Kondisi sekarang                                          | Bukti                                                                                                                                                                              |
| --------------------------------- | --------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Umur sesi idle                    | 120 menit                                                 | `SESSION_LIFETIME=120` (`.env`), `config/session.php:24`                                                                                                                            |
| Sesi mati saat browser ditutup    | Tidak                                                     | `config/session.php` → `'expire_on_close' => false`                                                                                                                                 |
| Penyimpanan sesi                  | Tabel `sessions` di MySQL                                 | `SESSION_DRIVER=database`; `database/migrations/2022_03_23_163443_create_sessions_table.php`                                                                                         |
| Checkbox "Ingat Saya"             | **Tidak ada**                                             | `resources/views/auth/login.blade.php` — form hanya punya `email` + `password`                                                                                                      |
| Cookie remember pada login email  | **Tidak pernah terbit**                                   | `AttemptToAuthenticate` membaca `$request->boolean('remember')` (`vendor/laravel/fortify/src/Actions/AttemptToAuthenticate.php:54`) — input itu tidak pernah dikirim                 |
| Cookie remember pada login Google | **Tidak pernah terbit**                                   | `app/Http/Controllers/GoogleAuthController.php:27, 35, 49` → `Auth::login($user)` tanpa argumen `$remember`                                                                          |
| Cookie remember pada registrasi   | **Tidak pernah terbit**                                   | `vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:64` → `$this->guard->login($user, $request->boolean('remember'))`                                          |
| Kolom `remember_token`            | Ada di tabel, **tidak pernah terpakai**                   | `database/migrations/2014_10_12_000000_create_users_table.php:20`; satu-satunya rujukan di `app/Models/User.php:57` (`$hidden`)                                                      |
| Middleware `AuthenticateSession`  | Terdaftar sebagai alias, **tidak dipasang di rute aplikasi** | Alias di `app/Http/Kernel.php:58`; rute aplikasi hanya `auth:sanctum` + `verified` (`routes/web.php:135`, `routes/web.php:229`)                                                      |
| `SESSION_SECURE_COOKIE`           | **Tidak di-set** meski `APP_URL=https://syaikhuna.id`     | tidak ada di `.env`; `config/session.php` → `env('SESSION_SECURE_COOKIE')` = `null`                                                                                                  |
| UI "Keluar dari perangkat lain"   | Komponen ada tapi hanya terjangkau admin                  | `resources/views/profile/show.blade.php:33`; rute `profile.show` hanya tertaut di navbar admin (`resources/views/components/app/navbar.blade.php:68`, `components/dropdown-profile.blade.php:39`) |

**Kesimpulan penyebab:** bukan bug, melainkan fitur yang memang belum pernah diimplementasikan.

---

## 3. Keputusan Produk (hasil wawancara)

| Aspek                      | Keputusan                                                       |
| -------------------------- | --------------------------------------------------------------- |
| Mekanisme                  | **Selalu aktif otomatis** — tanpa checkbox, tanpa opt-in        |
| Durasi                     | **Tanpa batas sampai logout** (praktis = 400 hari, lihat §7.1)  |
| Jalur login yang tercakup  | Login Google, login email+password, **dan** registrasi baru     |
| Super Admin                | **Sama seperti jamaah**, tanpa perlakuan khusus                 |
| Ganti / reset password     | **Mencabut semua sesi di perangkat lain**                       |
| Halaman kelola perangkat   | **Di luar scope**                                               |
| 2FA                        | Tidak dipakai secara nyata — hanya dicatat (§7.4)               |
| `SESSION_LIFETIME`         | **Dinaikkan** menjadi 7 hari                                    |
| Pengerasan cookie          | **Masuk scope** (`SESSION_SECURE_COOKIE`)                       |
| Akun dihapus / role dicabut | Tertangani otomatis oleh framework — cukup diverifikasi         |
| Test otomatis              | **Ada** — 21 test baru/diperluas, lihat §9.1                    |

---

## 4. Expected Behavior

1. Pengguna yang login lewat cara apa pun, lalu menutup browser/aplikasi dan membukanya lagi
   berminggu-minggu kemudian, **tetap dalam keadaan login** tanpa diminta kredensial.
2. Pengguna yang menekan **Keluar** benar-benar keluar: cookie sesi _dan_ cookie remember dihapus,
   dan kunjungan berikutnya adalah sebagai tamu.
3. Pengguna yang **mengganti password** dari pengaturan tetap login di perangkat yang dipakai untuk
   mengganti, tetapi **ter-logout di semua perangkat lain** pada permintaan berikutnya ke halaman
   yang butuh login.
4. Pengguna yang **me-reset password** lewat email (lupa password) ter-logout di semua perangkat lain.
5. Akun yang **dihapus** tidak lagi bisa masuk dengan cookie lama — pengguna kembali menjadi tamu.
6. Pengguna yang **role Super Admin-nya dicabut** langsung ditolak `IsAdmin`
   (`app/Http/Middleware/IsAdmin.php`) pada permintaan berikutnya ke `/admin/*`, meski sesinya masih hidup.
7. Perilaku identik pada semua role: Jamaah, Kontributor, Penulis, Super Admin.

---

## 5. Perubahan yang Diperlukan

### 5.1 Selalu "remember" pada rute Fortify (login, registrasi, 2FA)

**File baru:** `app/Http/Middleware/ForceRememberLogin.php`

Middleware yang menyisipkan `remember = true` ke dalam request sebelum controller Fortify membacanya:

```php
$request->merge(['remember' => true]);
```

**File diubah:** `config/fortify.php:105`

```php
'middleware' => ['web', \App\Http\Middleware\ForceRememberLogin::class],
```

**Alasan memilih pendekatan ini** (bukan hidden input di form, bukan `Fortify::authenticateThrough()`):

- Satu titik perubahan menutup **tiga** jalur sekaligus, karena semuanya membaca input yang sama:
    - `AttemptToAuthenticate` (`vendor/laravel/fortify/src/Actions/AttemptToAuthenticate.php:54`) — login email
    - `RegisteredUserController` (`vendor/laravel/fortify/src/Http/Controllers/RegisteredUserController.php:64`) — registrasi
    - `RedirectIfTwoFactorAuthenticatable` (`vendor/laravel/fortify/src/Actions/RedirectIfTwoFactorAuthenticatable.php:147`) — menyimpan `login.remember` ke sesi untuk jalur 2FA
- **Sisi server**, tidak bisa dilucuti pengguna (berbeda dengan hidden input di Blade).
- **Tidak menyalin pipeline Fortify.** Alternatif `Fortify::authenticateThrough()` mengharuskan kita
  menduplikasi array pipeline default (`AuthenticatedSessionController::loginPipeline()`), yang akan
  diam-diam basi setiap kali Fortify di-upgrade.

Middleware ini juga ikut jalan di rute Fortify lain (reset password, konfirmasi password). Itu tidak
berbahaya: controller-controller tersebut tidak pernah membaca input `remember`.

### 5.2 Login Google

**File diubah:** `app/Http/Controllers/GoogleAuthController.php`

Tiga pemanggilan `Auth::login($user)` (baris **27**, **35**, **49**) menjadi `Auth::login($user, true)`.

**Sekaligus (pengerasan yang sudah semestinya, lihat §10.3):** setelah login berhasil, panggil
`$request->session()->regenerate()`. Jalur Google saat ini **tidak** meregenerasi ID sesi — berbeda
dengan jalur Fortify yang melakukannya lewat `PrepareAuthenticatedSession`. Tanpa ini, ID sesi
pra-login tetap dipakai pasca-login (_session fixation_), dan dampaknya membesar justru karena sesi
kini berumur panjang. Metode `handleGoogleCallback()` sudah menerima `Request` di signature-nya.

### 5.3 Cabut sesi lain saat password berubah

**File baru:** `app/Http/Middleware/AuthenticateWebSession.php`, didaftarkan sebagai alias
`auth.web_session` di `app/Http/Kernel.php`.

**File diubah:** `routes/web.php` — pasang alias itu di **ketiga** grup rute terproteksi:

- baris **93**: `['noindex', 'auth', 'auth.web_session']` — menaungi `/pengaturan-akun` dan rute
  verifikasi email. Grup ini memakai `auth` polos, **bukan** `auth:sanctum` seperti dua grup lain,
  sehingga bentuknya berbeda dan mudah terlewat — padahal justru di situ halaman akun jamaah berada.
- baris **135**: `['noindex', 'auth:sanctum', 'auth.web_session', 'verified']`
- baris **229**: `['noindex', 'auth:sanctum', 'auth.web_session', 'verified', 'is_admin']`

#### Kenapa bukan middleware bawaan

Rencana awal memakai `config('jetstream.auth_session')`. Test membuktikan itu **rusak di sini**.
Dua middleware bawaan tersedia dan masing-masing punya satu lubang:

| Middleware | Cek hash di cookie recaller | Kunci sesi yang dipakai |
| --- | --- | --- |
| `Jetstream\...\AuthenticateSession` | ✅ ada | ❌ `password_hash_{Auth::getDefaultDriver()}` |
| `Sanctum\...\AuthenticateSession` | ❌ tidak ada | ✅ `config('sanctum.guard')` = `web` |

- **Lubang Jetstream.** Pada rute ber-`auth:sanctum`, middleware `auth` memanggil
  `shouldUse('sanctum')` sehingga driver default menjadi `sanctum` dan kuncinya
  `password_hash_sanctum`. Sementara form ganti password Jetstream berjalan lewat `livewire/update`
  yang **hanya** ber-middleware `web` (terverifikasi di `route:list`), jadi ia menulis
  `password_hash_web`. Kunci `password_hash_sanctum` tidak pernah ikut disegarkan: listener yang
  seharusnya melakukannya hanya didaftarkan di `JetstreamServiceProvider::bootInertia()`
  (baris 192-196), sedangkan `config('jetstream.stack')` proyek ini adalah **livewire**.
  Akibatnya pengguna yang mengganti passwordnya sendiri **ter-logout dari perangkatnya sendiri**.
- **Lubang Sanctum.** Versinya memakai kunci yang benar dan tidak punya bug di atas, tetapi tidak
  memeriksa cookie recaller sama sekali — perangkat yang sesinya sudah kedaluwarsa dan hanya
  berbekal cookie remember tidak akan pernah tercabut. Padahal justru itu kondisi yang paling umum
  setelah fitur ini aktif.

`AuthenticateWebSession` menggabungkan sisi benar keduanya dan **mengunci guard ke `web` secara
eksplisit**, sehingga perilakunya identik di grup `auth` maupun `auth:sanctum`. Middleware ini
mengimplementasikan `Illuminate\Contracts\Session\Middleware\AuthenticatesSessions` agar tetap
diurutkan setelah middleware autentikasi oleh `$middlewarePriority`.

**Cara kerjanya:**

- Cookie remember (_recaller_) berisi `id|remember_token|password_hash`
  (`SessionGuard::queueRecallerCookie()`, baris 582). Bila pengguna dikenali lewat cookie
  (`viaRemember()`), hash di ruas ketiga dicocokkan dengan hash terkini; beda → logout.
- Sesi aktif menyimpan `password_hash_web`; beda dengan hash terkini → logout.
- Sesi yang **belum punya** kunci itu diisi, bukan dicabut — supaya deploy tidak menendang seluruh
  pengguna yang sedang login.

#### Listener penyegar sesi untuk rute HTTP ganti password

**File diubah:** `app/Providers/EventServiceProvider.php`

Rute `PUT /user/password` (`Fortify\...\PasswordController`) memicu `PasswordUpdatedViaController`
tetapi **tidak ada listener yang terdaftar** di aplikasi ini, dengan alasan yang sama seperti di
atas: Jetstream hanya mendaftarkannya pada stack Inertia. Tanpa listener, ganti password lewat rute
itu membuat pengguna ter-logout dari perangkatnya sendiri. Proyek ini kini mendaftarkannya sendiri
untuk menyegarkan `password_hash_web`.

Form Livewire Jetstream tidak butuh ini — ia sudah menyegarkan sesi sendiri
(`vendor/laravel/jetstream/src/Http/Livewire/UpdatePasswordForm.php:36`), dan kuncinya kini cocok
karena middleware sudah dikunci ke guard `web`.

#### Tiga jalur ganti password, bukan dua

Selain form Livewire Jetstream dan `PUT /user/password`, **`/pengaturan-akun`
(`SettingController@update`, baris 58-62) juga mengubah password** — jalur ini tidak tercatat di
rencana awal dan ditemukan saat verifikasi manual. Ketiganya tertutup karena middleware menyegarkan
`password_hash_web` di akhir setiap request, bukan bergantung pada masing-masing controller.

#### Cookie recaller ikut diterbitkan ulang

`AuthenticateWebSession::refreshRecallerCookie()` menerbitkan ulang cookie recaller bila hash
password berubah di dalam request tersebut.

Tanpa ini ada bug halus yang merusak inti fitur: perangkat yang baru saja mengganti passwordnya
sendiri **tetap memegang cookie berisi hash lama**. Sesinya masih hidup sehingga saat itu tidak
terasa apa-apa — tetapi begitu sesi kedaluwarsa (kini 7 hari) dan pengguna kembali hanya berbekal
cookie, cocokan hash menolaknya. Artinya **setiap pengguna yang pernah mengganti password akan
kehilangan manfaat "tetap login" sekitar seminggu kemudian, tanpa sebab yang terlihat.**

Polanya mengikuti `SessionGuard::logoutOtherDevices()` (baris 693-703), yang juga menerbitkan ulang
recaller setelah hash password berubah. Karena `queueRecallerCookie()` dan `getRememberDuration()`
keduanya `protected`, cookie disusun sendiri lewat `Cookie::queue()` dengan konstanta
`RECALLER_DURATION` yang menyamai `SessionGuard::$rememberDuration`.

**Konsekuensi penting:** **jangan** menambah `setRememberToken()` manual di
`app/Actions/Fortify/UpdateUserPassword.php` atau `app/Actions/Fortify/ResetUserPassword.php`.
Mekanisme hash-di-cookie sudah cukup, dan mengganti `remember_token` secara manual justru akan
mematikan cookie perangkat **milik pengguna sendiri** — Laravel hanya menyegarkan cookie itu lewat
`logoutOtherDevices()`, yang tidak dipanggil di jalur ini.

### 5.4 Konfigurasi environment

**File diubah:** `.env` (produksi) dan `.env.example`

```
SESSION_LIFETIME=10080          # 7 hari (sebelumnya 120 menit)
SESSION_SECURE_COOKIE=true      # produksi saja; jangan di-set di .env lokal (http://localhost)
```

`SESSION_LIFETIME` yang lebih panjang mengurangi siklus buat-hapus baris `sessions` dan membuat sesi
aktif lebih awet. `SESSION_SECURE_COOKIE=true` memastikan cookie berumur panjang tidak pernah
dikirim lewat HTTP polos. Aplikasi sudah berada di belakang proxy TLS dan `TrustProxies`
(`app/Http/Middleware/TrustProxies.php`) sudah mempercayai `X-Forwarded-Proto`.

### 5.5 Tanpa perubahan data model

**Tidak ada migration baru.** Kolom `remember_token` sudah ada sejak
`database/migrations/2014_10_12_000000_create_users_table.php:20` dan selama ini hanya menganggur;
fitur ini mulai mengisinya. Tabel `sessions` juga tidak berubah.

---

## 6. Ringkasan File Terdampak

| File | Jenis | Perubahan |
| --- | --- | --- |
| `app/Http/Middleware/ForceRememberLogin.php` | **Baru** | Menyisipkan `remember = true` ke request Fortify |
| `app/Http/Middleware/AuthenticateWebSession.php` | **Baru** | Pencabutan sesi, guard dikunci ke `web` (§5.3) |
| `config/fortify.php` (baris 105) | Ubah | Daftarkan `ForceRememberLogin` |
| `app/Http/Kernel.php` | Ubah | Alias `auth.web_session` |
| `app/Providers/EventServiceProvider.php` | Ubah | Listener `PasswordUpdatedViaController` penyegar sesi |
| `app/Http/Controllers/GoogleAuthController.php` | Ubah | `Auth::login($user, true)` ×3 + `session()->regenerate()` |
| `routes/web.php` (93, 135, 229) | Ubah | Tambah `auth.web_session` ke ketiga grup |
| `.env` (produksi) | Ubah | `SESSION_LIFETIME=10080`, `SESSION_SECURE_COOKIE=true` |
| `.env.example` | Ubah | Dokumentasikan kedua variabel |
| `tests/Feature/PersistentLoginTest.php` | **Baru** | 7 test (§9.1) |
| `tests/Feature/SessionRevocationTest.php` | **Baru** | 11 test (§9.1) |
| `tests/Feature/GoogleAuthControllerTest.php` | Ubah | +3 test; memakai `setUp()` dan pola mock Socialite yang sudah ada |

**Tidak disentuh:** `app/Models/User.php`, `app/Actions/Fortify/*`,
`resources/views/auth/login.blade.php`, `config/session.php`, `config/auth.php`, migration mana pun.

> Pint (`./vendor/bin/pint --dirty`) ikut merapikan tiga baris lama di `GoogleAuthController.php`
> yang tidak berkaitan dengan fitur ini (urutan import, spasi `!`, dan operator `.`).

---

## 7. Detail Perilaku yang Perlu Diketahui

### 7.1 "Tanpa batas" secara praktis = 400 hari sejak **login terakhir**

`SessionGuard::$rememberDuration` bernilai `576000` menit ≈ **400 hari**
(`vendor/laravel/framework/src/Illuminate/Auth/SessionGuard.php:62`) — bukan selamanya. Angka ini
juga sudah merupakan batas maksimum umur cookie yang diterima Chrome dan Safari, jadi menaikkannya
tidak ada gunanya.

Yang lebih penting: cookie remember hanya diterbitkan ulang saat **login**, bukan pada tiap
kunjungan. Jadi jam 400 hari berjalan sejak login terakhir, bukan sejak aktivitas terakhir. Untuk
pengguna aktif hal ini tidak akan pernah terasa; ini dicatat agar tidak ada kejutan di kemudian hari.
**Menyegarkan cookie pada tiap kunjungan berada di luar scope.**

### 7.2 Interaksi `SESSION_LIFETIME` dengan cookie remember

Keduanya dua lapis yang berbeda: setelah 7 hari idle sesi mati dan barisnya dihapus, lalu Laravel
membangun sesi baru secara diam-diam dari cookie remember. Pengguna tidak melihat apa pun. Menaikkan
`SESSION_LIFETIME` **bukan** yang membuat login bertahan lama — cookie remember-lah yang melakukannya.

### 7.3 Rute publik tidak ikut mencabut sesi

`AuthenticateSession` hanya dipasang di dua grup rute terproteksi (§5.3). Perangkat lain yang setelah
penggantian password hanya membuka halaman publik (majelis, guru, tulisan) masih akan terlihat
"login" di header sampai ia menyentuh halaman yang butuh autentikasi. Ini diterima: halaman publik
tidak memuat data pribadi, dan aksi apa pun yang berarti melewati grup terproteksi.

### 7.4 2FA

`Features::twoFactorAuthentication()` aktif di `config/fortify.php:153`, tetapi menurut pemilik produk
belum ada pengguna nyata yang memakainya. Jika suatu saat dipakai: setelah sesi diingat, OTP **tidak**
akan diminta lagi sampai pengguna logout. Ini perilaku bawaan Laravel dan diterima.

### 7.5 Akun dihapus / role dicabut

Tidak perlu kode baru:

- **Akun dihapus** (`Features::accountDeletion()` aktif, `config/jetstream.php:65`) — pencarian
  `remember_token` tidak menemukan baris, pengguna kembali menjadi tamu.
- **Role dicabut** — `IsAdmin` (`app/Http/Middleware/IsAdmin.php`) memeriksa `hasRole('Super Admin')`
  pada setiap permintaan `/admin/*`, jadi penolakan terjadi seketika tanpa bergantung pada umur sesi.

---

## 8. Di Luar Scope

1. **Halaman kelola perangkat untuk jamaah.** Komponen Jetstream `LogoutOtherBrowserSessionsForm`
   sudah terpasang di `resources/views/profile/show.blade.php:33` tetapi hanya tertaut di navbar
   admin. Menyambungkannya ke `/pengaturan-akun` adalah pekerjaan terpisah.
2. **Tombol "Keluar dari semua perangkat"** untuk pengguna biasa.
3. **Pencabutan paksa satu pengguna oleh admin.**
4. Checkbox "Ingat Saya" atau pengaturan durasi per pengguna.
5. Perlakuan durasi khusus untuk Super Admin.
6. Konfirmasi password ulang untuk aksi admin sensitif.
7. Penyegaran cookie remember pada tiap kunjungan (§7.1).
8. Otentikasi biometrik / passkey.
9. Perubahan apa pun pada token API Sanctum (`personal_access_tokens`) — jalur berbeda, tidak terpengaruh.
10. Pembersihan terjadwal tabel `sessions` di luar mekanisme _lottery_ bawaan.

---

## 9. Testing

### 9.1 Test Otomatis

21 test, semuanya hijau. Memakai `RefreshDatabase` dan gaya PHPUnit yang sudah dipakai proyek.
Nama cookie remember diambil dari framework, tidak di-hardcode:

```php
$recaller = Auth::guard('web')->getRecallerName(); // remember_web_<sha1>
```

Tiga mekanik yang perlu diketahui sebelum menyentuh test ini:

- Cookie remember **terenkripsi** (`EncryptCookies::$except` kosong), jadi assertion dilakukan pada
  **keberadaan** cookie: `assertCookie($recaller)` tanpa argumen nilai.
- Untuk mensimulasikan perangkat lain, recaller dirakit manual (`"{id}|{token}|{password_hash}"`,
  sesuai `SessionGuard::queueRecallerCookie()` baris 582) lalu dikirim dengan **`withCookie()`** —
  **bukan** `withUnencryptedCookie()`, yang justru akan gagal didekripsi `EncryptCookies` dan
  berubah menjadi `null`.
- `assertGuest()` tanpa argumen memeriksa guard **default**, yang pada rute ber-`auth:sanctum`
  sudah bergeser menjadi `sanctum` dan masih menyimpan user hasil cache request. Karena itu test
  pencabutan memakai `assertGuest('web')`.

#### `tests/Feature/PersistentLoginTest.php` (baru, 7 test)

| Test | Yang dibuktikan |
| --- | --- |
| `login_email_menerbitkan_cookie_remember` | Cookie terbit; `remember_token` terisi |
| `registrasi_menerbitkan_cookie_remember` | Pengguna baru langsung "diingat" |
| `super_admin_juga_mendapat_cookie_remember` | Tidak ada perlakuan khusus per role |
| `sesi_kedaluwarsa_dipulihkan_dari_cookie_remember` | **Test inti fitur** — tanpa sesi sama sekali, hanya berbekal recaller, pengguna tetap dikenali |
| `logout_menghapus_cookie_remember` | `assertCookieExpired($recaller)` |
| `cookie_remember_lama_tidak_berlaku_setelah_logout` | `remember_token` di-cycle, salinan cookie lama ikut mati |
| `pengguna_belum_verifikasi_email_tetap_dicegat` | Sesi panjang tidak melangkahi `verified` |

#### `tests/Feature/SessionRevocationTest.php` (baru, 11 test)

Area paling berisiko. Diuji dari **kedua** sisi.

_Harus ter-logout:_

| Test | Yang dibuktikan |
| --- | --- |
| `ganti_password_mencabut_sesi_perangkat_lain` | Sesi dengan hash basi ditolak |
| `cookie_remember_dengan_hash_password_lama_ditolak` | Perangkat yang hanya berbekal cookie ikut tercabut |
| `reset_password_mencabut_sesi_perangkat_lain` | Jalur "Lupa Password?" lewat HTTP sungguhan |
| `pencabutan_berlaku_di_grup_pengaturan_akun` | Grup `routes/web.php:93` (`auth` polos) |
| `pencabutan_berlaku_di_area_admin` | Grup `routes/web.php:229` |

_Tidak boleh ter-logout — penjaga regresi termahal:_

| Test | Yang dibuktikan |
| --- | --- |
| `perangkat_yang_mengganti_password_tetap_login` | Sesi dengan hash terkini tidak diganggu |
| `cookie_remember_diterbitkan_ulang_saat_ganti_password_sendiri` | Lewat `/pengaturan-akun`. Response menerbitkan recaller baru berisi hash baru |
| `cookie_remember_baru_masih_berlaku_setelah_sesi_habis` | Cookie baru itu benar-benar diterima setelah sesi dibuang — inti fitur tetap utuh bagi pengguna yang pernah ganti password |
| `sesi_tanpa_hash_password_tidak_dicabut` | Sesi lama dari sebelum deploy diisi, bukan ditendang |
| `ganti_password_tidak_melogout_perangkat_sendiri` | Lewat `PUT /user/password`. **Test inilah yang menemukan bug §5.3** — diverifikasi gagal saat listener dinonaktifkan |
| `kunci_sesi_tetap_milik_guard_web_di_rute_sanctum` | Mengunci akar penyebabnya: rute `auth:sanctum` tetap memakai `password_hash_web`, bukan `password_hash_sanctum` |

#### `tests/Feature/GoogleAuthControllerTest.php` (diperluas, +3 test)

| Test | Yang dibuktikan |
| --- | --- |
| `login_google_menerbitkan_cookie_remember` | Cabang pengguna lama |
| `login_google_menerbitkan_cookie_remember_untuk_pengguna_baru` | Cabang pengguna baru |
| `login_google_meregenerasi_id_sesi` | Menutup celah session fixation §10.3 |

**Batasan yang diketahui.** Round-trip penuh form Livewire ganti password tidak dapat diuji
in-process: pada `Livewire::test()`, `request()->session()` adalah instance yang **berbeda** dari
`session()` milik test, sehingga tulisan sesi oleh komponen tidak terbaca oleh request berikutnya.
Jalur itu diuji lewat rute HTTP `PUT /user/password` (yang memicu masalah dan perbaikan yang sama),
ditambah `kunci_sesi_tetap_milik_guard_web_di_rute_sanctum` yang mengunci akar penyebabnya. Perilaku
form Livewire yang sesungguhnya tetap diverifikasi manual di §11 langkah 6.
### 9.2 Acceptance Criteria

Kolom **Verifikasi** menunjukkan apa yang menutup tiap kriteria: nama test dari §9.1, atau `manual`
untuk hal yang memang tidak bisa dijangkau test fitur (flag cookie di browser sungguhan, perilaku PWA,
konfigurasi produksi).

### Penerbitan cookie

| AC | Kriteria | Verifikasi |
| --- | --- | --- |
| **AC-1** | Login `/login` email+password menerbitkan cookie `remember_web_*`; `Max-Age` ± 400 hari | `login_email_menerbitkan_cookie_remember` + `manual` (nilai `Max-Age` di DevTools) |
| **AC-2** | Login **Google** menerbitkan cookie remember | `login_google_menerbitkan_cookie_remember` (dua cabang) |
| **AC-3** | Registrasi `/register` menerbitkan cookie remember | `registrasi_menerbitkan_cookie_remember` |
| **AC-4** | `users.remember_token` terisi setelah AC-1/2/3 (sebelumnya selalu `NULL`) | ketiga test penerbitan cookie di atas |
| **AC-5** | ID sesi berubah sebelum vs sesudah login Google | `login_google_meregenerasi_id_sesi` |

### Sesi bertahan

| AC | Kriteria | Verifikasi |
| --- | --- | --- |
| **AC-6** | Hapus **hanya** cookie sesi → muat ulang → tetap login | `sesi_kedaluwarsa_dipulihkan_dari_cookie_remember` |
| **AC-7** | Hapus baris `sessions` di database → muat ulang rute terproteksi → tetap login | `sesi_kedaluwarsa_dipulihkan_dari_cookie_remember` + `manual` |
| **AC-8** | Tutup total browser/PWA, buka lagi → tetap login | `manual` (perilaku PWA, di luar jangkauan test fitur) |
| **AC-9** | Berlaku sama untuk Super Admin di `/admin/dashboard` | `super_admin_juga_mendapat_cookie_remember` + `manual` |

### Logout & pencabutan

| AC | Kriteria | Verifikasi |
| --- | --- | --- |
| **AC-10** | **Keluar** menghapus cookie sesi **dan** cookie remember | `logout_menghapus_cookie_remember`, `cookie_remember_lama_tidak_berlaku_setelah_logout` |
| **AC-11** | Ganti password di A → A tetap login, B ter-logout | seluruh blok "harus/tidak boleh ter-logout" di §9.1 |
| **AC-12** | Reset password lewat email → perangkat lain ter-logout | `reset_password_mencabut_sesi_perangkat_lain` |
| **AC-13** | Setelah AC-11, B bisa login lagi dengan password baru dan kembali "diingat" | `login_email_menerbitkan_cookie_remember` + `manual` |

### Tidak ada regresi

| AC | Kriteria | Verifikasi |
| --- | --- | --- |
| **AC-14** | `php artisan test` memberi hasil **sama seperti sebelum perubahan**, ditambah 21 test baru yang hijau | **sudah dijalankan**, lihat catatan baseline di bawah |
| **AC-15** | Halaman publik (majelis, guru, tulisan, pustaka) tetap terbuka tanpa login | suite `tests/Feature/Visibility/*` dan `Seo/*` yang sudah ada |
| **AC-16** | Pengguna belum verifikasi email tetap dicegat `verified` | `pengguna_belum_verifikasi_email_tetap_dicegat` |
| **AC-17** | Pembatasan laju login 5/menit masih berlaku | `manual` |

**Hasil aktual:** baseline sebelum perubahan **17 gagal / 4 skipped / 438 lulus**; setelah
perubahan **17 gagal / 4 skipped / 459 lulus**. Daftar 17 kegagalan **identik** (dibandingkan
baris per baris) — 21 test baru lulus, nol regresi.

> **Baseline sudah merah sebelum fitur ini dikerjakan.** Pada `main` yang bersih,
> `AuthenticationTest::test_users_can_authenticate_using_the_login_screen` **gagal**: test
> mengharapkan redirect ke `RouteServiceProvider::HOME` (`/admin/dashboard`,
> `app/Providers/RouteServiceProvider.php:21`), sedangkan `app/Http/Responses/LoginResponse.php`
> mengarahkan pengguna non-admin ke `route('beranda')` (= `/`). Test-nya yang basi, bukan kodenya
> yang salah. **Catat hasil `php artisan test` sebelum mulai** agar perbandingan bermakna, dan jangan
> "memperbaiki" test ini sebagai bagian dari fitur — itu di luar scope (§8).

### Produksi

| AC | Kriteria | Verifikasi |
| --- | --- | --- |
| **AC-18** | Cookie sesi & remember membawa `Secure`, `HttpOnly`, `SameSite=Lax` di produksi | `manual` |
| **AC-19** | Login di `localhost` (HTTP) masih berfungsi — `SESSION_SECURE_COOKIE` tidak di-set `true` di `.env` lokal | `manual` |

---

## 10. Risiko & Trade-off

### 10.1 Perangkat bersama

Keputusan "selalu aktif tanpa checkbox" berarti pengguna yang login di HP pinjaman atau komputer
warnet akan tetap login di sana sampai seseorang menekan Keluar. Ini konsekuensi sadar dari memilih
kemudahan; mitigasi paling murah adalah membuat tombol Keluar mudah ditemukan, dan menyediakan
halaman kelola perangkat di iterasi berikutnya (§8.1).

### 10.2 `AuthenticateSession` dapat me-logout pengguna secara tak terduga

Ini tetap bagian paling berisiko dari fitur ini. Middleware `AuthenticateSession` melempar
`AuthenticationException` begitu hash password di sesi atau di cookie tidak cocok, dan middleware ini
akan berlaku pada **seluruh** area terproteksi aplikasi sekaligus (`routes/web.php:93`, `:135`, `:229`).
Kesalahan kecil di sini berdampak ke semua pengguna login, bukan ke satu fitur.

**Mitigasi:** `SessionRevocationTest` (§9.1) mengunci perilaku ini dari kedua sisi — lima test untuk
yang **harus** ter-logout, empat untuk yang **tidak boleh**.

**Risiko ini terbukti nyata, bukan teoretis.** Pendekatan yang disetujui di rencana awal
(`config('jetstream.auth_session')`) justru mengandung persis kegagalan itu: pengguna yang mengganti
passwordnya sendiri ter-logout dari perangkatnya sendiri. Blok "tidak boleh ter-logout" menangkapnya
sebelum sampai ke pengguna, dan §5.3 kini memakai middleware sendiri. `ganti_password_tidak_melogout_
perangkat_sendiri` diverifikasi memang gagal bila listener penyegar sesi dinonaktifkan — jadi ia
penjaga regresi yang sungguhan, bukan test yang lulus secara kebetulan.

Sisa risiko yang tidak tertutup test: perilaku nyata di PWA dan urutan middleware di lingkungan
produksi. Karena itu langkah **6–7** pada §11 tetap dijalankan manual sebelum deploy.

### 10.3 Session fixation di jalur Google (bug yang sudah ada)

`GoogleAuthController` tidak pernah meregenerasi ID sesi. Kelemahan ini **sudah ada sekarang**, tetapi
sesi berumur 400 hari memperbesar jendela eksploitasinya secara drastis. Karena itu perbaikannya
dimasukkan ke dalam scope (§5.2) meski secara teknis terpisah dari fitur "tetap login".

### 10.4 Pertumbuhan tabel `sessions`

`SESSION_LIFETIME` 120 menit → 7 hari membuat baris `sessions` bertahan ~84× lebih lama. Pembersihan
mengandalkan _lottery_ 2/100 bawaan (`config/session.php` → `'lottery' => [2, 100]`), tanpa cron. Pada
skala pengguna sekarang ini aman, tetapi seiring akuisisi pengguna tabel ini perlu dipantau. Jika
mengganggu, opsi ke depan: turunkan `SESSION_LIFETIME` (cookie remember tetap menjaga pengalaman
"tidak pernah logout", §7.2) atau pindahkan sesi ke Redis.

### 10.5 Nilai keamanan `remember_token` mulai berarti

Selama ini kolom `remember_token` selalu `NULL`. Setelah fitur ini, kolom tersebut menjadi kredensial
berumur panjang. Kolom sudah masuk `$hidden` di `app/Models/User.php:57` — jangan pernah keluarkan
lewat API, log, atau ekspor data. Perlu diperiksa ulang jika suatu saat ada endpoint yang menyerialkan
model `User` secara utuh.

### 10.6 `.env` lokal memakai `APP_URL` produksi

`.env` di mesin pengembangan memuat `APP_ENV=local` bersama `APP_URL=https://syaikhuna.id`. Perlu
kehati-hatian agar `SESSION_SECURE_COOKIE=true` **tidak** ikut ke `.env` lokal, karena akan membuat
login di `http://localhost` gagal secara membingungkan (login tampak berhasil lalu langsung kembali
menjadi tamu). AC-19 ada khusus untuk menjaga hal ini.

---

## 11. Verifikasi End-to-End

Test otomatis (§9.1) sudah mengunci logika di level HTTP. Skenario di bawah menutup yang **tidak bisa**
dijangkau test fitur: perilaku PWA sungguhan, flag cookie di browser, dan konfigurasi produksi.
Jalankan di staging/produksi setelah deploy — **setelah** `php artisan test` hijau, bukan sebagai
penggantinya.

**Persiapan:** dua browser berbeda — A (Chrome desktop) dan B (PWA Syaikhuna terpasang di HP). Satu
akun jamaah uji dengan email terverifikasi.

0. **Prasyarat:** `php artisan test` dijalankan dan dibandingkan dengan baseline yang dicatat sebelum
   perubahan; 15 test baru hijau. Jangan mulai langkah manual sebelum ini lulus. → _AC-14_
1. Di **B**, buka aplikasi sebagai tamu → pastikan halaman publik terbuka normal.
2. Di **B**, login lewat **Google**. Periksa: masuk ke `/beranda`, dan `users.remember_token` untuk
   akun itu kini terisi di database. → _AC-2, AC-4_
3. Di **B**, tutup PWA sepenuhnya (bukan sekadar minimize), tunggu, buka lagi → masih login. → _AC-8_
4. Di database, **hapus baris `sessions`** milik akun itu. Di **B**, buka `/pustaka-saya` → masih
   login, dan baris `sessions` baru muncul untuk akun tersebut. Inilah bukti cookie remember bekerja,
   bukan sekadar sesi yang belum kedaluwarsa. → _AC-7_
5. Di **A**, login akun yang sama dengan **email + password**. Periksa cookie `remember_web_*` ada
   dengan `Max-Age` ± 400 hari dan flag `Secure` + `HttpOnly`. → _AC-1, AC-18_
6. Di **A**, **ganti password**. Ulangi untuk **ketiga** jalur yang bisa mengubah password:
   `/pengaturan-akun`, form Jetstream di `/user/profile`, dan (bila dipakai) `PUT /user/password`.
   Tiap kali: muat ulang beberapa halaman terproteksi di A → A tetap login. → _AC-11 (bagian A)_
   - ✅ **`/pengaturan-akun` sudah diverifikasi pemilik produk (2026-08-31): A tetap login.**
     Verifikasi inilah yang memunculkan jalur ketiga dan bug cookie recaller basi di §5.3.
7. Di **B**, buka `/pustaka-saya` → **ter-logout**, diarahkan ke `/login`. Ulangi dari kondisi login
   untuk `/pengaturan-akun` (grup `routes/web.php:93`, bentuk middleware-nya berbeda) → juga
   ter-logout. → _AC-11 (bagian B)_
8. Di **B**, login lagi dengan password baru → berhasil, dan cookie remember baru terbit. → _AC-13_
9. Di **A**, tekan **Keluar**. Periksa cookie sesi dan cookie remember keduanya hilang; buka
   `/pustaka-saya` → diarahkan ke `/login`. → _AC-10_
10. Di **A**, login sebagai **Super Admin**, buka `/admin/dashboard`, tutup browser, buka lagi →
    masih login dan masih bisa mengakses area admin. → _AC-9_
**Kriteria lulus:** langkah **0** lulus lebih dulu, lalu seluruh langkah 1–10 berperilaku seperti
tertulis — khususnya langkah **4** (cookie remember benar-benar berfungsi di PWA sungguhan) dan
langkah **7** (pencabutan sesi berlaku di kedua bentuk grup rute).
