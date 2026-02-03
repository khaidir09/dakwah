<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Edit Amalan</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('wirid.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                    Kembali
                </a>
            </div>
        </div>

        <div>
            <form action="{{ route('wirid.update', $wirid->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">
                        @if (session('status'))
                            <div class="px-4 py-2 rounded-lg text-sm bg-green-500 text-white relative" role="alert">
                                <span class="block sm:inline">{{ session('status') }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2">Kategori <span class="text-red-500">*</span></label>
                            <div class="flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="kategori" value="wirid" class="form-radio text-emerald-500" {{ old('kategori', $wirid->kategori ?? 'wirid') == 'wirid' ? 'checked' : '' }}>
                                    <span class="ml-2">Wirid (Amalan)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="kategori" value="doa" class="form-radio text-emerald-500" {{ old('kategori', $wirid->kategori ?? 'wirid') == 'doa' ? 'checked' : '' }}>
                                    <span class="ml-2">Doa</span>
                                </label>
                            </div>
                            @error('kategori')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2" for="nama">Nama Amalan <span class="text-red-500">*</span></label>
                            <input id="nama" class="form-input w-full @error('nama') is-invalid @enderror" type="text" name="nama" value="{{ old('nama', $wirid->nama) }}" required placeholder="Contoh: Istighfar"/>
                            @error('nama')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2" for="arab">Teks Arab <span class="text-red-500">*</span></label>
                            <textarea id="arab" dir="rtl" class="form-input w-full text-right text-xl font-arabic @error('arab') is-invalid @enderror" name="arab" rows="4" required placeholder="أَسْتَغْفِرُ اللَّهَ الْعَظِيمَ الَّذِي لَا إِلَهَ إِلَّا هُوَ الْحَيَّ الْقَيُّومَ وَأَتُوبُ إِلَيْهِ">{{ old('arab', $wirid->arab) }}</textarea>
                            @error('arab')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2" for="arti">Arti</label>
                            <textarea id="arti" class="form-input w-full @error('arti') is-invalid @enderror" name="arti" rows="3" placeholder="Masukkan arti atau terjemahan">{{ old('arti', $wirid->arti) }}</textarea>
                            @error('arti')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="jumlah">Jumlah Dibaca <span class="text-red-500">*</span></label>
                            <input id="jumlah" class="form-input w-full @error('jumlah') is-invalid @enderror" type="number" name="jumlah" value="{{ old('jumlah', $wirid->jumlah) }}" min="1" required/>
                            @error('jumlah')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="waktu">Waktu Dibaca</label>
                            <input id="waktu" class="form-input w-full @error('waktu') is-invalid @enderror" type="text" name="waktu" value="{{ old('waktu', $wirid->waktu) }}" placeholder="Contoh: Pagi & Petang"/>
                            @error('waktu')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2" for="deskripsi">Deskripsi</label>
                            <textarea class="form-input w-full @error('deskripsi') is-invalid @enderror" name="deskripsi" id="deskripsi" rows="3" placeholder="Penjelasan tambahan tentang amalan ini">{{ old('deskripsi', $wirid->deskripsi) }}</textarea>
                            @error('deskripsi')
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
