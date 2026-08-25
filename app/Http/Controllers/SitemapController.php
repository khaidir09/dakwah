<?php

namespace App\Http\Controllers;

use App\Models\Assembly;
use App\Models\Library;
use App\Models\Post;
use App\Models\Schedule;
use App\Models\ScheduleNote;
use App\Models\ScientificArticle;
use App\Models\Teacher;
use App\Services\SeoService;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Peta situs dinamis untuk mesin telusur.
 *
 * Hanya memuat konten yang sah tayang untuk anonim: setiap URL di sini wajib
 * mengembalikan 200 (bukan 301 maupun 404) dan wajib identik dengan
 * <link rel="canonical"> halamannya. Karena itu seluruh URL dibangun lewat
 * SeoService::canonical() atas path dari route(), bukan dirakit manual.
 *
 * Acara dan jadwal Ramadhan sengaja tidak masuk (konten musiman) — keduanya
 * tetap dapat ditemukan lewat tautan internal.
 */
class SitemapController extends Controller
{
    public const CACHE_KEY = 'seo:sitemap';

    /** Konten Syaikhuna berubah harian, bukan per menit. */
    public const CACHE_TTL = 60 * 60 * 6;

    /**
     * Peringatan dini, bukan batas protokol (protokol sitemap: 50.000 URL atau
     * 50 MB per berkas). Saat ambang ini terlewati sitemap masih valid, tetapi
     * sudah waktunya dipecah menjadi sitemap index.
     */
    public const URL_WARNING_THRESHOLD = 10000;

    /**
     * Halaman daftar publik yang tidak berasal dari satu baris tabel.
     *
     * `/event` dan `/jadwal-ramadhan` tidak ada di sini: keduanya musiman dan
     * berada di luar scope sitemap.
     */
    private const STATIC_PAGES = [
        'beranda',
        'guru-list',
        'majelis-list',
        'jadwal-majelis-list',
        'tulisan.list',
        'manaqib-list',
        'pustaka-list',
        'wirid-list',
        'video-list',
        'catatan-pengajian.list',
        'tentang-kami',
        'kontributor.index',
    ];

    public function __construct(private readonly SeoService $seo) {}

    public function __invoke(): Response
    {
        $xml = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => $this->build());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function build(): string
    {
        $urls = array_merge(
            $this->staticPages(),
            $this->teachers(),
            $this->assemblies(),
            $this->schedules(),
            $this->posts(),
            $this->articles(),
            $this->libraries(),
            $this->scheduleNotes(),
        );

        if (count($urls) > self::URL_WARNING_THRESHOLD) {
            Log::warning('Sitemap melewati ambang '.self::URL_WARNING_THRESHOLD.' URL; saatnya dipecah menjadi sitemap index.', [
                'total' => count($urls),
            ]);
        }

        return $this->render($urls);
    }

    /**
     * @return array<int, array{loc: string, lastmod: ?string, changefreq: string}>
     */
    private function staticPages(): array
    {
        return array_map(
            fn (string $routeName) => $this->entry($routeName, [], null, 'weekly'),
            self::STATIC_PAGES
        );
    }

    /**
     * `/manaqib/{slug}` hanya didaftarkan bila kolom `manaqib` benar-benar terisi.
     * Selama kosong, halaman itu merender `biografi` yang sama persis dengan
     * `/guru/{slug}` dan kanoniknya menunjuk ke sana — duplikat yang tidak boleh
     * masuk sitemap.
     */
    private function teachers(): array
    {
        $teachers = Teacher::query()
            ->publiclyVisible()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select('id', 'slug', 'manaqib', 'updated_at')
            ->orderBy('id')
            ->get();

        $entri = [];

        foreach ($teachers as $teacher) {
            $entri[] = $this->entry('guru-detail', $teacher->slug, $teacher->updated_at, 'monthly');

            // hasManaqib() menilai teks setelah tag dibuang, jadi tidak dapat
            // dinyatakan sebagai kondisi SQL.
            if ($teacher->hasManaqib()) {
                $entri[] = $this->entry('manaqib-detail', $teacher->slug, $teacher->updated_at, 'monthly');
            }
        }

        return $entri;
    }

    private function assemblies(): array
    {
        return Assembly::query()
            ->publiclyVisible()
            ->select('id', 'slug', 'updated_at')
            ->orderBy('id')
            ->get()
            ->map(fn (Assembly $assembly) => $this->entry('majelis-detail', $assembly->route_slug, $assembly->updated_at, 'weekly'))
            ->all();
    }

    private function schedules(): array
    {
        return Schedule::query()
            ->publiclyVisible()
            ->select('id', 'slug', 'updated_at')
            ->orderBy('id')
            ->get()
            ->map(fn (Schedule $schedule) => $this->entry('jadwal-majelis-detail', $schedule->route_slug, $schedule->updated_at, 'weekly'))
            ->all();
    }

    private function posts(): array
    {
        return Post::query()
            ->published()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select('id', 'slug', 'updated_at')
            ->orderBy('id')
            ->get()
            ->map(fn (Post $post) => $this->entry('tulisan.detail', $post->slug, $post->updated_at, 'monthly'))
            ->all();
    }

    private function articles(): array
    {
        return ScientificArticle::query()
            ->where('status', 'PUBLISHED')
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select('id', 'slug', 'updated_at')
            ->orderBy('id')
            ->get()
            ->map(fn (ScientificArticle $article) => $this->entry('artikel.detail', $article->slug, $article->updated_at, 'monthly'))
            ->all();
    }

    private function libraries(): array
    {
        return Library::query()
            ->where('is_active', true)
            ->where(function ($query) {
                // Cermin persis Library::isFree(), yang menganggap NULL sebagai gratis.
                // Migrasi menyetel kolom ini NOT NULL DEFAULT 'free', tetapi
                // `!= 'paid'` sendirian akan diam-diam membuang baris ber-NULL bila
                // skema pernah diubah manual — dan pustaka gratis jadi hilang dari indeks.
                $query->whereNull('price_type')
                    ->orWhere('price_type', '!=', 'paid');
            })
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->select('id', 'slug', 'updated_at')
            ->orderBy('id')
            ->get()
            ->map(fn (Library $library) => $this->entry('pustaka-detail', $library->slug, $library->updated_at, 'monthly'))
            ->all();
    }

    private function scheduleNotes(): array
    {
        return ScheduleNote::query()
            ->publiclyVisible()
            ->where('visibility', 'Public')
            ->where('status', 'Approved')
            ->select('id', 'updated_at')
            ->orderBy('id')
            ->get()
            ->map(fn (ScheduleNote $note) => $this->entry('catatan-pengajian.detail', $note->id, $note->updated_at, 'monthly'))
            ->all();
    }

    /**
     * @return array{loc: string, lastmod: ?string, changefreq: string}
     */
    private function entry(string $routeName, mixed $parameters, ?Carbon $lastmod, string $changefreq): array
    {
        return [
            'loc' => $this->seo->canonical(route($routeName, $parameters, false)),
            'lastmod' => $lastmod?->toAtomString(),
            'changefreq' => $changefreq,
        ];
    }

    /**
     * @param  array<int, array{loc: string, lastmod: ?string, changefreq: string}>  $urls
     */
    private function render(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= '    <url>'."\n";
            $xml .= '        <loc>'.$this->escape($url['loc']).'</loc>'."\n";

            if ($url['lastmod'] !== null) {
                $xml .= '        <lastmod>'.$this->escape($url['lastmod']).'</lastmod>'."\n";
            }

            $xml .= '        <changefreq>'.$this->escape($url['changefreq']).'</changefreq>'."\n";
            $xml .= '    </url>'."\n";
        }

        return $xml.'</urlset>'."\n";
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
