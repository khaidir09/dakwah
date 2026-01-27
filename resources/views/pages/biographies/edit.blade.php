<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Edit Manaqib</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('biographies.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                    Kembali
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <form action="{{ route('biographies.update', $biography->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-sm font-medium mb-2" for="nama">Nama <span class="text-red-500">*</span></label>
                            <input id="nama" class="form-input w-full @error('nama') is-invalid @enderror" type="text" name="nama" value="{{ old('nama', $biography->nama) }}" required/>
                            @error('nama')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="foto">Foto</label>
                            @if ($biography->foto)
                                <div class="my-2">
                                    <img src="{{ Storage::url($biography->foto) }}" alt="Foto Saat Ini" class="w-32 h-32 object-cover rounded">
                                    <p class="text-xs text-gray-500 mt-1">Foto saat ini. Kosongkan jika tidak ingin ganti.</p>
                                </div>
                            @endif
                            <input id="foto" class="form-input w-full @error('foto') is-invalid @enderror" type="file" name="foto" />
                            @error('foto')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                     <div class="grid grid-cols-1 mt-6" x-data="{ sources: {{ Js::from($biography->source ?? [['name' => '', 'url' => '']]) }} }">
                        <label class="block text-sm font-medium mb-2">Sumber Biografi</label>
                        <template x-for="(source, index) in sources" :key="index">
                            <div class="flex gap-4 mb-2 items-start">
                                <div class="flex-1">
                                    <input type="text" :name="`source[${index}][name]`" x-model="source.name" class="form-input w-full" placeholder="Nama Sumber (Contoh: Wikipedia)">
                                </div>
                                <div class="flex-1">
                                    <input type="text" :name="`source[${index}][url]`" x-model="source.url" class="form-input w-full" placeholder="Link URL (Opsional)">
                                </div>
                                <button type="button" @click="sources.splice(index, 1)" class="text-red-500 hover:text-red-700 mt-2" x-show="sources.length > 0">
                                    <span class="sr-only">Hapus</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="sources.push({ name: '', url: '' })" class="text-sm text-blue-500 hover:text-blue-700 font-medium flex items-center mt-1">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Tambah Sumber
                        </button>
                    </div>

                    <div class="grid grid-cols-1 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="maps">Link Google Maps (Embed/URL)</label>
                            <input id="maps" class="form-input w-full @error('maps') is-invalid @enderror" type="text" name="maps" value="{{ old('maps', $biography->maps) }}"/>
                            @error('maps')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="deskripsi">Deskripsi (Riwayat Hidup, Kisah, Keutamaan) <span class="text-red-500">*</span></label>
                            <textarea class="form-input w-full @error('deskripsi') is-invalid @enderror" name="deskripsi" id="deskripsi" cols="30" rows="10" required>{{ old('deskripsi', $biography->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold my-4">Tanggal Wafat</h2>
                    <div class="grid grid-cols-2 gap-6 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="tanggal_wafat_masehi">Wafat Masehi</label>
                            <input id="tanggal_wafat_masehi" class="form-input w-full @error('tanggal_wafat_masehi') is-invalid @enderror" type="date" name="tanggal_wafat_masehi" value="{{ old('tanggal_wafat_masehi', $biography->tanggal_wafat_masehi) }}"/>
                            @error('tanggal_wafat_masehi')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" for="tanggal_wafat_hijriah">Wafat Hijriah (Teks)</label>
                            <input id="tanggal_wafat_hijriah" class="form-input w-full @error('tanggal_wafat_hijriah') is-invalid @enderror" type="text" name="tanggal_wafat_hijriah" value="{{ old('tanggal_wafat_hijriah', $biography->tanggal_wafat_hijriah) }}" placeholder="Contoh: 14 Ramadhan 1445 H"/>
                            @error('tanggal_wafat_hijriah')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                    <x-button>
                        Simpan Perubahan
                    </x-button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
