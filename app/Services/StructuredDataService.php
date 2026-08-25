<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

/**
 * Structured data (JSON-LD) untuk halaman publik.
 *
 * Setiap method mengembalikan blok <script type="application/ld+json"> yang siap
 * dicetak. Dibagikan ke seluruh view sebagai `$schema` lewat AppServiceProvider.
 *
 * Aturan yang dipegang di sini: **jangan mengarang properti**. Field yang datanya
 * tidak ada dihilangkan sama sekali, bukan diisi null atau string kosong —
 * validator Google menolak properti kosong, dan mengarang data terstruktur
 * berisiko lebih besar daripada tidak menyertakannya.
 */
class StructuredDataService
{
    /** Wilayah layanan Syaikhuna. */
    private const AREA_SERVED = [
        'Kalimantan Selatan',
        'Kalimantan Tengah',
        'Kalimantan Timur',
    ];

    public function __construct(private readonly SeoService $seo) {}

    /**
     * Identitas penerbit; dipasang di seluruh halaman publik lewat layout.
     *
     * `sameAs` (profil media sosial resmi) belum disertakan karena proyek ini
     * belum menyimpannya di mana pun — mengarang URL profil justru merusak
     * kepercayaan entitas di mata mesin telusur.
     */
    public function organization(): HtmlString
    {
        return $this->script([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name', 'Syaikhuna'),
            'url' => $this->seo->canonical('/'),
            'logo' => $this->seo->image(),
            'areaServed' => array_map(
                fn (string $nama) => ['@type' => 'AdministrativeArea', 'name' => $nama],
                self::AREA_SERVED
            ),
        ]);
    }

    /**
     * Hanya untuk beranda.
     *
     * `potentialAction`/`SearchAction` sengaja tidak diklaim: situs belum punya
     * endpoint pencarian global, dan klaim tanpa implementasi ditolak validator.
     */
    public function website(): HtmlString
    {
        return $this->script([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name', 'Syaikhuna'),
            'url' => $this->seo->canonical('/'),
            'inLanguage' => 'id-ID',
        ]);
    }

    public function person(Teacher $teacher, ?string $canonicalPath = null): HtmlString
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $teacher->name,
            'url' => $this->seo->canonical($canonicalPath ?? route('guru-detail', $teacher->slug, false)),
            'jobTitle' => 'Ulama',
        ];

        if (filled($teacher->biografi)) {
            $data['description'] = $this->seo->description($teacher->biografi);
        }

        if (filled($teacher->foto)) {
            $data['image'] = $this->seo->image($teacher->foto);
        }

        if ($tanggalWafat = $this->tanggal($teacher->wafat_masehi)) {
            $data['deathDate'] = $tanggalWafat->format('Y-m-d');
        }

        if ($tempat = $this->tempatTinggal($teacher)) {
            $data['homeLocation'] = $tempat;
        }

        return $this->script($data);
    }

    public function event(Event $event): HtmlString
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->name,
            'url' => $this->seo->canonical(route('event-detail', $event->route_slug, false)),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => $this->tempatAcara($event),
        ];

        if ($mulai = $this->tanggal($event->date)) {
            // Tanpa offset zona waktu, Google menafsirkannya sebagai waktu lokal
            // perayap. Acara di sini selalu WITA (config app.timezone).
            $data['startDate'] = $mulai->toIso8601String();
        }

        if (filled($event->image)) {
            $data['image'] = $this->seo->image($event->image);
        }

        if ($event->assembly) {
            $data['organizer'] = [
                '@type' => 'Organization',
                'name' => $event->assembly->nama_majelis,
                'url' => $this->seo->canonical(route('majelis-detail', $event->assembly->route_slug, false)),
            ];
        }

        return $this->script($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function tempatAcara(Event $event): array
    {
        $alamat = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $event->location,
            'addressLocality' => $event->district?->name ?? $event->city?->name,
            'addressRegion' => $event->province?->name,
            'addressCountry' => 'ID',
        ], fn ($nilai) => filled($nilai));

        return [
            '@type' => 'Place',
            'name' => $event->location,
            'address' => $alamat,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function tempatTinggal(Teacher $teacher): ?array
    {
        $alamat = array_filter([
            'addressLocality' => $teacher->district?->name ?? $teacher->city?->name,
            'addressRegion' => $teacher->province?->name,
        ], fn ($nilai) => filled($nilai));

        if ($alamat === []) {
            return null;
        }

        return [
            '@type' => 'Place',
            'address' => array_merge(['@type' => 'PostalAddress'], $alamat, ['addressCountry' => 'ID']),
        ];
    }

    /**
     * Kolom tanggal pada model-model ini tidak di-cast, jadi nilainya bisa berupa
     * string, Carbon, atau null. Nilai yang tidak dapat diurai diabaikan diam-diam
     * agar satu baris data rusak tidak menjatuhkan seluruh halaman.
     */
    private function tanggal(mixed $nilai): ?Carbon
    {
        if (blank($nilai)) {
            return null;
        }

        try {
            return Carbon::parse($nilai);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function script(array $data): HtmlString
    {
        // JSON_HEX_TAG mengubah kurung sudut menjadi escape unicode, sehingga tag
        // penutup script yang menyelinap di dalam nilai data tidak dapat menutup
        // blok ini lebih awal. Hasilnya tetap JSON yang sah.
        $json = json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );

        return new HtmlString('<script type="application/ld+json">'.$json.'</script>');
    }
}
