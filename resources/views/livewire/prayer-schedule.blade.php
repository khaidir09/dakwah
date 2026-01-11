<div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">Jadwal Sholat</div>
        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
            {{ $location ?? 'Hulu Sungai Utara' }}
        </div>
    </div>

    @if($schedule)
        <div class="space-y-1">
            @foreach($prayerList as $key)
                @php
                    $isGreen = ($key === $activePrayer);
                    $isIndigo = ($key === $nextPrayer);
                    $label = ucfirst($key);
                    $time = $schedule[$key];
                    
                    // Dynamic classes
                    $containerClasses = "flex items-center justify-between p-2 rounded-lg transition-colors";
                    $labelClasses = "text-sm font-medium";
                    $timeClasses = "text-sm font-bold font-mono";

                    if ($isGreen) {
                        $containerClasses .= " bg-green-50/50 dark:bg-green-900/10 border border-green-100 dark:border-green-900/20";
                        $labelClasses .= " text-green-700 dark:text-green-300";
                        $timeClasses .= " text-green-700 dark:text-green-400";
                    } elseif ($isIndigo) {
                        $containerClasses .= " bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-900/20";
                        $labelClasses .= " text-indigo-700 dark:text-indigo-300";
                        $timeClasses .= " text-indigo-700 dark:text-indigo-400";
                    } else {
                        $containerClasses .= " hover:bg-gray-50 dark:hover:bg-gray-700/50";
                        $labelClasses .= " text-gray-600 dark:text-gray-400";
                        $timeClasses .= " text-gray-800 dark:text-gray-200";
                    }
                @endphp

                <div class="{{ $containerClasses }}">
                    <span class="{{ $labelClasses }}">{{ $label }}</span>
                    <span class="{{ $timeClasses }}">{{ $time }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 text-center">
            <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $schedule['tanggal'] }}</span>
        </div>
    @else
        <div class="text-center py-4 text-sm text-gray-500 dark:text-gray-400">
            Gagal memuat jadwal sholat.
        </div>
    @endif
</div>
