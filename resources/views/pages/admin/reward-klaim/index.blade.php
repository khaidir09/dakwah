<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Klaim Reward Kontributor</h1>
            <p class="text-sm text-gray-500 mt-1">Proses pengajuan klaim reward: tandai sudah ditransfer atau tolak dengan alasan.</p>
        </div>

        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('message') }}</div>
        @endif

        {{-- Filter status --}}
        <div class="flex flex-wrap gap-2 mb-5">
            @foreach(['pending' => 'Menunggu', 'paid' => 'Sudah Ditransfer', 'rejected' => 'Ditolak'] as $key => $label)
                <a href="{{ route('admin.reward-klaim.index', ['status' => $key]) }}"
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
                            <th class="px-4 py-3">Kontributor</th>
                            <th class="px-4 py-3">XP saat klaim</th>
                            <th class="px-4 py-3">Nominal</th>
                            <th class="px-4 py-3">E-Wallet</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Aksi / Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($claims as $claim)
                        <tr class="align-top">
                            <td class="px-4 py-4 font-medium text-gray-800 dark:text-gray-100">
                                {{ $claim->user?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-gray-500">{{ number_format($claim->xp_at_claim) }} XP</td>
                            <td class="px-4 py-4 font-semibold text-gray-800 dark:text-gray-100">Rp {{ number_format($claim->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">
                                <span class="font-medium">{{ $claim->ewallet_type }}</span><br>
                                {{ $claim->ewallet_number }}<br>
                                <span class="text-xs text-gray-400">a.n. {{ $claim->ewallet_holder_name }}</span>
                            </td>
                            <td class="px-4 py-4 text-gray-500">{{ $claim->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-4">
                                @if($claim->status === 'pending')
                                    <div x-data="{ panel: null }" class="space-y-2">
                                        <div class="flex gap-2">
                                            <button type="button" @click="panel = (panel === 'paid' ? null : 'paid')"
                                                class="btn-sm bg-emerald-500 hover:bg-emerald-600 text-white">Tandai Paid</button>
                                            <button type="button" @click="panel = (panel === 'reject' ? null : 'reject')"
                                                class="btn-sm bg-red-500 hover:bg-red-600 text-white">Tolak</button>
                                        </div>

                                        {{-- Form Tandai Paid --}}
                                        <form x-show="panel === 'paid'" x-cloak method="POST" enctype="multipart/form-data"
                                              action="{{ route('admin.reward-klaim.paid', $claim) }}"
                                              class="mt-2 p-3 bg-gray-50 dark:bg-gray-900/40 rounded-lg space-y-2 w-72">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="block text-xs font-medium mb-1">Tanggal Transfer</label>
                                                <input type="date" name="transferred_at" value="{{ now()->toDateString() }}" class="form-input w-full text-sm" required />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium mb-1">Bukti Transfer</label>
                                                <input type="file" name="transfer_proof" accept="image/*" class="form-input w-full text-xs" required />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium mb-1">Catatan (opsional)</label>
                                                <textarea name="admin_note" rows="2" class="form-textarea w-full text-sm" maxlength="500"></textarea>
                                            </div>
                                            <button type="submit" class="btn-sm bg-emerald-500 hover:bg-emerald-600 text-white w-full">Simpan</button>
                                        </form>

                                        {{-- Form Tolak --}}
                                        <form x-show="panel === 'reject'" x-cloak method="POST"
                                              action="{{ route('admin.reward-klaim.reject', $claim) }}"
                                              class="mt-2 p-3 bg-gray-50 dark:bg-gray-900/40 rounded-lg space-y-2 w-72">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="block text-xs font-medium mb-1">Alasan Penolakan</label>
                                                <textarea name="rejection_reason" rows="2" class="form-textarea w-full text-sm" maxlength="500" required></textarea>
                                            </div>
                                            <button type="submit" class="btn-sm bg-red-500 hover:bg-red-600 text-white w-full">Tolak Klaim</button>
                                        </form>
                                    </div>
                                @elseif($claim->status === 'paid')
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Sudah ditransfer</span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ optional($claim->transferred_at)->format('d M Y') }}
                                        @if($claim->processor) · oleh {{ $claim->processor->name }} @endif
                                    </p>
                                    @if($claim->admin_note)
                                        <p class="text-xs text-gray-400 mt-1">{{ $claim->admin_note }}</p>
                                    @endif
                                    @if($claim->transfer_proof_path)
                                        <a href="{{ route('reward-klaim.bukti', $claim) }}" target="_blank" class="text-xs text-violet-600 hover:underline mt-1 inline-block">Lihat bukti</a>
                                    @endif
                                @elseif($claim->status === 'rejected')
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Ditolak</span>
                                    @if($claim->rejection_reason)
                                        <p class="text-xs text-gray-500 mt-1">{{ $claim->rejection_reason }}</p>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-gray-400">Tidak ada klaim pada status ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($claims->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $claims->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
