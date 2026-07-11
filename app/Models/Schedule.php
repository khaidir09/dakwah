<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Schedule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'day_of_month' => 'integer',
    ];

    public const RECURRENCE_TYPES = [
        'weekly',
        'monthly_weekday',
        'monthly_date',
        'semimonthly',
        'hijri_first_week',
    ];

    public const WEEKS_OF_MONTH = ['1', '2', '3', '4', 'last'];

    /**
     * Aturan validasi untuk field recurrence, bergantung pada tipe yang dikirim.
     * Field `hari` ikut diatur di sini karena kewajibannya tergantung tipe.
     * Digunakan oleh controller admin & kontributor sebagai satu sumber kebenaran.
     */
    public static function recurrenceRules(?string $type): array
    {
        $type = in_array($type, self::RECURRENCE_TYPES, true) ? $type : 'weekly';
        $needsHari = $type !== 'monthly_date';
        $needsWeek = in_array($type, ['monthly_weekday', 'semimonthly'], true);

        return [
            'recurrence_type' => ['nullable', Rule::in(self::RECURRENCE_TYPES)],
            'hari' => [$needsHari ? 'required' : 'nullable', 'string', 'max:50'],
            'week_of_month' => [Rule::requiredIf($needsWeek), 'nullable', Rule::in(self::WEEKS_OF_MONTH)],
            'week_of_month_secondary' => [
                Rule::requiredIf($type === 'semimonthly'),
                'nullable',
                Rule::in(self::WEEKS_OF_MONTH),
                'different:week_of_month',
            ],
            'day_of_month' => [Rule::requiredIf($type === 'monthly_date'), 'nullable', 'integer', 'min:1', 'max:31'],
        ];
    }

    /**
     * Kosongkan field recurrence yang tidak relevan dengan tipe terpilih,
     * dan set calendar_system otomatis, sebelum disimpan.
     */
    public static function normalizeRecurrence(array $data): array
    {
        $type = in_array($data['recurrence_type'] ?? null, self::RECURRENCE_TYPES, true)
            ? $data['recurrence_type']
            : 'weekly';

        $data['recurrence_type'] = $type;
        $data['calendar_system'] = $type === 'hijri_first_week' ? 'hijri' : 'gregorian';

        if ($type === 'monthly_date') {
            $data['hari'] = null;
        }
        if (! in_array($type, ['monthly_weekday', 'semimonthly'], true)) {
            $data['week_of_month'] = null;
        }
        if ($type !== 'semimonthly') {
            $data['week_of_month_secondary'] = null;
        }
        if ($type !== 'monthly_date') {
            $data['day_of_month'] = null;
        }

        return $data;
    }

    public function scopeWeekly($query)
    {
        return $query->where('recurrence_type', 'weekly');
    }

    public function scopeBerkala($query)
    {
        return $query->where('recurrence_type', '!=', 'weekly');
    }

    protected function weekLabel(?string $week): string
    {
        return $week === 'last' ? 'pekan terakhir' : 'pekan ke-'.$week;
    }

    public function getRecurrenceLabelAttribute(): string
    {
        return match ($this->recurrence_type) {
            'monthly_weekday' => $this->hari.', '.$this->weekLabel($this->week_of_month).' tiap bulan',
            'monthly_date' => 'Tiap tanggal '.$this->day_of_month,
            'semimonthly' => $this->hari.', '.$this->weekLabel($this->week_of_month)
                .' & '.$this->weekLabel($this->week_of_month_secondary).' tiap bulan',
            'hijri_first_week' => $this->hari.', pekan pertama tiap bulan Hijriah (1–7)',
            default => 'Setiap '.$this->hari,
        };
    }

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function notes()
    {
        return $this->hasMany(ScheduleNote::class);
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributor_user_id');
    }

    public function contribution()
    {
        return $this->morphOne(Contribution::class, 'contributable');
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('contribution_status')
                ->orWhere('contribution_status', 'approved');
        });
    }

    public function getWaktuFormattedAttribute()
    {
        // Cek jika 'waktu' ada isinya sebelum mem-format
        if ($this->waktu) {
            // Pilih salah satu format yang kamu suka dari atas
            // Contoh ini menggunakan Rekomendasi 1 (AM/PM)
            return Carbon::parse($this->waktu)->locale('id')->isoFormat('LT');

            // Jika suka format Indonesia (Rekomendasi 3):
            // return Carbon::parse($this->waktu)->locale('id')->isoFormat('LT');
        }

        return 'N/A'; // Tampilkan ini jika waktunya null
    }
}
