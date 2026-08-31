<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Pengaturan Poster AI</h1>
            <p class="text-sm text-gray-500 mt-1">Pengurus majelis dan kontributor dapat membuat poster acara dengan AI. Kuota dihitung per pengguna dan disetel ulang setiap awal bulan. Perubahan berlaku untuk pembuatan berikutnya, tidak retroaktif.</p>
        </div>

        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('message') }}</div>
        @endif

        <form action="{{ route('admin.poster-settings.update') }}" method="POST" class="max-w-xl">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
                <div class="p-6 space-y-6">

                    <div>
                        <label for="monthly_quota" class="block text-sm font-medium text-gray-800 dark:text-gray-100 mb-1">Kuota per Pengguna per Bulan</label>
                        <input
                            type="number"
                            id="monthly_quota"
                            name="monthly_quota"
                            value="{{ old('monthly_quota', $setting->monthly_quota) }}"
                            min="0" max="1000"
                            class="form-input w-full @error('monthly_quota') border-red-500 @enderror"
                            required
                        />
                        <p class="text-xs text-gray-400 mt-1">Jumlah poster yang berhasil dibuat per pengguna dalam satu bulan. Default 5. Pembuatan yang gagal tidak dihitung.</p>
                        @error('monthly_quota')
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
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100 ml-2">Fitur poster AI aktif</span>
                        </label>
                        <p class="text-xs text-gray-400 mt-1">Jika dinonaktifkan, tombol pembuatan poster hilang dari form acara dan permintaan baru ditolak. Poster yang sudah terpasang pada acara tidak terpengaruh.</p>
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
