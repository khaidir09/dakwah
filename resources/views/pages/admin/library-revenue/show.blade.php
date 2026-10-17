<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">

        <div class="mb-4">
            <a href="{{ route('admin.library-revenue.index', ['bulan' => $period]) }}" class="text-sm font-medium text-violet-500 hover:text-violet-600">&larr; Kembali ke ringkasan</a>
        </div>

        <div class="mb-6 sm:flex sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">{{ $library->title }}</h1>
                <p class="text-sm text-gray-500 mt-1">Bagi hasil periode <strong>{{ $periodLabel }}</strong> · Penerima: {{ $summary['payee_name'] ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2 mt-4 sm:mt-0">
                <form method="GET" action="{{ route('admin.library-revenue.show', $library) }}" class="flex items-center gap-2">
                    <input type="month" name="bulan" value="{{ $period }}" class="form-input text-sm" aria-label="Pilih bulan" />
                    <button type="submit" class="btn-sm bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 text-gray-800 dark:text-gray-300">Tampilkan</button>
                </form>
                <a href="{{ route('admin.library-revenue.report', [$library, 'bulan' => $period]) }}" target="_blank" class="btn-sm bg-violet-500 hover:bg-violet-600 text-white">Laporan untuk Penulis</a>
            </div>
        </div>

        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('message') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5">
                <div class="text-xs font-semibold uppercase text-gray-400">Pembelian</div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ $summary['purchase_count'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5">
                <div class="text-xs font-semibold uppercase text-gray-400">Kotor</div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">Rp {{ number_format($summary['gross_amount'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5">
                <div class="text-xs font-semibold uppercase text-gray-400">Penulis ({{ $summary['share_percent'] }}%)</div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">Rp {{ number_format($summary['author_amount'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5">
                <div class="text-xs font-semibold uppercase text-gray-400">Platform</div>
                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">Rp {{ number_format($summary['platform_amount'], 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Status / pencatatan transfer --}}
        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5 mb-6">
            <h2 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Transfer ke Penulis</h2>
            @if($summary['payout'])
                @php($payout = $summary['payout'])
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Sudah ditransfer <strong>Rp {{ number_format($payout->author_amount, 0, ',', '.') }}</strong>
                    kepada {{ $payout->payee_name }} pada {{ $payout->transferred_at->format('d M Y') }}
                    @if($payout->processor) · dicatat oleh {{ $payout->processor->name }} @endif
                </p>
                @if($payout->admin_note)
                    <p class="text-xs text-gray-500 mt-1">{{ $payout->admin_note }}</p>
                @endif
                @if($payout->transfer_proof_path)
                    <a href="{{ route('admin.library-revenue.proof', $payout) }}" target="_blank" class="text-xs text-violet-600 hover:underline mt-1 inline-block">Lihat bukti transfer</a>
                @endif
                @if($summary['changed_after_payout'])
                    <p class="mt-3 p-3 text-sm rounded-lg bg-amber-50 text-amber-800 border border-amber-200">
                        Data pembelian periode ini berubah setelah transfer dicatat (mis. pembelian ditolak/refund). Angka di atas adalah angka saat transfer; selisihnya perlu diselesaikan manual.
                    </p>
                @endif
            @elseif($library->author_share_percent === null || blank($library->payee_name))
                <p class="text-sm text-gray-500">Pustaka ini belum punya penerima dan persentase bagi hasil. Isi di <a href="{{ route('libraries.edit', $library) }}" class="text-violet-600 hover:underline">form edit pustaka</a>.</p>
            @elseif(! $isClosed)
                <p class="text-sm text-gray-500">Periode ini belum berakhir. Transfer bisa dicatat mulai bulan berikutnya.</p>
            @else
                <form method="POST" action="{{ route('admin.library-revenue.mark-paid', $library) }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 gap-4 max-w-2xl">
                    @csrf
                    <input type="hidden" name="bulan" value="{{ $period }}" />
                    <div>
                        <label class="block text-xs font-medium mb-1" for="transferred_at">Tanggal Transfer</label>
                        <input id="transferred_at" type="date" name="transferred_at" value="{{ old('transferred_at', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" class="form-input w-full text-sm" required />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1" for="transfer_proof">Bukti Transfer</label>
                        <input id="transfer_proof" type="file" name="transfer_proof" accept="image/*" class="form-input w-full text-xs" required />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium mb-1" for="admin_note">Catatan (opsional)</label>
                        <textarea id="admin_note" name="admin_note" rows="2" class="form-textarea w-full text-sm" maxlength="500">{{ old('admin_note') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-sm bg-emerald-500 hover:bg-emerald-600 text-white">Catat Transfer Rp {{ number_format($summary['author_amount'], 0, ',', '.') }}</button>
                        <p class="text-xs text-gray-500 mt-2">Angka periode ini akan dibekukan setelah transfer dicatat.</p>
                    </div>
                </form>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="font-semibold text-gray-800 dark:text-gray-100">Rincian Pembelian Aktif</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3">Dikonfirmasi</th>
                            <th class="px-4 py-3">Pembeli</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($purchases as $purchase)
                            <tr>
                                <td class="px-4 py-3 text-gray-500">{{ $purchase->verified_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-gray-800 dark:text-gray-100">{{ $purchase->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">Rp {{ number_format($purchase->price, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-400">Tidak ada pembelian aktif pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
