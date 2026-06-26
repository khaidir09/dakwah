<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
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
                <label class="block text-sm font-medium mb-2">Nama Acara <span class="text-red-500">*</span></label>
                <input class="form-input w-full" type="text" name="name" value="{{ old('name', $acara?->name) }}" required/>
                @error('name')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Kategori <span class="text-red-500">*</span></label>
                <select class="form-select w-full" name="category" required>
                    <option value="">Pilih Kategori</option>
                    @foreach(['Taklim','Maulid','Dzikir','Haul','Tabligh Akbar','Peringatan Hari Besar Islam'] as $k)
                        <option value="{{ $k }}" {{ old('category', $acara?->category) == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
                @error('category')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Majelis <span class="text-red-500">*</span></label>
                <select class="form-select w-full" name="assembly_id" required>
                    <option value="">Pilih Majelis</option>
                    @foreach($majelisList as $m)
                        <option value="{{ $m->id }}" {{ old('assembly_id', $acara?->assembly_id) == $m->id ? 'selected' : '' }}>{{ $m->nama_majelis }}</option>
                    @endforeach
                </select>
                @error('assembly_id')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Tanggal & Waktu <span class="text-red-500">*</span></label>
                <input class="form-input w-full" type="datetime-local" name="date" value="{{ old('date', $acara?->date ? \Carbon\Carbon::parse($acara->date)->format('Y-m-d\TH:i') : '') }}" required/>
                @error('date')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Akses <span class="text-red-500">*</span></label>
                <select class="form-select w-full" name="access" required>
                    <option value="Umum" {{ old('access', $acara?->access) == 'Umum' ? 'selected' : '' }}>Umum</option>
                    <option value="Khusus" {{ old('access', $acara?->access) == 'Khusus' ? 'selected' : '' }}>Khusus</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Poster (Gambar)</label>
                @if($acara?->image)
                    <img src="{{ Storage::url($acara->image) }}" class="w-24 h-16 object-cover rounded mb-2">
                @endif
                <input class="form-input w-full" type="file" name="image" accept="image/*"/>
                @error('image')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

        </div>
    </div>

    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 sm:rounded-bl-md sm:rounded-br-md shadow">
        <x-button>Kirim Kontribusi</x-button>
    </div>
</form>
