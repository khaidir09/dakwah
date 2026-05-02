<div>
    <div class="col-span-full xl:col-span-8 bg-white dark:bg-gray-800 shadow-xs rounded-xl mb-8">
        <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Pencatat Terbanyak</h2>
        </header>
        <div class="p-3">

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="table-auto w-full dark:text-gray-300">
                    <!-- Table header -->
                    <thead class="text-xs uppercase text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/50 rounded-xs">
                        <tr>
                            <th class="p-2">
                                <div class="font-semibold text-left">Pencatat</div>
                            </th>
                            <th class="p-2">
                                <div class="font-semibold text-center">Jumlah Catatan</div>
                            </th>
                        </tr>
                    </thead>
                    <!-- Table body -->
                    <tbody class="text-sm font-medium divide-y divide-gray-100 dark:divide-gray-700/60">
                        <!-- Row -->
                        @foreach($topUsers as $topUser)
                        <tr>
                            <td class="p-2">
                                <div class="flex items-center">
                                    @if($topUser->profile_photo_path != null)
                                        <img class="rounded-full w-10 h-10 object-cover" src="{{ Storage::url($topUser->profile_photo_path) }}" alt="{{ $topUser->name }}" />
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 font-bold">
                                            {{ substr($topUser->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="text-gray-800 dark:text-gray-100 ml-3">{{ $topUser->name }}</div>
                                </div>
                            </td>
                            <td class="p-2">
                                <div class="text-center">{{ $topUser->notes_count }} Catatan</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <!-- List Catatan -->
    <div>
        <div class="space-y-4">
            @foreach($notes as $note)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xs p-5 border border-gray-100 dark:border-gray-700/60 transition-all hover:shadow-md">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                    <div class="space-y-1 flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ $note->schedule->nama_jadwal ?? 'Jadwal Majelis' }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $note->created_at->format('d M Y') }}
                            </span>
                        </div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ $note->schedule->assembly->nama_majelis ?? 'Majelis' }}</h3>
                        <div class="flex items-center gap-2 mt-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span>Pencatat: <strong>{{ $note->user->name }}</strong></span>
                        </div>
                    </div>
                    <div class="md:ml-4 shrink-0">
                        <a href="{{ route('catatan-pengajian.detail', $note->id) }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors w-full md:w-auto">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($hasMore)
        <div class="mt-6 text-center">
            <button wire:click="loadMore" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition-colors">
                Muat Lebih Banyak
            </button>
        </div>
        @endif
    </div>
</div>
