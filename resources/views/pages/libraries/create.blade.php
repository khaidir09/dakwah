<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Tambah Pustaka</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('libraries.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
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
            <form action="{{ route('libraries.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-sm font-medium mb-2" for="title">Judul <span class="text-red-500">*</span></label>
                            <input id="title" class="form-input w-full @error('title') is-invalid @enderror" type="text" name="title" value="{{ old('title') }}" required/>
                            @error('title')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                         <div>
                            <label class="block text-sm font-medium mb-2" for="category">Kategori <span class="text-red-500">*</span></label>
                            <select id="category" class="form-select w-full @error('category') is-invalid @enderror" name="category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Fikih" {{ old('category') == 'Fikih' ? 'selected' : '' }}>Fikih</option>
                                <option value="Tasawuf" {{ old('category') == 'Tasawuf' ? 'selected' : '' }}>Tasawuf</option>
                                <option value="Sejarah" {{ old('category') == 'Sejarah' ? 'selected' : '' }}>Sejarah</option>
                                <option value="Hadits" {{ old('category') == 'Hadits' ? 'selected' : '' }}>Hadits</option>
                                <option value="Tafsir" {{ old('category') == 'Tafsir' ? 'selected' : '' }}>Tafsir</option>
                                <option value="Amalan" {{ old('category') == 'Amalan' ? 'selected' : '' }}>Amalan (Ratib, Maulid, dll)</option>
                                <option value="Lainnya" {{ old('category') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('category')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="price_type">Tipe <span class="text-red-500">*</span></label>
                            <select id="price_type" class="form-select w-full @error('price_type') is-invalid @enderror" name="price_type" required>
                                <option value="free" {{ old('price_type') == 'free' ? 'selected' : '' }}>Gratis</option>
                                <option value="paid" {{ old('price_type') == 'paid' ? 'selected' : '' }}>Berbayar</option>
                            </select>
                            @error('price_type')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                         <div>
                            <label class="block text-sm font-medium mb-2" for="file">File PDF <span class="text-red-500">*</span></label>
                            <input id="file" class="form-input w-full @error('file') is-invalid @enderror" type="file" name="file" accept="application/pdf" required />
                            <div class="text-xs text-gray-500 mt-1">Format PDF, Maks 10MB</div>
                            @error('file')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="cover_image">Cover Buku (Opsional)</label>
                            <input id="cover_image" class="form-input w-full @error('cover_image') is-invalid @enderror" type="file" name="cover_image" accept="image/*" />
                             <div class="text-xs text-gray-500 mt-1">Format Gambar (JPG, PNG), Maks 2MB</div>
                            @error('cover_image')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="description">Deskripsi <span class="text-red-500">*</span></label>
                            <textarea class="form-input w-full @error('description') is-invalid @enderror" name="description" id="description" cols="30" rows="5" required>{{ old('description') }}</textarea>
                            @error('description')
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
</x-app-layout>
