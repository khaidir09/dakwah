<div class="p-6 bg-white border-b border-gray-200">
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">

        @if($isAdmin)
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Pilih Majelis / Masjid</label>
            <select wire:model="assembly_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                <option value="">-- Pilih Majelis --</option>
                @foreach($assemblies as $assembly)
                    <option value="{{ $assembly->id }}">{{ $assembly->nama_majelis }} ({{ $assembly->city?->name }})</option>
                @endforeach
            </select>
            @error('assembly_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        @else
            <!-- Hidden input for assembly_id if user is regular user -->
            @if(!$assembly_id)
                <div class="md:col-span-2 bg-yellow-50 p-4 rounded text-yellow-800">
                    Anda belum memiliki Majelis yang terdaftar. Silahkan daftarkan majelis Anda terlebih dahulu.
                </div>
            @endif
        @endif

        <!-- Header Information -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Tahun Hijriah</label>
            <input type="number" wire:model="hijri_year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
            @error('hijri_year') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Mulai (1 Ramadhan)</label>
            <input type="date" wire:model.live="gregorian_start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
            @error('gregorian_start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Judul (Opsional)</label>
            <input type="text" wire:model="title" placeholder="Contoh: Kuliah Shubuh Masjid Agung" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Deskripsi (Opsional)</label>
            <textarea wire:model="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50"></textarea>
        </div>

        <div class="md:col-span-2">
            <label class="inline-flex items-center">
                <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-300 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                <span class="ml-2">Aktifkan Jadwal Ini</span>
            </label>
        </div>
    </div>

    <hr class="my-6">

    <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Penceramah Harian</h3>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Hari</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Tanggal</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Waktu</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penceramah (Guru)</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atau Nama Custom</th>
                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Topik / Judul</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($days as $index => $day)
                    <tr wire:key="day-{{ $index }}">
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                            Ke-{{ $day['day'] }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                            @if($gregorian_start_date)
                                {{ \Carbon\Carbon::parse($gregorian_start_date)->addDays($day['day'] - 1)->locale('id')->isoFormat('ddd, D MMM') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <input type="time" wire:model="days.{{ $index }}.time" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 text-sm">
                        </td>
                        <td class="px-3 py-2">
                            <select wire:model="days.{{ $index }}.teacher_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 text-sm">
                                <option value="">-- Pilih Guru --</option>
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-3 py-2">
                             <input type="text" wire:model="days.{{ $index }}.custom_speaker_name" placeholder="Nama Penceramah Luar" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 text-sm">
                        </td>
                        <td class="px-3 py-2">
                             <input type="text" wire:model="days.{{ $index }}.title" placeholder="Topik Ceramah" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 text-sm">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6 flex justify-end">
        <button wire:click="save" class="bg-emerald-600 text-white px-4 py-2 rounded-md hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
            Simpan Jadwal
        </button>
    </div>
</div>
