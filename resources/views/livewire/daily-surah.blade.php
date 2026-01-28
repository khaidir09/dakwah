<div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm" x-data="{ open: false }">
    <button class="w-full flex items-center justify-between" @click="open = !open">
        <div class="flex items-center space-x-2">
            <div class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase">Bacaan Sholat Hari Ini</div>
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                {{ $dayName }}
            </div>
        </div>
        <div class="ml-2">
            <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <div x-show="open" x-collapse class="mt-4">
        @if($readings->count() > 0)
            <div class="space-y-2">
                @foreach($readings as $reading)
                    <div x-data="{ showVerse: false }" class="flex flex-col p-2 rounded-lg bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-900/20">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-bold text-indigo-700 dark:text-indigo-300 capitalize">{{ $reading->prayer }}</span>
                            @if($reading->description)
                                <button @click="showVerse = !showVerse" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-200 focus:outline-none" title="Lihat Ayat">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path x-show="!showVerse" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path x-show="!showVerse" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        <path x-show="showVerse" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                        <div x-show="!showVerse" class="text-sm text-gray-800 dark:text-gray-200">
                            {{ $reading->surah_name }}
                        </div>
                        @if($reading->description)
                            <div x-show="showVerse" style="display: none;" class="text-sm text-gray-800 dark:text-gray-200 mt-1 italic">
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
</div>
