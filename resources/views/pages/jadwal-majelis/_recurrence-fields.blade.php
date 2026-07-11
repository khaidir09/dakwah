@php
    $jadwal = $jadwal ?? null;
    $recType = old('recurrence_type', $jadwal?->recurrence_type ?? 'weekly');
    $recHari = old('hari', $jadwal?->hari);
    $recWeek = old('week_of_month', $jadwal?->week_of_month);
    $recWeek2 = old('week_of_month_secondary', $jadwal?->week_of_month_secondary);
    $recDay = old('day_of_month', $jadwal?->day_of_month);

    $weekdays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    $weeks = ['1' => 'Pekan ke-1', '2' => 'Pekan ke-2', '3' => 'Pekan ke-3', '4' => 'Pekan ke-4', 'last' => 'Pekan terakhir'];
    $recTypeLabels = [
        'weekly' => 'Mingguan (setiap pekan)',
        'monthly_weekday' => 'Bulanan — pekan + hari',
        'monthly_date' => 'Bulanan — tanggal tetap',
        'semimonthly' => 'Dua kali sebulan',
        'hijri_first_week' => 'Pekan pertama Hijriah (1–7)',
    ];
@endphp

<div x-data="{ rec: '{{ $recType }}' }" class="contents">
    <div>
        <label class="block text-sm font-medium mb-2" for="recurrence_type">Tipe Jadwal <span class="text-red-500">*</span></label>
        <select id="recurrence_type" class="form-select w-full @error('recurrence_type') is-invalid @enderror" name="recurrence_type" x-model="rec">
            @foreach($recTypeLabels as $value => $label)
                <option value="{{ $value }}" {{ $recType === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('recurrence_type')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
        <p class="text-xs mt-1 text-gray-500 dark:text-gray-400" x-show="rec !== 'weekly'">
            Jadwal non-mingguan tampil di seksi "Jadwal Berkala", bukan di widget "Jadwal Hari Ini".
        </p>
    </div>

    <div x-show="rec !== 'monthly_date'">
        <label class="block text-sm font-medium mb-2" for="hari">Hari <span class="text-red-500">*</span></label>
        <select id="hari" class="form-select w-full @error('hari') is-invalid @enderror" name="hari" :disabled="rec === 'monthly_date'">
            <option value="">Pilih Hari</option>
            @if($recHari && ! in_array($recHari, $weekdays))
                <option value="{{ $recHari }}" selected>{{ $recHari }}</option>
            @endif
            @foreach($weekdays as $h)
                <option value="{{ $h }}" {{ $recHari === $h ? 'selected' : '' }}>{{ $h }}</option>
            @endforeach
        </select>
        @error('hari')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
    </div>

    <div x-show="rec === 'monthly_weekday' || rec === 'semimonthly'">
        <label class="block text-sm font-medium mb-2" for="week_of_month">Pekan <span class="text-red-500">*</span></label>
        <select id="week_of_month" class="form-select w-full @error('week_of_month') is-invalid @enderror" name="week_of_month" :disabled="rec !== 'monthly_weekday' && rec !== 'semimonthly'">
            <option value="">Pilih Pekan</option>
            @foreach($weeks as $value => $label)
                <option value="{{ $value }}" {{ (string) $recWeek === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('week_of_month')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
    </div>

    <div x-show="rec === 'semimonthly'">
        <label class="block text-sm font-medium mb-2" for="week_of_month_secondary">Pekan Kedua <span class="text-red-500">*</span></label>
        <select id="week_of_month_secondary" class="form-select w-full @error('week_of_month_secondary') is-invalid @enderror" name="week_of_month_secondary" :disabled="rec !== 'semimonthly'">
            <option value="">Pilih Pekan Kedua</option>
            @foreach($weeks as $value => $label)
                <option value="{{ $value }}" {{ (string) $recWeek2 === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('week_of_month_secondary')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
    </div>

    <div x-show="rec === 'monthly_date'">
        <label class="block text-sm font-medium mb-2" for="day_of_month">Tanggal (1–31) <span class="text-red-500">*</span></label>
        <input id="day_of_month" class="form-input w-full @error('day_of_month') is-invalid @enderror" type="number" min="1" max="31" name="day_of_month" value="{{ $recDay }}" :disabled="rec !== 'monthly_date'"/>
        @error('day_of_month')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
    </div>
</div>
