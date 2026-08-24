<?php

namespace App\Http\Resources\Api\V1;

use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\Schedule $resource
 */
class ScheduleOccurrenceResource extends JsonResource
{
    public function __construct(private readonly \App\Models\Schedule $schedule, private readonly CarbonImmutable $date)
    {
        parent::__construct($schedule);
    }

    public function toArray($request): array
    {
        $assembly = $this->schedule->assembly;
        $teacher = $this->schedule->teacher;

        return [
            'schedule_id' => $this->schedule->id,
            'tanggal' => $this->date->toDateString(),
            'hari' => $this->date->locale('id')->isoFormat('dddd'),
            'waktu' => $this->schedule->waktu
                ? CarbonImmutable::parse($this->schedule->waktu)->format('H:i')
                : null,
            'nama_jadwal' => $this->schedule->nama_jadwal,
            'recurrence_label' => $this->schedule->recurrence_label,
            'access' => $this->schedule->access,
            'majelis' => $assembly ? [
                'id' => $assembly->id,
                'nama' => $assembly->nama_majelis,
                'tipe' => $assembly->tipe,
                'alamat' => $assembly->alamat,
                'maps' => $assembly->maps,
                'city_code' => $assembly->city_code,
                'district_code' => $assembly->district_code,
                'gambar_url' => $this->absoluteUrl($assembly->gambar_thumb_url),
            ] : null,
            'guru' => $teacher ? [
                'id' => $teacher->id,
                'nama' => $teacher->name,
                'slug' => $teacher->slug,
                'foto_url' => $this->absoluteUrl($teacher->foto_url),
            ] : null,
            'url' => url('/jadwal-majelis/'.$this->schedule->id),
        ];
    }

    private function absoluteUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : url($path);
    }
}
