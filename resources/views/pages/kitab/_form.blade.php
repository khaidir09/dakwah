<div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md space-y-6">
    <div class="grid md:grid-cols-2 gap-6">

        <div>
            <label class="block text-sm font-medium mb-2" for="title">Judul (Latin) <span class="text-red-500">*</span></label>
            <input id="title" class="form-input w-full" type="text" name="title" value="{{ old('title', $book->title) }}" placeholder="Contoh: Sabilal Muhtadin" required />
            @error('title')
                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" for="title_arabic">Judul Arab</label>
            <input id="title_arabic" class="form-input w-full text-right" dir="rtl" lang="ar" type="text" name="title_arabic" value="{{ old('title_arabic', $book->title_arabic) }}" placeholder="سبيل المهتدين" />
            @error('title_arabic')
                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" for="slug">Slug URL</label>
            <input id="slug" class="form-input w-full" type="text" name="slug" value="{{ old('slug', $book->slug) }}" placeholder="Kosongkan untuk dibuat otomatis dari judul" />
            <div class="text-xs text-gray-500 mt-1">Slug tidak berubah otomatis saat judul diubah. Mengubahnya akan mematikan URL lama.</div>
            @error('slug')
                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" for="specialization_id">Bidang Ilmu</label>
            <select id="specialization_id" class="form-select w-full" name="specialization_id">
                <option value="">Pilih Bidang Ilmu</option>
                @foreach ($specializations as $id => $name)
                    <option value="{{ $id }}" @selected((string) old('specialization_id', $book->specialization_id) === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
            @error('specialization_id')
                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" for="language">Bahasa</label>
            <select id="language" class="form-select w-full" name="language">
                <option value="">Pilih Bahasa</option>
                @foreach (\App\Models\Book::LANGUAGES as $value => $label)
                    <option value="{{ $value }}" @selected(old('language', $book->language) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('language')
                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" for="publication_status">Status Naskah</label>
            <select id="publication_status" class="form-select w-full" name="publication_status">
                <option value="">Pilih Status</option>
                @foreach (\App\Models\Book::PUBLICATION_STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(old('publication_status', $book->publication_status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('publication_status')
                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" for="publisher">Penerbit</label>
            <input id="publisher" class="form-input w-full" type="text" name="publisher" value="{{ old('publisher', $book->publisher) }}" />
            @error('publisher')
                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" for="external_url">Tautan Eksternal</label>
            <input id="external_url" class="form-input w-full" type="url" name="external_url" value="{{ old('external_url', $book->external_url) }}" placeholder="https://" />
            @error('external_url')
                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" for="library_id">Tautkan ke Pustaka</label>
            <select id="library_id" class="form-select w-full" name="library_id">
                <option value="">Tidak ada</option>
                @foreach ($libraries as $id => $title)
                    <option value="{{ $id }}" @selected((string) old('library_id', $book->library_id) === (string) $id)>{{ $title }}</option>
                @endforeach
            </select>
            <div class="text-xs text-gray-500 mt-1">Pilih bila naskah digital kitab ini tersedia di Pustaka.</div>
            @error('library_id')
                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2" for="cover_image">Sampul</label>
            @if ($book->cover_thumb_url)
                <img src="{{ $book->cover_thumb_url }}" alt="Sampul {{ $book->title }}" class="w-20 h-28 object-cover rounded-md mb-2">
            @endif
            <input id="cover_image" class="form-input w-full" type="file" name="cover_image" accept="image/*" />
            <div class="text-xs text-gray-500 mt-1">Format gambar (JPG, PNG, WebP), maks 2MB.</div>
            @error('cover_image')
                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-2">Guru Terkait <span class="text-red-500">*</span></label>
        <div class="text-xs text-gray-500 mb-3">Guru harus sudah ada di data guru dan sudah tayang. Urutan baris menjadi urutan tampil.</div>
        <livewire:partials.book-teacher-rows :rows="$teacherRows" />
        @error('teachers')
            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium mb-2" for="description">Deskripsi</label>
        <x-rich-editor name="description" :value="old('description', $book->description)" />
        @error('description')
            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
        @enderror
    </div>

    <div class="flex flex-wrap gap-6">
        <label class="flex items-center">
            <input type="checkbox" class="form-checkbox" name="is_active" value="1" @checked(session()->hasOldInput() ? old('is_active') : $book->is_active) />
            <span class="text-sm ml-2">Tayangkan ke publik</span>
        </label>
        <label class="flex items-center">
            <input type="checkbox" class="form-checkbox" name="is_featured" value="1" @checked(session()->hasOldInput() ? old('is_featured') : $book->is_featured) />
            <span class="text-sm ml-2">Kitab unggulan (tampil di beranda)</span>
        </label>
    </div>
</div>

<div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
    <x-button>
        Simpan
    </x-button>
</div>
