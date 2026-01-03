<x-dashboard-layout>
    <div class="grow">

        <div class="p-6 space-y-6">
            <!-- Page header -->
            <div class="sm:flex sm:justify-between sm:items-center mb-8">
                <div class="mb-4 sm:mb-0">
                    <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold">Tambah Acara Majelis</h2>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('kelola-acara-majelis') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                        Kembali
                    </a>
                </div>
            </div>

            <div>
                <form action="{{ route('kelola-acara-majelis.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="assembly_id" value="{{ Auth::user()->assembly->id }}">
                    <div class="text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20 border-t border-b border-gray-100 dark:border-gray-700/60 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                        <div class="grid md:grid-cols-2 gap-6">

                            <div>
                                <label class="block text-sm font-medium mb-2" for="name">Nama Acara <span class="text-red-500">*</span></label>
                                <input id="name" class="form-input w-full @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Maulid Akbar"/>
                                @error('name')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="category">Kategori <span class="text-red-500">*</span></label>
                                <select id="category" class="form-select w-full @error('category') is-invalid @enderror" name="category" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Taklim">Taklim</option>
                                    <option value="Maulid">Maulid</option>
                                    <option value="Dzikir">Dzikir</option>
                                    <option value="Haul">Haul</option>
                                    <option value="Tabligh Akbar">Tabligh Akbar</option>
                                </select>
                                @error('category')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="date">Tanggal & Waktu <span class="text-red-500">*</span></label>
                                <input id="date" class="form-input w-full @error('date') is-invalid @enderror" type="datetime-local" name="date" value="{{ old('date') }}" required/>
                                @error('date')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="access">Akses <span class="text-red-500">*</span></label>
                                <select id="access" name="access" class="form-select w-full @error('access') is-invalid @enderror" required>
                                    <option value="Umum" {{ old('access') == 'Umum' ? 'selected' : '' }}>Umum</option>
                                    <option value="Khusus" {{ old('access') == 'Khusus' ? 'selected' : '' }}>Khusus</option>
                                </select>
                                @error('access')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="image">Poster (Gambar)</label>
                                <input id="image" class="form-input w-full @error('image') is-invalid @enderror" type="file" name="image" accept="image/*" />
                                @error('image')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                        <x-button>
                            Simpan
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-dashboard-layout>
