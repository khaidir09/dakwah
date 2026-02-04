<div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center space-x-2">
            <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">
                Kalender Hari Ini
            </div>
        </div>
        <!-- Optional: Icon or decoration -->
        <div class="text-emerald-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    </div>

    <div class="space-y-1">
        <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
            {{ $gregorianDate }}
        </div>
        <div class="text-sm font-medium text-emerald-600 dark:text-emerald-400">
            {{ $hijriDate }}
        </div>
    </div>
</div>
