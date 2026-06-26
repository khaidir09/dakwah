# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Syaikhuna** adalah platform untuk Dakwah dan pengetahuan Islam di Kalimantan (Kalimantan, Indonesia). Menghubungkan komunitas dengan kalangan studi Islam _majelis ilmu_ dalam silsilah keilmuan Syekh Muhammad Arsyad Al-Banjari dan KH. Muhammad Zaini bin Abdul Ghani (Abah Guru Sekumpul).
Visi utama Syaikhuna bukanlah sekadar menjadi aplikasi jadwal pengajian. Visi besarnya adalah menjadi "Sistem Operasi Digital" bagi kehidupan religius masyarakat Banjar.

Pengguna utama:

- **Admin**: Mengelola semua data.
- **Jamaah**: Mengakses informasi.
- **Kontributor**: Pengguna jamaah yang ikut berkontribusi menambahkan data majelis/guru/amalan/acara, namun dengan moderasi admin.

Tujuan bisnis utama:

- Menjadi wadah/platform data majelis, jadwal pengajian, guru, acara agama yang terpusat, terstruktur dan terpercaya di daerah Kalimantan Selatan, Kalimantan Timur, Kalimantan Tengah.
- Mengakuisisi banyak pengguna,

## Commands

```bash
# Frontend
npm run dev       # Vite dev server with HMR
npm run build     # Production asset build

# Backend
php artisan serve        # Local dev server
php artisan migrate      # Run migrations
php artisan db:seed      # Seed database

# Testing
php artisan test                    # Run all tests
php artisan test --filter=TestName  # Run a single test
./vendor/bin/phpunit                # Alternative PHPUnit runner

# Code style
./vendor/bin/pint                   # Fix code style (PSR-12)

# Artisan commands khusus aplikasi
php artisan majelis:invite           # Generate signed URL onboarding majelis (default: 1440 menit)
php artisan majelis:invite 60        # Link valid 60 menit
php artisan app:make-author {email}  # Assign role Penulis ke user
```

Tests use SQLite in-memory; the database is automatically switched in `phpunit.xml`.

## Architecture

### Stack

- **Framework**: Laravel 11 with TALL stack (Tailwind CSS v4, Alpine.js via Livewire, Livewire 3)
- **Auth**: Jetstream + Fortify (2FA, email verify, API tokens) + Google OAuth (Socialite)
- **Roles**: Spatie Laravel Permission v6 — dua role yang digunakan:
  - `Super Admin` — akses penuh ke semua area admin
  - `Penulis` — hanya bisa membuat dan mengedit tulisan miliknya sendiri (`kelola-tulisan`); assign via `php artisan app:make-author {email}`
- **Database**: MySQL (dev), SQLite in-memory (tests)
- **Images**: Intervention Image — generates both `large` and `thumb` variants in `public/` disk
- **HTML sanitization**: mews/purifier — use `clean()` helper on all user-supplied HTML content
- **AI Library Chat**: External FastAPI service (Open Notebook) via `OpenNotebookService` — RAG-based chat over PDFs
- **Push notifications**: OneSignal via `OneSignalService`
- **Hijri calendar**: Cached API via `HijriService` (24h cache, `api.myquran.com`)
- **Indonesian geography**: `laravolt/indonesia` (Province/City/District/Village) linked to User, Assembly, Teacher, Event

### Route Access Levels

Three tiers defined in `routes/web.php`:

1. **Public** — browsable without login: majelis, guru, video, event, wirid, pustaka, tulisan, manaqib, jadwal-majelis, artikel
2. **Authenticated + verified** (`auth:sanctum` + `verified`) — self-service: kelola-majelis, kelola-acara-majelis, kelola-tulisan, kelola-mitra, kelola-artikel-ilmiah, account settings, favorites, schedule notes
3. **Admin** (`/admin/*`, `IsAdmin` middleware) — requires `Super Admin` role via Spatie; full CRUD over all content

### Controller Structure

Dual-controller pattern — same resource has separate admin and user-facing controllers:

- `app/Http/Controllers/` — admin controllers (unrestricted CRUD)
- `app/Http/Controllers/User/` — user controllers (ownership-scoped: `Assembly::where('user_id', Auth::id())`, `Auth::user()->foundations()->findOrFail()`)

**Pengecualian — `PostController`**: `app/Http/Controllers/PostController.php` melayani dua rute sekaligus — `Route::resource('kelola-tulisan', ...)` (user) dan `Route::resource('/posts', ...)` (admin) — bukan dua controller terpisah. Otorisasi dilakukan di dalam controller dengan `hasAnyRole(['Super Admin', 'Penulis'])`. Jangan ikuti pola ini untuk resource baru; gunakan dual-controller yang benar.

### Content Moderation

Events (`ManageEventController`) and ScheduleNotes (`ScheduleNoteController`) follow a `Pending → Approved/Rejected` workflow. Admin approves via dedicated admin routes; public views only show `Approved` content.

### Key Models

| Model                        | Role                                                                                     |
| ---------------------------- | ---------------------------------------------------------------------------------------- |
| `Assembly`                   | Islamic study circle; owned by a User, led by a Teacher                                  |
| `Teacher`                    | Islamic scholar; slug-routed; has assemblies                                             |
| `Schedule`                   | Recurring class schedule within an Assembly                                              |
| `ScheduleNote`               | Community study notes per session; has visibility (Public/Private) and moderation status |
| `Event`                      | Assembly events with image, location, moderation status, and contributions               |
| `Library`                    | PDF library entries; may have a podcast audio and an AI `notebook_id` for RAG            |
| `ScientificArticle`          | Academic papers from Foundations; may have `notebook_id` for AI chat                     |
| `Foundation`                 | Partner institution; many-to-many with Users (foundation admins)                         |
| `Wirid`                      | Islamic recitation/prayer; users can like/save                                           |
| `Comment`                    | Polymorphic (commentable: Teacher, ScheduleNote)                                         |
| `Contribution`               | Polymorphic (contributable: Event)                                                       |
| `ChatSession`, `ChatMessage` | AI library chat history                                                                  |

### Image Upload Traits

Ada dua trait dengan perilaku berbeda — pilih sesuai kebutuhan:

- `app/Traits/HandlesImageUploads.php` — menghasilkan **dua file**: `{folder}/large/{uuid}.webp` dan `{folder}/thumb/{uuid}.webp`. Simpan path `large` ke database; gunakan accessor `str_replace('large', 'thumb', ...)` untuk thumb. Dipakai oleh: `ManagedMajelisController`. Gunakan trait ini untuk resource baru yang butuh tampilan list (thumbnail) dan detail (large).
- `app/Traits/ImageUploadTrait.php` — menghasilkan **satu file** tanpa variant thumb. Dipakai oleh: `LibraryController`.

`EventController` dan `ManageEventController` tidak menggunakan trait — image upload dilakukan inline dan menghasilkan satu file flat di `events/{uuid}.webp`.

### Services

- `HijriService` — Hijri date + Ramadan detection (24h cache)
- `OneSignalService` — push notifications to specific users or all subscribers
- `OpenNotebookService` — RAG AI chat (creates sessions, sends messages, retrieves history)
- `ImageService` — image processing utilities

### Livewire Components

Located in `app/Livewire/`. Namespaced components:

- `Biography\CommentSection`, `ScheduleNote\CommentSection` — polymorphic comment sections
- `Majelis\FollowButton` — follow/unfollow assemblies
- `Front\PostList`, `Front\ArticleList` — public content feeds
- `User\*` — authenticated user dashboard widgets
- `Forms\UserSelect`, `Partials\TeacherSelect` — reusable form selectors

Homepage widgets: `HomeEvent`, `HomeJadwalMajelis`, `HomeRamadhanToday`, `HomeUpcomingHaul`, `DailySurah`, `HijriCalendar`, `PrayerSchedule`.

### Layouts

Four layout types: `AppLayout` (untuk SuperAdmin), `DashboardLayout` (untuk pengguna yang sudah login), `UserLayout` (publik, tidak login), `AuthenticationLayout`.

## Environment Variables

Beyond standard Laravel, these are required:

```
# Google OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

# OneSignal push notifications
ONESIGNAL_APP_ID=
ONESIGNAL_REST_API_KEY=

# Open Notebook AI (external FastAPI)
OPEN_NOTEBOOK_BASE_URL=
OPEN_NOTEBOOK_API_KEY=
OPEN_NOTEBOOK_DEFAULT_ID=
```

Session driver defaults to `database` (`SESSION_DRIVER=database`).

## Security Notes

- Always use `clean()` (mews/purifier) before persisting any user-supplied HTML — this project has had XSS issues in post content, guru descriptions, and biographies (see recent commits).
- The `IsAdmin` middleware (`app/Http/Middleware/IsAdmin.php`) checks `hasRole('Super Admin')` — do not bypass this for admin routes.
- User controllers must scope all queries to the authenticated user's owned resources to prevent IDOR.

## Template Debris — Jangan Disentuh atau Dijadikan Referensi

Sebagian besar isi admin area berasal dari **template UI Flowbite/Mosaic** dan **tidak ada hubungannya dengan logika bisnis Syaikhuna**. Jangan jadikan ini referensi, jangan perluas, dan jangan hapus tanpa diskusi.

**Routes** (semua di bawah `/admin/`): `/ecommerce/*`, `/community/*`, `/finance/*`, `/job/*`, `/messages`, `/tasks/*`, `/inbox`, `/calendar`, `/settings/account`, `/campaigns`, `/onboarding-0*`, `/component/*`, `/utility/*`

**Controllers**: `CampaignController`, `CustomerController`, `InvoiceController`, `JobController`, `MemberController`, `OrderController`, `TransactionController`

**Models**: `Campaign`, `Customer`, `DataFeed`, `Invoice`, `Marketer`, `Member`, `Order`, `Transaction`

**Seeders**: Semua seeder kecuali `PostRoleSeeder`, `SpesialisasiSeeder`, `DailySurahReadingSeeder`

## IMPORTANT

- Gunakan package manager yang sudah dipakai proyek.
- Jangan mengganti framework atau dependency utama tanpa persetujuan.
- Jangan melakukan upgrade dependency massal sebagai bagian dari tugas lain.

### Architecture Rules

Sebelum menambahkan implementasi baru:

1. Cari implementasi serupa dalam codebase.
2. Jelaskan pola yang ditemukan.
3. Gunakan kembali abstraction yang ada jika sesuai.
4. Jangan membuat duplicate utility atau duplicate service.

### Coding Conventions

- Ikuti style kode yang sudah digunakan pada file di area yang sedang dikerjakan
- Pertahankan public API yang sudah ada kecuali perubahan breaking memang diminta
- Hindari any, suppress error, empty catch, dan hardcoded configuration.
- Jangan menambahkan komentar yang hanya mengulang isi kode.
- Beri komentar hanya untuk keputusan atau perilaku yang tidak mudah dipahami.
- Jangan melakukan refactor di luar ruang lingkup tugas.

### Database Rules

- Jangan menghapus tabel, kolom, atau data.
- Jangan menjalankan destructive migration tanpa persetujuan.
- Migration harus backward-compatible jika memungkinkan.
- Jangan mengubah migration lama yang sudah pernah digunakan di environment lain.
- Tambahkan migration baru untuk perubahan schema.

### Workflow for Non-Trivial Tasks

Untuk tugas yang memengaruhi beberapa file atau mengubah perilaku aplikasi:

1. Pelajari implementasi yang relevan.
2. Jelaskan current flow.
3. Identifikasi file yang kemungkinan berubah.
4. Tulis implementation plan.
5. Tunggu sampai plan disetujui sebelum mengubah kode.
6. Implementasikan perubahan secara bertahap.
7. Jalankan test dan pemeriksaan terkait.
8. Tinjau git diff.
9. Laporkan: file yang berubah, keputusan teknis, test yang dijalankan, risiko atau pekerjaan lanjutan.

### Definition of Done

Sebuah tugas belum selesai sampai:

- kebutuhan dan acceptance criteria terpenuhi;
- implementasi mengikuti arsitektur yang ada;
- tidak ada perubahan di luar scope;
- test terkait lulus;
- lint dan type check lulus;
- build lulus jika relevan;
- tidak ada credential atau debug code tertinggal;
- git diff sudah diperiksa;
- dokumentasi diperbarui jika perilaku publik berubah.
