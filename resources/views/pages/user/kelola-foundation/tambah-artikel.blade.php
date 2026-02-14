<x-dashboard-layout>
    <div class="grow">
        <div class="p-6 space-y-6">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold">Tambah Artikel Ilmiah</h2>
                <a href="{{ route('kelola-artikel.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">Kembali</a>
            </div>

            <form action="{{ route('kelola-artikel.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-6">

                    {{-- Foundation Selection --}}
                    <div>
                        <label class="block text-sm font-medium mb-2" for="foundation_id">Yayasan <span class="text-red-500">*</span></label>
                        @if($foundations->count() > 1)
                            <select id="foundation_id" class="form-select w-full @error('foundation_id') is-invalid @enderror" name="foundation_id" required>
                                <option value="">Pilih Yayasan</option>
                                @foreach($foundations as $foundation)
                                    <option value="{{ $foundation->id }}" {{ old('foundation_id') == $foundation->id ? 'selected' : '' }}>{{ $foundation->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="foundation_id" value="{{ $foundations->first()->id }}">
                            <input class="form-input w-full bg-gray-100 text-gray-500" type="text" value="{{ $foundations->first()->name }}" disabled>
                        @endif
                        @error('foundation_id')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="title">Judul <span class="text-red-500">*</span></label>
                        <input id="title" class="form-input w-full @error('title') is-invalid @enderror" type="text" name="title" value="{{ old('title') }}" required/>
                        @error('title')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="subtitle">Sub Judul</label>
                        <input id="subtitle" class="form-input w-full @error('subtitle') is-invalid @enderror" type="text" name="subtitle" value="{{ old('subtitle') }}"/>
                        @error('subtitle')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="author_name">Penulis <span class="text-red-500">*</span></label>
                        <input id="author_name" class="form-input w-full @error('author_name') is-invalid @enderror" type="text" name="author_name" value="{{ old('author_name', Auth::user()->name) }}" required/>
                        @error('author_name')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="category">Kategori <span class="text-red-500">*</span></label>
                        <input id="category" class="form-input w-full @error('category') is-invalid @enderror" type="text" name="category" value="{{ old('category') }}" placeholder="Contoh: Fiqih, Aqidah" required/>
                        @error('category')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="cover_image">Gambar Sampul</label>
                        <input id="cover_image" class="form-input w-full @error('cover_image') is-invalid @enderror" type="file" name="cover_image" accept="image/*"/>
                        @error('cover_image')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white ml-3">Simpan Artikel</button>
                </div>
            </form>
        </div>
    </div>
</x-dashboard-layout>
