<x-user-layout>
    @section('title', $schedule->title)
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

                             <!-- Back Button -->
                             <div class="mb-4">
                                <a href="{{ route('ramadhan-list') }}" class="inline-flex items-center text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Kembali ke Daftar Jadwal
                                </a>
                            </div>

                            <!-- Header -->
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 border border-gray-100 dark:border-gray-700/60">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                         @if($schedule->assembly->gambar)
                                            <img class="w-16 h-16 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700" src="{{ $schedule->assembly->gambar_thumb_url }}" alt="{{ $schedule->assembly->nama_majelis }}">
                                        @else
                                            <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 font-bold text-xl ring-2 ring-gray-100 dark:ring-gray-700">
                                                {{ substr($schedule->assembly->nama_majelis, 0, 2) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $schedule->title }}</h1>
                                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $schedule->assembly->nama_majelis }}</span>
                                                <span class="text-gray-300 dark:text-gray-600">•</span>
                                                <span class="text-gray-500 dark:text-gray-400">{{ $schedule->hijri_year }} H</span>
                                                @if($schedule->assembly->district)
                                                    <span class="text-gray-300 dark:text-gray-600">•</span>
                                                    <span class="text-gray-500 dark:text-gray-400">{{ $schedule->assembly->district->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Share/Action Buttons if needed -->
                                </div>
                                @if($schedule->description)
                                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/60">
                                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                                            {{ $schedule->description }}
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Table -->
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700/60">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left">
                                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase font-semibold">
                                            <tr>
                                                <th class="px-6 py-4 w-20 text-center">Hari</th>
                                                <th class="px-6 py-4 w-40">Tanggal</th>
                                                <th class="px-6 py-4 w-1 whitespace-nowrap">Penceramah</th>
                                                @if ($schedule->lectures->whereNotNull('title')->count() > 0)
                                                    <th class="px-6 py-4">Judul / Tema</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60">
                                            @forelse($schedule->lectures as $lecture)
                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                                    <td class="px-6 py-4 text-center">
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 font-bold text-sm">
                                                            {{ $lecture->day }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($schedule->gregorian_start_date)->addDays($lecture->day - 1)->locale('id')->translatedFormat('l, d M Y') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        @if($lecture->teacher)
                                                            <div class="flex items-center gap-3">
                                                                @if($lecture->teacher->foto)
                                                                    <img class="w-8 h-8 rounded-full object-cover" src="{{ \Illuminate\Support\Facades\Storage::url($lecture->teacher->foto) }}" alt="{{ $lecture->teacher->name }}">
                                                                @else
                                                                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 text-xs font-bold">
                                                                        {{ substr($lecture->teacher->name, 0, 1) }}
                                                                    </div>
                                                                @endif
                                                                <a href="{{ route('guru-detail', $lecture->teacher) }}" class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $lecture->teacher->name }}</a>
                                                            </div>
                                                        @else
                                                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $lecture->custom_speaker_name ?? '-' }}</span>
                                                        @endif
                                                    </td>
                                                    @if ($schedule->lectures->whereNotNull('title')->count() > 0)
                                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                            {{ $lecture->title ?? '-' }}
                                                        </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                                        Belum ada jadwal ceramah yang diinputkan.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
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
