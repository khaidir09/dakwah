# API Publik Syaikhuna — Panduan Partner

API baca-saja untuk menampilkan **jadwal majelis**, **wirid & doa**, dan **haul guru** di aplikasi pihak ketiga.

- **Base URL:** `https://<domain>/api/v1`
- **Format:** JSON, UTF-8
- **Metode:** `GET` saja

---

## Autentikasi

Setiap permintaan wajib menyertakan API key pada header:

```
X-API-Key: <api-key-anda>
```

Key diterbitkan oleh admin Syaikhuna dan hanya ditampilkan satu kali saat dibuat. Key yang salah, tidak dikenal, atau sudah dicabut sama-sama menghasilkan `401`.

## Batas Permintaan

Batas standar **60 permintaan per menit per key**. Bila terlampaui, API mengembalikan `429` beserta header `Retry-After` (detik).

## Caching

Response di-cache selama **15 menit**. Konsekuensinya, perubahan data di Syaikhuna dapat terlihat paling lambat 15 menit kemudian.

Setiap response membawa header `ETag`. Kirim kembali nilainya pada `If-None-Match` untuk mendapatkan `304 Not Modified` tanpa body — sangat dianjurkan untuk perangkat yang melakukan polling berkala.

```bash
curl -H "X-API-Key: $KEY" -H 'If-None-Match: "16b7345a..."' \
  "https://<domain>/api/v1/haul"
```

## Versi

Versi berada di URL. Versi aktif saat ini: **v1**, ditegaskan lewat header `X-Api-Version: 1` pada setiap response.

- Selama v1 hidup, hanya perubahan **additive** yang dilakukan: field baru, parameter opsional baru, endpoint baru.
- **Klien wajib mengabaikan field yang tidak dikenal.** Tanpa ini, penambahan field pun dapat merusak aplikasi Anda.
- Perubahan yang merusak kontrak akan terbit sebagai `/api/v2` yang berjalan berdampingan; v1 tidak akan berubah diam-diam.
- Memanggil tanpa versi (`/api/jadwal-majelis`) menghasilkan `404`. Selalu sertakan `/v1`.

---

## `GET /api/v1/jadwal-majelis`

Mengembalikan **kejadian jadwal** (occurrence) dengan tanggal konkret, bukan aturan pengulangan. Aturan mingguan, bulanan, dua-mingguan, hingga pekan pertama Hijriah sudah dihitung di server.

| Parameter | Wajib | Keterangan |
|---|---|---|
| `city_code` | **ya** | Kode kota/kabupaten (BPS), mis. `6371` |
| `district_code` | tidak | Kode kecamatan untuk mempersempit |
| `days` | tidak | Panjang jendela dalam hari, `1`–`30`, default `7` |
| `tipe` | tidak | `Majelis`, `Mesjid`, `Langgar`, atau `Musholla` |

```bash
curl -H "X-API-Key: $KEY" \
  "https://<domain>/api/v1/jadwal-majelis?city_code=6371&days=7"
```

```json
{
  "data": [
    {
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
        "gambar_url": "https://<domain>/storage/majelis/thumb/....webp"
      },
      "guru": { "id": 7, "nama": "KH. ...", "slug": "kh-...", "foto_url": "https://..." },
      "url": "https://<domain>/jadwal-majelis/145"
    }
  ],
  "meta": {
    "city_code": "6371",
    "district_code": null,
    "days": 7,
    "from": "2026-07-21",
    "to": "2026-07-27",
    "is_ramadhan": false
  }
}
```

Catatan:

- Satu jadwal dapat muncul beberapa kali (satu entri per tanggal), atau tidak sama sekali bila tidak jatuh dalam jendela.
- Identitas sebuah kejadian adalah kombinasi **`schedule_id` + `tanggal`**.
- `waktu` berformat 24 jam `HH:mm`, zona waktu WITA (`Asia/Makassar`). Dapat bernilai `null`.
- `majelis` dan `guru` dapat bernilai `null`.
- Hasil terurut menaik berdasarkan tanggal lalu jam. Tidak ada pagination.
- `meta.is_ramadhan` menandai bahwa saat ini bulan Ramadan; jadwal yang berstatus libur Ramadan tidak dikembalikan.

---

## `GET /api/v1/wirid`

| Parameter | Wajib | Keterangan |
|---|---|---|
| `kategori` | tidak | `wirid` atau `doa`; tanpa parameter mengembalikan keduanya |
| `waktu` | tidak | Label waktu, mis. `Pagi` |
| `per_page` | tidak | `1`–`50`, default `20` |
| `page` | tidak | Nomor halaman, default `1` |

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
      "deskripsi_html": null,
      "deskripsi_text": null,
      "url": "https://<domain>/wirid"
    }
  ],
  "meta": { "current_page": 1, "per_page": 20, "total": 143, "last_page": 8 }
}
```

Setiap field teks tersedia dalam dua bentuk: `*_html` (sudah dibersihkan di server, aman dirender) dan `*_text` (tanpa tag, untuk klien yang tidak merender HTML). Keduanya bernilai `null` bila kosong.

---

## `GET /api/v1/haul`

Daftar haul guru yang paling dekat, diurutkan menaik berdasarkan `hari_menuju_haul`.

| Parameter | Wajib | Keterangan |
|---|---|---|
| `limit` | tidak | `1`–`50`, default `10` |

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
      "foto_url": "https://<domain>/storage/guru/....webp",
      "url": "https://<domain>/guru/kh-muhammad-zaini-bin-abdul-ghani"
    }
  ],
  "meta": { "hijri_today": { "day": 5, "month": 2, "year": 1448 } }
}
```

Tanggal Hijriah memakai kalender **islamic-civil** (aritmatis), sama dengan yang ditampilkan situs Syaikhuna. Karena tidak mengikuti rukyat setempat, tanggalnya dapat berbeda satu hari dari penetapan di daerah Anda.

---

## Format Error

Semua error memakai bentuk yang sama:

```json
{
  "error": {
    "code": "validation_failed",
    "message": "The kode kota/kabupaten field is required.",
    "details": { "city_code": ["The kode kota/kabupaten field is required."] }
  }
}
```

| HTTP | `code` | Penyebab |
|---|---|---|
| 401 | `unauthenticated` | Key tidak ada, salah, atau sudah dicabut |
| 404 | `not_found` | Endpoint/versi tidak dikenal |
| 422 | `validation_failed` | Parameter tidak valid; rincian ada di `details` |
| 429 | `rate_limited` | Melebihi batas per menit; lihat `Retry-After` |
| 500 | `server_error` | Kesalahan pada server |

Field `details` hanya muncul pada `validation_failed`.

---

## Cakupan Data

API hanya mengembalikan konten publik yang sudah dimoderasi:

- Jadwal berstatus **Aktif**, akses **Umum/Ikhwan/Akhwat**, milik majelis yang sudah disetujui.
- Wirid dan guru yang kontribusinya sudah disetujui.
- Endpoint haul hanya memuat guru yang sudah wafat dan tanggal wafat Hijriahnya lengkap.

Karena penyaringan ini, jumlah data yang dikembalikan API dapat lebih sedikit daripada yang tampak di situs Syaikhuna. Itu perilaku yang disengaja.
