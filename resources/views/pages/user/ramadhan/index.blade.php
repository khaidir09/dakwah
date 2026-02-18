<x-user-layout>
    @section('title', 'Jadwal Ramadhan')
    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">

        <div class="xl:flex">

            <!-- Left + Middle content -->
            <div class="md:flex flex-1">

                <!-- Left content -->
                <div class="hidden md:block w-full md:w-60 mb-8 md:mb-0">
                     <x-community.feed-left-content />
                </div>

                <!-- Middle content -->
                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">

                        <div class="space-y-4">

                            <!-- Title -->
                            <header class="mb-5">
                                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Jadwal Ramadhan</h1>
                            </header>

                            <!-- Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @forelse($schedules as $schedule)
                                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 border border-gray-100 dark:border-gray-700/60 flex flex-col h-full hover:shadow-md transition-shadow duration-200">

                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex items-center gap-3">
                                                @if($schedule->assembly->gambar)
                                                     <img class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700" src="{{ $schedule->assembly->gambar_thumb_url }}" alt="{{ $schedule->assembly->nama_majelis }}">
                                                @else
                                                     <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-sm ring-2 ring-gray-100 dark:ring-gray-700">
                                                        {{ substr($schedule->assembly->nama_majelis, 0, 2) }}
                                                     </div>
                                                @endif
                                                <div>
                                                    <h3 class="font-semibold text-gray-800 dark:text-gray-100 line-clamp-1 text-sm md:text-base">{{ $schedule->assembly->nama_majelis }}</h3>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                        {{ $schedule->assembly->district->name ?? 'Lokasi tidak tersedia' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-4 flex-grow">
                                            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-1">{{ $schedule->title }}</h2>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                                {{ $schedule->description }}
                                            </p>
                                        </div>

                                        <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100 dark:border-gray-700/60">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                                {{ $schedule->hijri_year }} H
                                            </span>
                                            <a href="{{ route('ramadhan-detail', $schedule->id) }}" class="inline-flex items-center text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors duration-150">
                                                Lihat Jadwal
                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-full flex flex-col items-center justify-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-700/50 mb-4">
                                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Belum ada jadwal</h3>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 text-center max-w-xs">Saat ini belum ada jadwal Ramadhan yang tersedia untuk ditampilkan.</p>
                                    </div>
                                @endforelse
                            </div>

                            <!-- Pagination -->
                            <div class="mt-6">
                                {{ $schedules->links() }}
                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <!-- Right content -->
            <x-community.feed-right-content />

        </div>

    </div>
</x-user-layout>
