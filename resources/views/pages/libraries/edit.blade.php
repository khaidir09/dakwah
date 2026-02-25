<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Edit Pustaka</h1>
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
            <form action="{{ route('libraries.update', $library) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-sm font-medium mb-2" for="title">Judul <span class="text-red-500">*</span></label>
                            <input id="title" class="form-input w-full @error('title') is-invalid @enderror" type="text" name="title" value="{{ old('title', $library->title) }}" required/>
                            @error('title')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                         <div>
                            <label class="block text-sm font-medium mb-2" for="category">Kategori <span class="text-red-500">*</span></label>
                            <select id="category" class="form-select w-full @error('category') is-invalid @enderror" name="category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Fikih" {{ old('category', $library->category) == 'Fikih' ? 'selected' : '' }}>Fikih</option>
                                <option value="Tasawuf" {{ old('category', $library->category) == 'Tasawuf' ? 'selected' : '' }}>Tasawuf</option>
                                <option value="Sejarah" {{ old('category', $library->category) == 'Sejarah' ? 'selected' : '' }}>Sejarah</option>
                                <option value="Hadits" {{ old('category', $library->category) == 'Hadits' ? 'selected' : '' }}>Hadits</option>
                                <option value="Tafsir" {{ old('category', $library->category) == 'Tafsir' ? 'selected' : '' }}>Tafsir</option>
                                <option value="Amalan" {{ old('category', $library->category) == 'Amalan' ? 'selected' : '' }}>Amalan (Ratib, Maulid, dll)</option>
                                <option value="Lainnya" {{ old('category', $library->category) == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('category')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="price_type">Tipe <span class="text-red-500">*</span></label>
                            <select id="price_type" class="form-select w-full @error('price_type') is-invalid @enderror" name="price_type" required>
                                <option value="free" {{ old('price_type', $library->price_type) == 'free' ? 'selected' : '' }}>Gratis</option>
                                <option value="paid" {{ old('price_type', $library->price_type) == 'paid' ? 'selected' : '' }}>Berbayar</option>
                            </select>
                            @error('price_type')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                         <div>
                            <label class="block text-sm font-medium mb-2" for="file">File PDF</label>
                            <input id="file" class="form-input w-full @error('file') is-invalid @enderror" type="file" name="file" accept="application/pdf" />
                            <div class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah file. Format PDF, Maks 10MB.</div>
                            @if($library->file_path)
                                <div class="text-xs text-blue-500 mt-1">File saat ini: <a href="{{ Storage::url($library->file_path) }}" target="_blank" class="underline">Lihat PDF</a></div>
                            @endif
                            @error('file')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="cover_image">Cover Buku (Opsional)</label>
                            <input id="cover_image" class="form-input w-full @error('cover_image') is-invalid @enderror" type="file" name="cover_image" accept="image/*" />
                             <div class="text-xs text-gray-500 mt-1">Format Gambar (JPG, PNG), Maks 2MB</div>
                             @if($library->cover_image)
                                <div class="mt-2">
                                    <img src="{{ Storage::url($library->cover_image) }}" class="w-20 h-28 object-cover rounded shadow" alt="Current Cover">
                                </div>
                             @endif
                            @error('cover_image')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                         <div>
                            <label class="block text-sm font-medium mb-2" for="is_active">Status Aktif</label>
                            <div class="flex items-center mt-2">
                                <input id="is_active" type="checkbox" class="form-checkbox" name="is_active" value="1" {{ old('is_active', $library->is_active) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Tampilkan Pustaka ini</span>
                            </div>
                        </div>

                    </div>

                    <div class="grid grid-cols-1 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="description">Deskripsi <span class="text-red-500">*</span></label>
                            <textarea class="form-textarea w-full @error('description') is-invalid @enderror" name="description" id="description" cols="30" rows="5" required>{{ old('description', $library->description) }}</textarea>
                            @error('description')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Podcast Episodes Section -->
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Episode Podcast</h3>

                        <!-- Existing Episodes -->
                        @if($library->episodes->count() > 0)
                            <div class="mb-4 space-y-2">
                                <p class="text-sm font-medium text-gray-500 mb-2">Episode Saat Ini:</p>
                                @foreach($library->episodes as $episode)
                                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded bg-white dark:bg-gray-800">
                                        <div class="flex items-center gap-3">
                                            <span class="text-gray-400 text-sm">#{{ $episode->sort_order + 1 }}</span>
                                            <div>
                                                <p class="font-medium text-gray-800 dark:text-gray-200">{{ $episode->title }}</p>
                                                <a href="{{ Storage::url($episode->file_path) }}" target="_blank" class="text-xs text-indigo-500 hover:underline">Download/Listen</a>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="flex items-center space-x-2 text-sm text-red-500 cursor-pointer p-2 hover:bg-red-50 rounded">
                                                <input type="checkbox" name="delete_episodes[]" value="{{ $episode->id }}" class="form-checkbox text-red-500 rounded border-gray-300">
                                                <span>Hapus</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- New Episodes -->
                        <div x-data="{ newEpisodes: [] }">
                             <template x-for="(episode, index) in newEpisodes" :key="index">
                                <div class="grid md:grid-cols-2 gap-4 mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded bg-gray-50 dark:bg-gray-700/50">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Judul Episode Baru <span class="text-red-500">*</span></label>
                                        <input type="text" :name="'new_episodes['+index+'][title]'" class="form-input w-full" required placeholder="Judul Episode">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">File Audio <span class="text-red-500">*</span></label>
                                        <input type="file" :name="'new_episodes['+index+'][file]'" class="form-input w-full" accept="audio/*" required>
                                        <div class="text-xs text-gray-500 mt-1">Format Audio (MP3, WAV, M4A)</div>
                                    </div>
                                    <div class="md:col-span-2 text-right">
                                        <button type="button" @click="newEpisodes.splice(index, 1)" class="text-red-500 text-sm hover:underline">Batal</button>
                                    </div>
                                </div>
                            </template>

                            <button type="button" @click="newEpisodes.push({})" class="btn bg-indigo-500 hover:bg-indigo-600 text-white mt-2">
                                <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                                </svg>
                                Tambah Episode Baru
                            </button>
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
