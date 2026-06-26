<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Pengaturan XP Kontribusi</h1>
            <p class="text-sm text-gray-500 mt-1">Nilai XP berlaku untuk approval berikutnya, tidak retroaktif.</p>
        </div>

        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('message') }}</div>
        @endif

        <form action="{{ route('admin.xp-settings.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-6 py-4">Jenis Kontribusi</th>
                            <th class="px-6 py-4 w-48">Nilai XP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($settings as $type => $setting)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-100">
                                {{ $setting->label }}
                                <span class="ml-2 text-xs text-gray-400 font-normal">({{ $type }})</span>
                            </td>
                            <td class="px-6 py-4">
                                <input
                                    type="number"
                                    name="xp[{{ $type }}]"
                                    value="{{ old('xp.'.$type, $setting->points) }}"
                                    min="1" max="1000"
                                    class="form-input w-32 @error('xp.'.$type) border-red-500 @enderror"
                                    required
                                />
                                @error('xp.'.$type)
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="btn bg-primary-500 hover:bg-primary-600 text-white">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

    </div>
</x-app-layout>
