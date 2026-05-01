<div>
    <!-- Top 3 Pencatat Terbanyak -->
    <div class="mb-8">
        <h2 class="text-xl font-bold mb-4">Top 3 Pencatat Terbanyak</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($topUsers as $topUser)
            <div class="bg-white rounded-lg shadow p-4 border">
                <div class="flex items-center space-x-4">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500 font-bold">
                        {{ substr($topUser->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold">{{ $topUser->name }}</p>
                        <p class="text-sm text-gray-500">{{ $topUser->notes_count }} Catatan</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- List Catatan -->
    <div>
        <h2 class="text-xl font-bold mb-4">Catatan Pengajian Terbaru</h2>
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
