<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JadwalMajelisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_code' => ['required', 'string', Rule::exists('indonesia_cities', 'code')],
            'district_code' => ['nullable', 'string', Rule::exists('indonesia_districts', 'code')],
            'days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'tipe' => ['nullable', Rule::in(['Majelis', 'Mesjid', 'Langgar', 'Musholla'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'city_code' => 'kode kota/kabupaten',
            'district_code' => 'kode kecamatan',
            'days' => 'jumlah hari',
        ];
    }

    public function days(): int
    {
        return (int) ($this->validated('days') ?? 7);
    }
}
