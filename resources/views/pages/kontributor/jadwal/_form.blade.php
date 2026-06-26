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
                <label class="block text-sm font-medium mb-2">Nama Jadwal <span class="text-red-500">*</span></label>
                <input class="form-input w-full" type="text" name="nama_jadwal" value="{{ old('nama_jadwal', $jadwal?->nama_jadwal) }}" required/>
                @error('nama_jadwal')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Majelis <span class="text-red-500">*</span></label>
                <select class="form-select w-full" name="assembly_id" required>
                    <option value="">Pilih Majelis</option>
                    @foreach($majelisList as $m)
                        <option value="{{ $m->id }}" {{ old('assembly_id', $jadwal?->assembly_id) == $m->id ? 'selected' : '' }}>{{ $m->nama_majelis }}</option>
                    @endforeach
                </select>
                @error('assembly_id')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Guru <span class="text-red-500">*</span></label>
                <select class="form-select w-full" name="teacher_id" required>
                    <option value="">Pilih Guru</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ old('teacher_id', $jadwal?->teacher_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
                @error('teacher_id')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Hari <span class="text-red-500">*</span></label>
                <select class="form-select w-full" name="hari" required>
                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad'] as $h)
                        <option value="{{ $h }}" {{ old('hari', $jadwal?->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                    @endforeach
                </select>
                @error('hari')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Waktu <span class="text-red-500">*</span></label>
                <input class="form-input w-full" type="time" name="waktu" value="{{ old('waktu', $jadwal?->waktu ? \Carbon\Carbon::parse($jadwal->waktu)->format('H:i') : '') }}" required/>
                @error('waktu')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Akses</label>
                <select class="form-select w-full" name="access">
                    @foreach(['Umum','Ikhwan','Akhwat'] as $a)
                        <option value="{{ $a }}" {{ old('access', $jadwal?->access) == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-2">
                <label class="block text-sm font-medium mb-2">Deskripsi</label>
                <textarea class="form-textarea w-full" name="deskripsi" rows="4">{{ old('deskripsi', $jadwal?->deskripsi) }}</textarea>
            </div>

        </div>
    </div>

    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 sm:rounded-bl-md sm:rounded-br-md shadow">
        <x-button>Kirim Kontribusi</x-button>
    </div>
</form>
