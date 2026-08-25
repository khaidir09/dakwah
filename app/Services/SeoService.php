<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Penurunan metadata halaman publik (title, description, canonical, og:image).
 *
 * Dibagikan ke seluruh view sebagai `$seo` lewat AppServiceProvider, sehingga
 * halaman cukup memanggilnya di dalam @section(...) tanpa import apa pun.
 */
class SeoService
{
    /** Batas aman agar judul tidak terpotong di hasil pencarian. */
    public const TITLE_LIMIT = 60;

    /** Batas aman agar deskripsi tidak terpotong di hasil pencarian. */
    public const DESCRIPTION_LIMIT = 155;

    public const DEFAULT_DESCRIPTION = 'Platform informasi jadwal majelis, profil ulama, acara, dan konten keislaman di Kalimantan.';

    /**
     * Judul lengkap dengan nama situs, mis. "Guru Sekumpul — Syaikhuna".
     */
    public function title(?string $page = null): string
    {
        $site = config('app.name', 'Syaikhuna');
        $page = $this->normalize($page);

        if ($page === '') {
            return $site;
        }

        $suffix = ' — '.$site;
        $room = self::TITLE_LIMIT - mb_strlen($suffix);

        if ($room < 1) {
            return $site;
        }

        return Str::limit($page, $room, '…').$suffix;
    }

    /**
     * Ringkasan bebas HTML untuk <meta name="description">. Nilai balik tidak
     * pernah kosong selama $fallback diisi.
     */
    public function description(?string $raw, string $fallback = self::DEFAULT_DESCRIPTION): string
    {
        $text = $this->normalize($raw);

        if ($text === '') {
            $text = $this->normalize($fallback);
        }

        return Str::limit($text, self::DESCRIPTION_LIMIT, '…');
    }

    /**
     * URL kanonik: absolut, tanpa query string, tanpa trailing slash.
     *
     * Selalu dibangun dari config('app.url'), bukan host request, agar canonical
     * tidak ikut berubah saat situs diakses lewat host lain (mis. IP server).
     */
    public function canonical(?string $path = null): string
    {
        $path = $path ?? request()->path();

        // request()->path() mengembalikan '/' untuk root.
        $path = trim($path, '/');

        return $path === ''
            ? $this->baseUrl().'/'
            : $this->baseUrl().'/'.$path;
    }

    /**
     * og:image absolut. Urutan: gambar entitas → banner kategori → logo situs.
     *
     * Banner kategori (public/images/og/{kategori}.png) bersifat opsional; selama
     * berkasnya belum ada, otomatis jatuh ke logo.
     */
    public function image(?string $storagePath = null, string $category = 'default'): string
    {
        if (filled($storagePath)) {
            return $this->absolute(Storage::url($storagePath));
        }

        $banner = 'images/og/'.$category.'.png';

        if (is_file(public_path($banner))) {
            return $this->absolute($banner);
        }

        return $this->absolute('images/android-chrome-512x512.png');
    }

    /**
     * Buang tag HTML, decode entitas, dan rapatkan whitespace jadi satu spasi.
     */
    private function normalize(?string $raw): string
    {
        if ($raw === null) {
            return '';
        }

        // Decode lebih dulu agar tag yang ter-encode ikut terbuang strip_tags.
        $text = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }

    private function absolute(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        return $this->baseUrl().'/'.ltrim($path, '/');
    }

    private function baseUrl(): string
    {
        return rtrim(config('app.url'), '/');
    }
}
