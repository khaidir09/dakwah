<div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm mb-4">
    <div class="flex items-center justify-between mb-4">
        <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">Bacaan Sholat Hari Ini</div>
        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
            {{ $dayName }}
        </div>
    </div>

    @if($readings->count() > 0)
        <div class="space-y-3">
            @foreach($readings as $reading)
                <div class="flex flex-col p-2 rounded-lg bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-900/20">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300 capitalize">{{ $reading->prayer }}</span>
                    </div>
                    <div class="text-sm text-gray-800 dark:text-gray-200">
                        {{ $reading->surah_name }}
                    </div>
                    @if($reading->description)
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">
                            {{ $reading->description }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-4 text-sm text-gray-500 dark:text-gray-400">
            Belum ada data bacaan untuk hari ini.
        </div>
    @endif
</div>
