<x-user-layout>
    @section('title', 'Pustaka Saya')

    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Pustaka Saya</h1>
                <p class="text-sm text-gray-500 mt-1">Riwayat pembelian pustaka berbayar Anda.</p>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn-sm px-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300" href="{{ route('pustaka-list') }}">
                    <span>Jelajahi Pustaka</span>
                </a>
            </div>
        </div>

        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('message') }}</div>
        @endif

        <div class="space-y-3">
            @forelse($purchases as $purchase)
                @php($library = $purchase->library)
                <div class="bg-white dark:bg-gray-800 p-4 shadow-xs rounded-xl border border-gray-100 dark:border-gray-700/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-gray-800 dark:text-gray-100">
                            @if($library)
                                <a href="{{ route('pustaka-detail', $library) }}" class="hover:text-indigo-600">{{ $library->title }}</a>
                            @else
                                <span class="text-gray-400">Pustaka tidak tersedia</span>
                            @endif
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Rp {{ number_format($purchase->price, 0, ',', '.') }} · {{ $purchase->created_at->format('d M Y') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($purchase->status === 'active')
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Aktif</span>
                            @if($library)
                                <a href="{{ route('pustaka-read', $library) }}" target="_blank" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Baca</a>
                            @endif
                        @elseif($purchase->status === 'pending')
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Menunggu verifikasi</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Ditolak</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 p-10 text-center text-gray-400 rounded-xl border border-gray-100 dark:border-gray-700/60">
                    Belum ada pembelian pustaka.
                </div>
            @endforelse
        </div>

        @if($purchases->hasPages())
            <div class="mt-6">{{ $purchases->links() }}</div>
        @endif
    </div>
</x-user-layout>
