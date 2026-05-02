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
            <div class="bg-white rounded-lg shadow p-4 border">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="font-bold text-lg">{{ $note->schedule->assembly->nama_majelis ?? 'Majelis' }}</h3>
                        <p class="text-sm text-gray-500">Oleh: {{ $note->user->name }} | {{ $note->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                <div class="prose max-w-none text-gray-700 mt-2">
                    {!! nl2br(e($note->content)) !!}
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
