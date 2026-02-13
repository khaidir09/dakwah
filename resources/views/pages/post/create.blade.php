<x-dashboard-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Tambah Tulisan</h1>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
            <form action="{{ Auth::user()->hasRole('Super Admin') ? route('admin.posts.store') : route('kelola-tulisan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="title">Judul <span class="text-red-500">*</span></label>
                        <input id="title" class="form-input w-full" type="text" name="title" value="{{ old('title') }}" required />
                        @error('title') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                    </div>

                    <!-- Labels -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="labels">Label (Pisahkan dengan koma)</label>
                        <input id="labels" class="form-input w-full" type="text" name="labels" value="{{ old('labels') }}" placeholder="Contoh: Fiqih, Sejarah, Umum" />
                        <div class="text-xs text-gray-500 mt-1">Gunakan koma untuk memisahkan label.</div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="status">Status <span class="text-red-500">*</span></label>
                        <select id="status" class="form-select w-full" name="status" required>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                        @error('status') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                    </div>

                    <!-- Cover Image -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="cover_image">Gambar Cover</label>
                        <input id="cover_image" class="form-input w-full" type="file" name="cover_image" accept="image/*" />
                        @error('cover_image') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                    </div>

                    <!-- Content -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="content">Isi Tulisan <span class="text-red-500">*</span></label>
                        <textarea id="content" class="form-textarea w-full" name="content" rows="10" required>{{ old('content') }}</textarea>
                        @error('content') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex justify-end pt-5">
                        <button type="submit" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-dashboard-layout>
