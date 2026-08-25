<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * URL kanonik berbentuk "{id}-{slug}", mis. /majelis/42-majelis-ar-raudhah.
 *
 * Resolusinya **tetap lewat ID**: `(int) '42-majelis-ar-raudhah'` menghasilkan 42
 * karena PHP berhenti di karakter non-digit pertama. Konsekuensinya slug bersifat
 * kosmetik semata:
 *
 * - tidak perlu unik (dua majelis boleh bernama sama — ID yang membedakan);
 * - boleh kosong (nama non-latin menghasilkan slug kosong → URL cukup "/majelis/42");
 * - boleh berubah saat entitas diganti nama, sebab controller me-301-kan bentuk
 *   URL apa pun ke bentuk kanonik terkini.
 *
 * Model pemakai wajib mendefinisikan konstanta ROUTE_SLUG_SOURCE berisi nama
 * kolom sumber slug.
 */
trait HasRouteSlug
{
    /**
     * Slug dijaga di level model, bukan di controller, karena entitas ini dibuat
     * dan diubah dari banyak jalur (admin, pemilik majelis, kontributor, seeder).
     */
    public static function bootHasRouteSlug(): void
    {
        static::saving(function (self $model) {
            $sumber = static::ROUTE_SLUG_SOURCE;

            if ($model->isDirty($sumber) || blank($model->slug)) {
                $model->slug = static::makeRouteSlug($model->{$sumber});
            }
        });
    }

    /**
     * Parameter URL kanonik. Jatuh ke ID saja bila slug tidak terbentuk.
     */
    public function getRouteSlugAttribute(): string
    {
        return filled($this->slug)
            ? $this->id.'-'.$this->slug
            : (string) $this->id;
    }

    /**
     * Null bila sumbernya tidak menghasilkan slug latin apa pun.
     */
    public static function makeRouteSlug(?string $source): ?string
    {
        $slug = Str::slug((string) $source);

        return $slug === '' ? null : $slug;
    }

    /**
     * "42-majelis-ar-raudhah" → 42. Parameter non-numerik menghasilkan 0,
     * sehingga findOrFail() menutupnya dengan 404.
     */
    public static function idFromRouteParam(?string $param): int
    {
        return (int) $param;
    }
}
