<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Pengaturan Reward Kontributor</h1>
            <p class="text-sm text-gray-500 mt-1">Reward dapat diklaim kontributor yang mencapai threshold XP. Perubahan berlaku untuk klaim berikutnya, tidak retroaktif.</p>
        </div>

        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('message') }}</div>
        @endif

        <form action="{{ route('admin.reward-settings.update') }}" method="POST" class="max-w-xl">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
                <div class="p-6 space-y-6">

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-800 dark:text-gray-100 mb-1">Nominal Reward (Rp)</label>
                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            value="{{ old('amount', $setting->amount) }}"
                            min="0" max="100000000"
                            class="form-input w-full @error('amount') border-red-500 @enderror"
                            required
                        />
                        <p class="text-xs text-gray-400 mt-1">Besaran rupiah yang ditransfer ke kontributor. Default Rp 50.000.</p>
                        @error('amount')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="min_xp" class="block text-sm font-medium text-gray-800 dark:text-gray-100 mb-1">Threshold XP Minimal</label>
                        <input
                            type="number"
                            id="min_xp"
                            name="min_xp"
                            value="{{ old('min_xp', $setting->min_xp) }}"
                            min="1"
                            class="form-input w-full @error('min_xp') border-red-500 @enderror"
                            required
                        />
                        <p class="text-xs text-gray-400 mt-1">XP minimum agar kontributor berhak klaim. Default 501 (selaras gelar Khadam Syaikhuna).</p>
                        @error('min_xp')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center">
                            {{-- Hidden input menjamin is_active selalu terkirim meski checkbox tidak dicentang --}}
                            <input type="hidden" name="is_active" value="0" />
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                class="form-checkbox"
                                @checked(old('is_active', $setting->is_active))
                            />
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100 ml-2">Program reward aktif</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-1">Jika dinonaktifkan, kontributor tidak dapat mengajukan klaim baru. Klaim yang sudah masuk tetap dapat diproses.</p>
                        @error('is_active')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="btn bg-primary-500 hover:bg-primary-600 text-white">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

    </div>
</x-app-layout>
