<?php

namespace App\Services;

use App\Models\Assembly;
use App\Models\EventPosterGeneration;
use App\Models\PosterSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Pembuatan poster acara lewat model gambar Gemini ("Nano Banana").
 *
 * Prompt selalu disusun di sini dari data acara + gaya pilihan; pengguna tidak
 * pernah mengirim teks prompt, sehingga tidak ada jalur untuk menyelipkan
 * instruksi ke model selain lewat nilai form yang sudah dibatasi panjangnya.
 */
class GeminiPosterService
{
    /** Dimensi akhir poster — potret 9:16, siap dipakai sebagai status WhatsApp. */
    public const WIDTH = 1080;

    public const HEIGHT = 1920;

    public const THUMB_WIDTH = 360;

    public const THUMB_HEIGHT = 640;

    /** Panjang maksimal tiap nilai dari pengguna yang ikut masuk prompt. */
    private const MAX_FIELD_LENGTH = 120;

    public const STYLES = [
        'kaligrafi_emas' => 'Kaligrafi Emas',
        'klasik_banjar' => 'Klasik Banjar',
        'minimalis_hijau' => 'Minimalis Hijau',
        'hitam_elegan' => 'Hitam Elegan',
    ];

    private const STYLE_PROMPTS = [
        'kaligrafi_emas' => 'ornamen kaligrafi Arab keemasan di atas latar hijau tua pekat, bingkai arabesque emas, kesan mewah dan khidmat',
        'klasik_banjar' => 'ornamen ukiran khas Banjar Kalimantan Selatan, motif kayu ulin dan sulur tradisional, warna cokelat keemasan hangat',
        'minimalis_hijau' => 'desain minimalis modern, latar hijau lembut bergradasi, banyak ruang kosong, garis geometris tipis, bersih dan mudah dibaca',
        'hitam_elegan' => 'latar hitam pekat dengan aksen emas tipis, ornamen geometris islami samar, kesan formal dan elegan',
    ];

    private const MONTHS = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    private const DAYS = [
        'Sunday' => 'Ahad',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    private ?string $apiKey;

    private string $model;

    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.image_model');
        $this->timeout = config('services.gemini.timeout');
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey) && filled($this->model);
    }

    public function model(): string
    {
        return $this->model;
    }

    /**
     * Fitur ini untuk pengurus majelis dan kontributor. User terverifikasi lain
     * tidak mendapatkannya agar kuota API tidak terbuka untuk semua pendaftar.
     */
    public function isEligible(User $user): bool
    {
        return Assembly::where('user_id', $user->id)->exists()
            || $user->hasRole('Kontributor');
    }

    /**
     * Ringkasan kuota untuk ditampilkan di form sekaligus dipakai sebagai gerbang
     * di endpoint generate, supaya UI dan server memakai angka yang sama persis.
     *
     * @return array{available: bool, eligible: bool, used: int, total: int, remaining: int}
     */
    public function quotaFor(User $user): array
    {
        $setting = PosterSetting::current();
        $used = EventPosterGeneration::quotaUsedThisMonth($user->id);
        $total = $setting->monthly_quota;

        return [
            'available' => $setting->is_active && $this->isConfigured(),
            'eligible' => $this->isEligible($user),
            'used' => $used,
            'total' => $total,
            'remaining' => max(0, $total - $used),
        ];
    }

    /**
     * Susun prompt final dari data acara.
     *
     * @param  array{name: string, category: string, date: string, style: string, assembly?: ?string, location?: ?string}  $data
     */
    public function buildPrompt(array $data): string
    {
        $nama = $this->sanitize($data['name']);
        $tanggal = $this->formatDate($data['date']);
        $gaya = self::STYLE_PROMPTS[$data['style']] ?? reset(self::STYLE_PROMPTS);

        $baris = [
            'Judul acara: "' . $nama . '"',
            'Waktu: ' . $tanggal,
        ];

        if (filled($data['assembly'] ?? null)) {
            $baris[] = 'Penyelenggara: "' . $this->sanitize($data['assembly']) . '"';
        }

        if (filled($data['location'] ?? null)) {
            $baris[] = 'Tempat: "' . $this->sanitize($data['location']) . '"';
        }

        $informasi = implode("\n", array_map(fn($b) => '- ' . $b, $baris));

        return <<<PROMPT
        Buat sebuah poster undangan acara keagamaan Islam berorientasi potret (vertikal, rasio 9:16).

        Informasi yang harus tercetak jelas dan terbaca pada poster:
        {$informasi}

        Gaya visual: {$gaya}.

        Ketentuan wajib:
        - Seluruh teks ditulis dalam bahasa Indonesia dengan ejaan persis seperti tertulis di atas, tanpa mengubah, menyingkat, atau menambah kata.
        - Judul acara menjadi elemen paling menonjol; tanggal dan waktu terbaca jelas.
        - Nuansa sopan, teduh, dan penuh hormat sesuai adab majelis ilmu.
        - Tidak boleh ada gambar makhluk bernyawa, wajah manusia, hewan, atau patung.
        - Tidak boleh ada simbol agama selain Islam.
        - Tidak boleh menambahkan teks, nama, logo, atau keterangan apa pun yang tidak disebutkan di atas.
        PROMPT;
    }

    /**
     * Kirim prompt ke Gemini dan simpan hasilnya sebagai dua varian webp.
     *
     * Tidak pernah melempar exception ke pemanggil: kegagalan apa pun dikembalikan
     * sebagai ['ok' => false, 'error' => ...] agar controller dapat mencatat baris
     * gagal dan menampilkan pesan yang bisa dibaca pengguna.
     *
     * @return array{ok: bool, path: ?string, error: ?string}
     */
    public function generate(string $prompt): array
    {
        if (! $this->isConfigured()) {
            return $this->failure('Konfigurasi Gemini belum lengkap.');
        }

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
                ->timeout($this->timeout)
                ->connectTimeout(10)
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent',
                    [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]],
                        ],
                        'generationConfig' => [
                            'responseModalities' => ['IMAGE'],
                            'imageConfig' => ['aspectRatio' => '9:16'],
                        ],
                    ]
                );
        } catch (\Throwable $e) {
            return $this->failure('Gagal menghubungi layanan Gemini: ' . $e->getMessage());
        }

        if (! $response->successful()) {
            return $this->failure('Gemini menolak permintaan (HTTP ' . $response->status() . '): ' . Str::limit($response->body(), 500));
        }

        $base64 = $this->extractImage($response->json());

        if ($base64 === null) {
            return $this->failure('Respons Gemini tidak memuat data gambar.');
        }

        $binary = base64_decode($base64, true);

        if ($binary === false || $binary === '') {
            return $this->failure('Data gambar dari Gemini tidak dapat dibaca.');
        }

        try {
            $path = $this->store($binary);
        } catch (\Throwable $e) {
            return $this->failure('Gagal memproses gambar poster: ' . $e->getMessage());
        }

        return ['ok' => true, 'path' => $path, 'error' => null];
    }

    /**
     * Ambil poster hasil generate yang boleh dilampirkan ke sebuah acara.
     *
     * Mengembalikan null — bukan error — bila id tidak dikenali, bukan milik
     * pengguna, gagal dibuat, atau sudah terpakai oleh acara lain. Tanpa
     * pemeriksaan ini, pengguna dapat mencantumkan id milik orang lain pada form
     * dan mencuri posternya.
     */
    public function claimGeneration(mixed $generationId, int $userId, ?int $eventId = null): ?EventPosterGeneration
    {
        if (blank($generationId) || ! is_numeric($generationId)) {
            return null;
        }

        $generation = EventPosterGeneration::where('id', (int) $generationId)
            ->where('user_id', $userId)
            ->where('status', EventPosterGeneration::STATUS_SUCCESS)
            ->whereNotNull('image_path')
            ->first();

        if (! $generation) {
            return null;
        }

        if ($generation->event_id !== null && $generation->event_id !== $eventId) {
            return null;
        }

        return $generation;
    }

    /**
     * Hapus poster beserta varian thumb-nya. Aman dipanggil untuk path flat milik
     * poster unggahan lama — hanya berkas itu yang dihapus.
     */
    public function deletePoster(?string $largePath): void
    {
        if (! $largePath) {
            return;
        }

        Storage::disk('public')->delete($largePath);

        if (str_contains($largePath, '/large/')) {
            Storage::disk('public')->delete(str_replace('/large/', '/thumb/', $largePath));
        }
    }

    /**
     * Simpan dua varian webp. Rasio akhir dijamin di sini lewat cover(), bukan oleh
     * API — apa pun dimensi yang dikembalikan Gemini, hasilnya tetap 1080x1920.
     */
    private function store(string $binary): string
    {
        $filename = Str::uuid() . '.webp';

        $large = Image::read($binary)->cover(self::WIDTH, self::HEIGHT)->toWebp(80);
        $thumb = Image::read($binary)->cover(self::THUMB_WIDTH, self::THUMB_HEIGHT)->toWebp(80);

        Storage::disk('public')->put('events/large/' . $filename, (string) $large);
        Storage::disk('public')->put('events/thumb/' . $filename, (string) $thumb);

        return 'events/large/' . $filename;
    }

    private function extractImage(?array $payload): ?string
    {
        foreach ($payload['candidates'][0]['content']['parts'] ?? [] as $part) {
            if (filled($part['inlineData']['data'] ?? null)) {
                return $part['inlineData']['data'];
            }
        }

        return null;
    }

    /**
     * Catat kegagalan tanpa pernah menyertakan API key.
     *
     * @return array{ok: bool, path: ?string, error: string}
     */
    private function failure(string $message): array
    {
        Log::warning('Pembuatan poster Gemini gagal', [
            'model' => $this->model,
            'message' => $message,
        ]);

        return ['ok' => false, 'path' => null, 'error' => $message];
    }

    /**
     * Buang HTML dan baris baru dari nilai yang diisi pengguna sebelum masuk prompt,
     * lalu potong panjangnya. Baris baru dibuang karena itulah cara termudah
     * menyisipkan instruksi tandingan ke dalam prompt.
     */
    private function sanitize(?string $value): string
    {
        $clean = preg_replace('/\s+/', ' ', strip_tags((string) $value));

        return Str::limit(trim($clean), self::MAX_FIELD_LENGTH, '');
    }

    private function formatDate(string $raw): string
    {
        $date = Carbon::parse($raw);

        return sprintf(
            '%s, %d %s %d pukul %s WITA',
            self::DAYS[$date->format('l')] ?? $date->format('l'),
            $date->day,
            self::MONTHS[$date->month],
            $date->year,
            $date->format('H:i')
        );
    }
}
