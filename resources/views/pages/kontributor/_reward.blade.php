@php
    $rupiah = 'Rp ' . number_format($rewardSetting->amount, 0, ',', '.');
@endphp

@if($latestClaim?->status === 'paid')
    {{-- Reward sudah diterima (sekali seumur hidup) --}}
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-6">
        <div class="flex items-start gap-4">
            <div class="text-3xl shrink-0">🎉</div>
            <div>
                <h3 class="font-semibold text-emerald-800 dark:text-emerald-300">Reward Sudah Diterima</h3>
                <p class="text-sm text-emerald-700 dark:text-emerald-400 mt-1">
                    Reward {{ $rupiah }} telah ditransfer ke akun e-wallet Anda.
                    @if($latestClaim->transferred_at)
                        <span class="block mt-1">Tanggal transfer: <span class="font-medium">{{ $latestClaim->transferred_at->translatedFormat('d F Y') }}</span></span>
                    @endif
                </p>
                @if($latestClaim->transfer_proof_path)
                    <a href="{{ route('reward-klaim.bukti', $latestClaim) }}" target="_blank"
                       class="inline-block mt-2 text-sm font-medium text-emerald-700 dark:text-emerald-400 underline">Lihat bukti transfer</a>
                @endif
                <p class="text-xs text-emerald-600/80 dark:text-emerald-500 mt-2">Jazakallahu khairan atas kontribusi Anda untuk Syaikhuna.</p>
            </div>
        </div>
    </div>
@elseif($latestClaim?->status === 'pending')
    {{-- Klaim sedang diproses --}}
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-6">
        <div class="flex items-start gap-4">
            <div class="text-3xl shrink-0">⏳</div>
            <div>
                <h3 class="font-semibold text-amber-800 dark:text-amber-300">Klaim Reward Sedang Diproses</h3>
                <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                    Pengajuan klaim Anda sebesar {{ $rupiah }} sedang menunggu diproses admin.
                </p>
                <p class="text-xs text-amber-600/80 dark:text-amber-500 mt-2">
                    Tujuan: {{ $latestClaim->ewallet_type }} — {{ $latestClaim->ewallet_number }}
                    (a.n. {{ $latestClaim->ewallet_holder_name }})
                </p>
            </div>
        </div>
    </div>
@else
    @if($latestClaim?->status === 'rejected')
        {{-- Alasan penolakan klaim sebelumnya --}}
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
            <p class="text-sm text-red-800 dark:text-red-300">
                <span class="font-semibold">Klaim sebelumnya ditolak.</span> Alasan: {{ $latestClaim->rejection_reason }}
            </p>
            <p class="text-xs text-red-600/80 dark:text-red-400 mt-1">Anda dapat memperbaiki data dan mengajukan klaim kembali di bawah ini.</p>
        </div>
    @endif

    @if($rewardEligible)
        {{-- Form pengajuan klaim --}}
        <div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }" class="bg-gradient-to-r from-amber-400 to-yellow-500 rounded-xl p-6 text-white">
            <div class="sm:flex sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold flex items-center gap-2">⭐ Klaim Reward {{ $rupiah }}</h3>
                    <p class="text-sm opacity-90 mt-1">
                        Selamat! Anda telah mencapai {{ number_format($rewardSetting->min_xp) }} XP dan berhak atas reward kontributor.
                    </p>
                </div>
                <button type="button" @click="open = !open"
                    class="mt-3 sm:mt-0 shrink-0 btn bg-white text-amber-600 hover:bg-amber-50 font-semibold px-5 py-2 rounded-lg">
                    Klaim Reward
                </button>
            </div>

            <div x-show="open" x-cloak class="mt-5 bg-white/95 dark:bg-gray-800 rounded-lg p-5 text-gray-800 dark:text-gray-100">
                <form action="{{ route('kontributor.reward.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <p class="text-sm text-gray-500">Masukkan data e-wallet tujuan transfer. Pastikan data benar agar transfer tidak gagal.</p>

                    <div>
                        <label for="ewallet_type" class="block text-sm font-medium mb-1">Jenis E-Wallet</label>
                        <select id="ewallet_type" name="ewallet_type" class="form-select w-full @error('ewallet_type') border-red-500 @enderror" required>
                            @foreach(['Dana', 'GoPay', 'OVO', 'ShopeePay'] as $type)
                                <option value="{{ $type }}" @selected(old('ewallet_type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('ewallet_type') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="ewallet_number" class="block text-sm font-medium mb-1">Nomor E-Wallet</label>
                        <input type="text" id="ewallet_number" name="ewallet_number" value="{{ old('ewallet_number') }}"
                            class="form-input w-full @error('ewallet_number') border-red-500 @enderror"
                            placeholder="08xxxxxxxxxx" maxlength="30" required />
                        @error('ewallet_number') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label for="ewallet_holder_name" class="block text-sm font-medium mb-1">Nama Pemilik Akun</label>
                        <input type="text" id="ewallet_holder_name" name="ewallet_holder_name" value="{{ old('ewallet_holder_name', $user->name) }}"
                            class="form-input w-full @error('ewallet_holder_name') border-red-500 @enderror"
                            maxlength="100" required />
                        @error('ewallet_holder_name') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn bg-amber-500 hover:bg-amber-600 text-white font-semibold px-6">
                            Ajukan Klaim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endif
