# Syaikhuna
## Sistem Operasi Digital Keilmuan & Syiar Banua

**Syaikhuna** adalah platform digital (berbasis web dan mobile) yang didedikasikan untuk melestarikan, mengelola, dan mendistribusikan khazanah keilmuan Islam Ahlussunnah wal Jamaah di Kalimantan Selatan, Kalimantan Tengah, dan Kalimantan Timur.

Platform ini dirancang khusus untuk menghubungkan jamaah, santri, mahasiswa, dan profesional dengan jaringan majelis ilmu yang diasuh oleh zuriyat Syekh Muhammad Arsyad Al-Banjari (Datu Kalampayan) serta murid-murid KH. Muhammad Zaini bin Abdul Ghani (Abah Guru Sekumpul).

Syaikhuna bukan sekadar portal jadwal pengajian statis, melainkan sebuah ekosistem pintar yang mengintegrasikan pencatatan kolaboratif, pustaka digital cerdas berbasis kecerdasan buatan (RAG), serta logistik acara keagamaan berskala masif.

---

# 🚀 Fitur Unggulan

## 1. Peta Majelis & Jadwal Dinamis (Live Religious Map)

- Sistem pemantauan status jadwal majelis secara real-time:
  - 🟢 Buka
  - 🔴 Libur
  - 🟡 Tentatif
- Otorisasi perubahan status hanya dapat dilakukan oleh perwakilan resmi atau admin masing-masing majelis.
- Integrasi peta interaktif untuk membantu jamaah menuju lokasi majelis.

---

## 2. Pustaka Cerdas & Chatbot AI (Open Notebook Integration)

### Kemampuan Utama

- **Chat with Kitab**
  - Tanya jawab interaktif langsung terhadap kitab yang telah tervalidasi.
  - Contoh: *Terjemah Kitab Sabilal Muhtadin*.

- **Semantic Search**
  - Mencari esensi makna dari ribuan halaman kitab, risalah, dan artikel ilmiah Islam.
  - Tidak terbatas pada pencarian kata kunci literal.

### Teknologi

- Retrieval-Augmented Generation (RAG)
- Embedding Vector Search
- PostgreSQL + pgvector

---

## 3. Podcast Generator (Audio-Based Talaqqi)

Mengubah naskah keagamaan menjadi dialog audio dinamis berformat **Deep Dive Discussion**.

### Sumber Konten

- Kitab dan terjemahan
- Manaqib ulama
- Artikel ilmiah populer
- Risalah pengajian

### Manfaat

- Mendukung pembelajaran sambil berkendara.
- Cocok bagi mahasiswa dan profesional yang memiliki mobilitas tinggi.

---

## 4. Catatan Pengajian Kolaboratif (Risalah Jamaah)

Fitur pencatatan faedah pengajian secara kolaboratif oleh jamaah.

### Alur Kerja

1. Jamaah membuat catatan pengajian.
2. Catatan terhubung dengan jadwal majelis terkait.
3. Admin resmi melakukan proses **Tashih**.
4. Catatan yang lolos verifikasi dapat dipublikasikan.

### Tujuan

- Mengurangi kesalahan kutipan dalil.
- Menjaga akurasi penyampaian fatwa ulama.
- Membangun arsip ilmu yang terdokumentasi dengan baik.

---

# 🛠️ Stack Teknologi

Syaikhuna menggunakan arsitektur hibrida yang dirancang agar tetap optimal pada VPS dengan sumber daya terbatas (< 4 GB RAM).

| Komponen | Teknologi |
|-----------|------------|
| Frontend | Tailwind CSS, Alpine.js |
| Backend | Laravel, Livewire |
| Framework | Laravel TALL Stack |
| Database Relasional | PostgreSQL |
| Vector Database | pgvector |
| AI Service | Open Notebook (FastAPI + Python + SurrealDB) |
| Containerization | Docker |
| Push Notification | OneSignal |
| Messaging | WhatsApp Business API |

### Keunggulan Arsitektur

- SEO Friendly
- Reaktif tanpa SPA kompleks
- Hemat RAM VPS
- Mudah dipelihara
- Skalabel untuk fitur AI

---

# 📦 Panduan Instalasi Server (VPS)

## 1. Prasyarat Sistem

### Sistem Operasi

- Ubuntu 22.04 LTS
- Debian 11+

### Minimum Resource

- RAM: 2 GB
- Swap File: 4 GB
- CPU: 2 Core

### Software

- Docker
- Docker Compose
- PHP 8.2+
- Composer
- PostgreSQL
- Ekstensi `pgvector`

---

## 2. Setup AI Service (Open Notebook - Single Container Lite)

### Membuat Direktori Kerja

```bash
mkdir -p ~/open-notebook
cd ~/open-notebook
```

### Membuat Docker Compose

```bash
nano docker-compose.yml
```

Tambahkan konfigurasi sesuai kebutuhan deployment.

### Menjalankan Container

```bash
docker compose up -d
```

---

# 🗄️ Struktur Database Utama

## Tabel `seasonal_schedules`

Digunakan untuk jadwal yang memiliki pola pengulangan (recurrence).

### Contoh

- Kuliah Shubuh
- Ramadhan
- Maulid rutin
- Haul tahunan

### Manfaat

- Mengurangi redundansi data.
- Menghemat kapasitas database.
- Mempercepat query jadwal.

---

## Tabel `scientific_articles`

Menyimpan artikel ilmiah populer dari mitra lembaga dan yayasan.

| Kolom | Tipe Data | Deskripsi |
|---------|-----------|------------|
| id | BigInt (PK) | Identifier unik artikel |
| foundation_id | Int (FK) | Relasi ke yayasan/lembaga |
| title | Varchar | Judul artikel |
| slug | Varchar | URL SEO-friendly |
| notebook_id | Varchar | Mapping ke Open Notebook |
| views_count | BigInt | Total kunjungan |
| likes_count | BigInt | Total apresiasi jamaah |

---

# 🧠 Mekanisme Pengindeksan Catatan (One-Time Indexing)

Untuk mendukung pencarian semantik berskala besar tanpa membebani biaya API, Syaikhuna menerapkan skema **One-Time Indexing berbasis RAG**.

## 1. Sistem Antrean (Queue)

Setelah catatan pengajian lolos proses tashih:

```text
Catatan ➜ Queue Laravel ➜ Proses Embedding
```

---

## 2. Chunking

Teks dipotong menjadi:

- Maksimal 500 token per chunk
- Overlap 10%

Tujuannya menjaga kesinambungan konteks antar potongan teks.

---

## 3. Batch Embedding

Setiap tengah malam:

1. Laravel mengumpulkan seluruh chunk baru.
2. Data dikirim secara kolektif ke OpenAI Batch API.
3. Menggunakan model:

```text
text-embedding-3-small
```

### Efisiensi Biaya

- Standard API: ± $0.02 / 1 juta token
- Batch API: ± $0.01 / 1 juta token

Penghematan biaya hingga **50%**.

---

## 4. Penyimpanan Vektor

Hasil embedding:

- 1.536 dimensi
- Disimpan pada kolom bertipe `vector`
- Menggunakan PostgreSQL + pgvector

---

## 5. Semantic Search Lokal

Saat pengguna melakukan pencarian:

```sql
embedding <=> query_embedding
```

### Keuntungan

- Tidak perlu memanggil API eksternal.
- Respon sangat cepat.
- Biaya operasional rendah.
- Data tetap berada di server sendiri.

---

# 📜 Lisensi & Adab Penggunaan

Syaikhuna dibangun dengan mengedepankan keberkahan ilmu dan penghormatan terhadap sanad keilmuan.

## Ketentuan

### 1. Hak Cipta Naskah

Seluruh naskah digital yang dipublikasikan wajib memiliki:

- Izin tertulis dari ahli waris pengarang; atau
- Kontrak lisensi digital yang sah dari penerbit terkait.

### 2. Penggunaan AI

AI hanya digunakan sebagai:

- Pencari referensi sumber
- Asisten penelusuran teks
- Mesin pencarian semantik

AI **bukan** alat pembuat fatwa mandiri.

### 3. Larangan Penyalahgunaan

Aplikasi tidak boleh digunakan untuk:

- Kepentingan politik praktis
- Propaganda kelompok tertentu
- Penyebaran paham di luar koridor Ahlussunnah wal Jamaah An-Nahdliyah di Kalimantan

---

# 🤝 Visi

> Merawat sanad keilmuan para guru, mendampingi langkah kaki umat menuju majelis, serta menghadirkan teknologi yang bermanfaat, tenang, dan penuh keberkahan bagi Banua.

---

## Persembahan

**Syaikhuna** merupakan persembahan bakti pemuda Banua untuk menjaga, mendokumentasikan, dan menyebarluaskan khazanah keilmuan Islam kepada generasi mendatang.
