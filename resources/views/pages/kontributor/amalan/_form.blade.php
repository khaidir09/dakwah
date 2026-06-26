<form action="{{ $action }}" method="POST">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
        <div class="grid md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-medium mb-2">Nama Amalan <span class="text-red-500">*</span></label>
                <input class="form-input w-full" type="text" name="nama" value="{{ old('nama', $amalan?->nama) }}" required/>
                @error('nama')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Kategori <span class="text-red-500">*</span></label>
                <select class="form-select w-full" name="kategori" required>
                    <option value="wirid" {{ old('kategori', $amalan?->kategori) == 'wirid' ? 'selected' : '' }}>Wirid</option>
                    <option value="doa" {{ old('kategori', $amalan?->kategori) == 'doa' ? 'selected' : '' }}>Doa</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Jumlah Bacaan <span class="text-red-500">*</span></label>
                <input class="form-input w-full" type="number" name="jumlah" value="{{ old('jumlah', $amalan?->jumlah ?? 1) }}" min="1" required/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Waktu Baca</label>
                <input class="form-input w-full" type="text" name="waktu" value="{{ old('waktu', $amalan?->waktu) }}" placeholder="Misal: Setelah Subuh"/>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium mb-2">Lafadz Arab <span class="text-red-500">*</span></label>
                <textarea class="form-textarea w-full text-right text-xl leading-loose" name="arab" rows="4" dir="rtl" required>{{ old('arab', $amalan?->arab) }}</textarea>
                @error('arab')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium mb-2">Arti / Terjemahan</label>
                <textarea class="form-textarea w-full" name="arti" rows="3">{{ old('arti', $amalan?->arti) }}</textarea>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium mb-2">Deskripsi <span class="text-red-500">*</span></label>
                <textarea class="form-textarea w-full" name="deskripsi" rows="4" required>{{ old('deskripsi', $amalan?->deskripsi) }}</textarea>
                @error('deskripsi')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 sm:rounded-bl-md sm:rounded-br-md shadow">
        <x-button>Kirim Kontribusi</x-button>
    </div>
</form>
