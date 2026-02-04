<div>
    @if($upcomingHauls->count() > 0)
        <!-- Title -->
        <header class="mb-6">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Haul Terdekat</h1>
        </header>

        <!-- Content -->
        <div class="grid xl:grid-cols-2 gap-6 mb-8">
            @foreach($upcomingHauls as $teacher)
            <a href="{{ route('manaqib-detail', $teacher->slug) }}" class="flex flex-col sm:flex-row bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden hover:shadow-lg transition-shadow duration-200">
                <!-- Image -->
                <div class="block w-full h-48 sm:h-auto sm:w-40 shrink-0">
                    <img class="w-full h-full object-cover"
                        src="{{ Storage::url($teacher->foto) }}"
                        alt="{{ $teacher->name }}"
                    />
                </div>
                <!-- Content -->
                <div class="grow p-5 flex flex-col justify-center">
                    <div class="text-sm font-semibold text-emerald-500 uppercase mb-2">
                        @php
                            $bulan = [
                                1 => 'Muharram',
                                2 => 'Safar',
                                3 => 'Rabiul Awal',
                                4 => 'Rabiul Akhir',
                                5 => 'Jumadil Awal',
                                6 => 'Jumadil Akhir',
                                7 => 'Rajab',
                                8 => 'Sya\'ban',
                                9 => 'Ramadhan',
                                10 => 'Syawal',
                                11 => 'Dzulqaidah',
                                12 => 'Dzulhijjah'
                            ];
                        @endphp
                        {{ $teacher->wafat_hijriah_day }} {{ $bulan[$teacher->wafat_hijriah_month] ?? $teacher->wafat_hijriah_month }}
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2">{{ $teacher->name }}</h3>

                    @if($teacher->province_code && $teacher->domisili !== '-')
                    <div class="flex items-center text-gray-600 dark:text-gray-300 text-sm">
                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ $teacher->village->name }}, {{ $teacher->district->name }}</span>
                    </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    @endif
</div>
