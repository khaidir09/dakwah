<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="mb-6 sm:flex sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Bagi Hasil Pustaka</h1>
                <p class="text-sm text-gray-500 mt-1">Pendapatan dari pembelian aktif, dihitung per bulan konfirmasi pembayaran. Periode: <strong>{{ $periodLabel }}</strong>.</p>
            </div>
            <form method="GET" action="{{ route('admin.library-revenue.index') }}" class="flex items-center gap-2 mt-4 sm:mt-0">
                <input type="month" name="bulan" value="{{ $period }}" class="form-input text-sm" aria-label="Pilih bulan" />
                <button type="submit" class="btn-sm bg-violet-500 hover:bg-violet-600 text-white">Tampilkan</button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            @foreach(['gross_amount' => 'Pendapatan Kotor', 'author_amount' => 'Bagian Penulis', 'platform_amount' => 'Bagian Platform'] as $key => $label)
                <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5">
                    <div class="text-xs font-semibold uppercase text-gray-400">{{ $label }}</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">Rp {{ number_format($totals[$key], 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3">Pustaka</th>
                            <th class="px-4 py-3">Penerima</th>
                            <th class="px-4 py-3 text-right">Pembelian</th>
                            <th class="px-4 py-3 text-right">Kotor</th>
                            <th class="px-4 py-3 text-right">Penulis</th>
                            <th class="px-4 py-3 text-right">Platform</th>
                            <th class="px-4 py-3">Transfer</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($rows as $row)
                        <tr class="align-top">
                            <td class="px-4 py-4 font-medium text-gray-800 dark:text-gray-100">{{ $row['library']->title }}</td>
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">{{ $row['payee_name'] ?? '—' }}</td>
                            <td class="px-4 py-4 text-right tabular-nums">{{ $row['purchase_count'] }}</td>
                            <td class="px-4 py-4 text-right tabular-nums">Rp {{ number_format($row['gross_amount'], 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right tabular-nums font-semibold text-gray-800 dark:text-gray-100">
                                Rp {{ number_format($row['author_amount'], 0, ',', '.') }}
                                <div class="text-xs font-normal text-gray-400">{{ $row['share_percent'] }}%</div>
                            </td>
                            <td class="px-4 py-4 text-right tabular-nums">Rp {{ number_format($row['platform_amount'], 0, ',', '.') }}</td>
                            <td class="px-4 py-4">
                                @if($row['payout'])
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Sudah ditransfer</span>
                                    <p class="text-xs text-gray-500 mt-1">{{ $row['payout']->transferred_at->format('d M Y') }}</p>
                                    @if($row['changed_after_payout'])
                                        <p class="text-xs text-amber-600 mt-1">Data berubah setelah transfer</p>
                                    @endif
                                @elseif($row['author_amount'] > 0)
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Belum ditransfer</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('admin.library-revenue.show', [$row['library'], 'bulan' => $period]) }}" class="text-violet-500 hover:text-violet-600">Rincian</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-gray-400">Belum ada pustaka dengan bagi hasil. Isi penerima dan persentase di form edit pustaka berbayar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
