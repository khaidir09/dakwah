<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Pembelian Pustaka Berbayar</h1>
            <p class="text-sm text-gray-500 mt-1">Verifikasi pembayaran manual: aktifkan akses pembeli setelah dana diterima, atau tolak permintaan.</p>
        </div>

        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('message') }}</div>
        @endif

        {{-- Filter status --}}
        <div class="flex flex-wrap gap-2 mb-5">
            @foreach(['pending' => 'Menunggu', 'active' => 'Aktif', 'rejected' => 'Ditolak'] as $key => $label)
                <a href="{{ route('admin.library-purchases.index', ['status' => $key]) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium border {{ $status === $key ? 'bg-violet-500 text-white border-violet-500' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3">Pembeli</th>
                            <th class="px-4 py-3">Pustaka</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Aksi / Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($purchases as $purchase)
                        <tr class="align-top">
                            <td class="px-4 py-4 font-medium text-gray-800 dark:text-gray-100">
                                {{ $purchase->user?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">
                                {{ $purchase->library?->title ?? '—' }}
                            </td>
                            <td class="px-4 py-4 font-semibold text-gray-800 dark:text-gray-100">Rp {{ number_format($purchase->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-gray-500">{{ $purchase->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-4">
                                @if($purchase->status === 'pending')
                                    <div x-data="{ panel: null }" class="space-y-2">
                                        <div class="flex gap-2">
                                            <form method="POST" action="{{ route('admin.library-purchases.activate', $purchase) }}">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn-sm bg-emerald-500 hover:bg-emerald-600 text-white">Aktifkan</button>
                                            </form>
                                            <button type="button" @click="panel = (panel === 'reject' ? null : 'reject')"
                                                class="btn-sm bg-red-500 hover:bg-red-600 text-white">Tolak</button>
                                        </div>

                                        {{-- Form Tolak --}}
                                        <form x-show="panel === 'reject'" x-cloak method="POST"
                                              action="{{ route('admin.library-purchases.reject', $purchase) }}"
                                              class="mt-2 p-3 bg-gray-50 dark:bg-gray-900/40 rounded-lg space-y-2 w-72">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="block text-xs font-medium mb-1">Catatan (opsional)</label>
                                                <textarea name="admin_note" rows="2" class="form-textarea w-full text-sm" maxlength="500"></textarea>
                                            </div>
                                            <button type="submit" class="btn-sm bg-red-500 hover:bg-red-600 text-white w-full">Tolak Permintaan</button>
                                        </form>
                                    </div>
                                @elseif($purchase->status === 'active')
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Aktif</span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ optional($purchase->verified_at)->format('d M Y') }}
                                        @if($purchase->verifier) · oleh {{ $purchase->verifier->name }} @endif
                                    </p>
                                @elseif($purchase->status === 'rejected')
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Ditolak</span>
                                    @if($purchase->admin_note)
                                        <p class="text-xs text-gray-500 mt-1">{{ $purchase->admin_note }}</p>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400">Tidak ada permintaan pada status ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($purchases->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
