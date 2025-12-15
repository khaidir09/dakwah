<div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm">
    <div class="flex items-center justify-between mb-4">
        <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">Jadwal Sholat</div>
        <div class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
            {{ $location ?? 'Jakarta' }}
        </div>
    </div>

    @if($schedule)
        <div class="space-y-3">
            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Imsak</span>
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200 font-mono">{{ $schedule['imsak'] }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors bg-green-50/50 dark:bg-green-900/10 border border-green-100 dark:border-green-900/20">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Subuh</span>
                <span class="text-sm font-bold text-green-700 dark:text-green-400 font-mono">{{ $schedule['subuh'] }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Dzuhur</span>
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200 font-mono">{{ $schedule['dzuhur'] }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Ashar</span>
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200 font-mono">{{ $schedule['ashar'] }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-900/20">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Maghrib</span>
                <span class="text-sm font-bold text-indigo-700 dark:text-indigo-400 font-mono">{{ $schedule['maghrib'] }}</span>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Isya</span>
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200 font-mono">{{ $schedule['isya'] }}</span>
            </div>
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
