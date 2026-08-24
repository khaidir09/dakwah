<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read \App\Models\Wirid $resource
 */
class WiridResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'kategori' => $this->resource->kategori,
            'nama' => $this->resource->nama,
            'waktu' => $this->resource->waktu,
            'jumlah' => $this->resource->jumlah,
            'arab_html' => $this->html($this->resource->arab),
            'arab_text' => $this->text($this->resource->arab),
            'arti_html' => $this->html($this->resource->arti),
            'arti_text' => $this->text($this->resource->arti),
            'deskripsi_html' => $this->html($this->resource->deskripsi),
            'deskripsi_text' => $this->text($this->resource->deskripsi),
            'url' => url('/wirid'),
        ];
    }

    /**
     * Konten lama di DB belum tentu tersimpan dalam keadaan bersih,
     * jadi pembersihan dilakukan saat serialisasi.
     */
    private function html(?string $value): ?string
    {
        return $value === null || $value === '' ? null : clean($value);
    }

    private function text(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $stripped = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $stripped)) ?: null;
    }
}
