# Otomatisasi n8n — Distribusi Konten Syaikhuna

Workflow n8n yang menyebarkan konten Syaikhuna ke Telegram Channel. Semuanya hanya **membaca** API publik v1 (`docs/api-partner.md`), jadi tidak ada data di Syaikhuna yang diubah.

| File | Jadwal (WITA) | Isi |
|---|---|---|
| `workflows/a1-jadwal-majelis-harian.json` | 04:30 setiap hari | Jadwal majelis hari ini, satu pesan per kab/kota Kalsel. Kota tanpa jadwal dilewati. |
| `workflows/a2-pengingat-haul.json` | 06:00 setiap hari | Pengingat haul guru pada H-7, H-1, dan hari H. |
| `workflows/a3-amalan-harian.json` | 05:15 setiap hari | Satu wirid/doa per hari, bergiliran berurutan sampai seluruh daftar habis. |

## Pemasangan

### 1. API key Syaikhuna

Di server Syaikhuna:

```bash
php artisan api:client-create "n8n Telegram"
```

Key hanya tampil sekali, jadi simpan baik-baik. Ketiga workflow cukup memakai satu key bersama. Pemakaiannya jauh di bawah batas 60 request/menit: A1 mengirim 13 request dengan jeda 1,5 detik.

### 2. Bot Telegram

1. Buat bot lewat [@BotFather](https://t.me/BotFather) (`/newbot`), lalu simpan token-nya.
2. Tambahkan bot sebagai **admin** channel dengan izin *Post Messages*.

### 3. Credential di n8n

Buat dua credential di **Credentials → Add credential**:

- **Header Auth**, nama `Syaikhuna API`: *Name* `X-API-Key`, *Value* diisi API key dari langkah 1.
- **Telegram API**, nama `Syaikhuna Bot`: *Access Token* diisi token dari BotFather.

### 4. Impor workflow

Untuk setiap file di `workflows/`:

1. **Workflows → Import from File**.
2. Pilih credential `Syaikhuna API` di setiap node HTTP Request dan `Syaikhuna Bot` di node *Kirim ke Telegram*.
3. Buka node **Config**, lalu isi:
   - `base_url`: domain Syaikhuna tanpa garis miring di akhir (default `https://syaikhuna.id`)
   - `telegram_chat_id`: `@username_channel`, atau ID numerik `-100…` untuk channel privat
4. Klik **Test workflow** untuk mencoba, lalu aktifkan dengan toggle **Active**.

Timezone tiap workflow sudah diset `Asia/Makassar` di *Workflow Settings*, jadi jadwal tetap WITA walaupun server n8n memakai UTC.

## Perilaku yang perlu diketahui

- **A1, kegagalan sebagian.** Bila request untuk satu kota gagal setelah 3 kali percobaan, kota itu dilewati dan kota lain tetap terkirim. Bila semua kota gagal, workflow error dan tercatat di *Executions*.
- **A1, pesan panjang.** Kota dengan banyak jadwal dipecah menjadi beberapa pesan bertanda "(lanjutan N)" karena batas Telegram 4096 karakter.
- **A1, daftar kota.** Daftar kab/kota ada di node *Daftar Kota*. Untuk menambah Kaltim atau Kalteng, cukup tambah baris `{ city_code, nama }` (kode BPS).
- **A2, tanggal haul.** Tanggal dihitung dengan kalender Hijriah aritmatis, sama dengan situs, sehingga bisa selisih satu hari dari penetapan setempat. Pesan haul menyertakan catatan ini.
- **A3, urutan amalan.** Bila ada wirid/doa baru, urutan rotasi ikut bergeser karena API mengurutkan dari yang terbaru. Ini tidak berbahaya, hanya giliran amalan yang berubah.
- **Cache API.** Respons API di-cache 15 menit, jadi data yang baru diubah admin bisa belum ikut terkirim pada jadwal berikutnya.

Disarankan memasang **Error Workflow** di *Workflow Settings* yang mengirim notifikasi ke admin bila salah satu workflow gagal.
