<?php

namespace App\Http\Resources\Api\V1;

use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\Teacher $resource
 */
class HaulResource extends JsonResource
{
    public function __construct(\App\Models\Teacher $teacher, private readonly CarbonImmutable $nextHaul, private readonly int $daysUntil)
    {
        parent::__construct($teacher);
    }

    /**
     * Dipakai hanya untuk merangkai label keluaran, bukan untuk mem-parse input.
     */
    private const HIJRI_MONTHS = [
        1 => 'Muharram', 2 => 'Safar', 3 => 'Rabiul Awal', 4 => 'Rabiul Akhir',
        5 => 'Jumadil Awal', 6 => 'Jumadil Akhir', 7 => 'Rajab', 8 => 'Syakban',
        9 => 'Ramadhan', 10 => 'Syawal', 11 => 'Zulkaidah', 12 => 'Zulhijjah',
    ];

    public function toArray($request): array
    {
        $fotoUrl = $this->resource->foto_url;

        return [
            'id' => $this->resource->id,
            'nama' => $this->resource->name,
            'slug' => $this->resource->slug,
            'wafat_hijriah_label' => $this->hijriLabel(),
            'wafat_hijriah_day' => $this->resource->wafat_hijriah_day,
            'wafat_hijriah_month' => $this->resource->wafat_hijriah_month,
            'wafat_hijriah_year' => $this->resource->wafat_hijriah_year,
            'wafat_masehi' => $this->resource->wafat_masehi
                ? CarbonImmutable::parse($this->resource->wafat_masehi)->toDateString()
                : null,
            'haul_berikutnya_masehi' => $this->nextHaul->toDateString(),
            'hari_menuju_haul' => $this->daysUntil,
            'foto_url' => $fotoUrl ? url($fotoUrl) : null,
            'url' => url('/guru/'.$this->resource->slug),
        ];
    }

    private function hijriLabel(): ?string
    {
        $month = self::HIJRI_MONTHS[$this->resource->wafat_hijriah_month] ?? null;

        if (! $month) {
            return null;
        }

        return trim(sprintf(
            '%d %s %s',
            $this->resource->wafat_hijriah_day,
            $month,
            $this->resource->wafat_hijriah_year ?: ''
        ));
    }
}
