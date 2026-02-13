<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                {{-- DIUBAH --}}
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Edit Tulisan</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('posts.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                    Kembali
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <form action="{{ Auth::user()->hasRole('Super Admin') ? route('posts.update', $post->id) : route('kelola-tulisan.update', $post->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="title">Judul <span class="text-red-500">*</span></label>
                            <input id="title" class="form-input w-full" type="text" name="title" value="{{ old('title', $post->title) }}" required />
                            @error('title') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                        </div>

                        <!-- Labels -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="labels">Label (Pisahkan dengan koma)</label>
                            <input id="labels" class="form-input w-full" type="text" name="labels" value="{{ old('labels', $post->labels->pluck('name')->implode(', ')) }}" placeholder="Contoh: Fiqih, Sejarah, Umum" />
                        </div>

                        <!-- Source -->
                        <div class="col-span-2" x-data="{ sources: {{ Js::from(old('source', $post->source ?? [])) }}, errors: {{ Js::from($errors->messages()) }} }">
                            <label class="block text-sm font-medium mb-2">Sumber</label>
                            <template x-for="(source, index) in sources" :key="index">
                                <div class="flex gap-4 mb-2 items-start">
                                    <div class="flex-1">
                                        <input type="text" :name="`source[${index}][name]`" x-model="source.name" class="form-input w-full" placeholder="Nama Sumber (Contoh: Kitab Ihya Ulumuddin)">
                                        <div x-show="errors[`source.${index}.name`]" x-text="errors[`source.${index}.name`] ? errors[`source.${index}.name`][0] : ''" class="text-xs mt-1 text-red-500"></div>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" :name="`source[${index}][url]`" x-model="source.url" class="form-input w-full" placeholder="Link URL (Opsional)">
                                        <div x-show="errors[`source.${index}.url`]" x-text="errors[`source.${index}.url`] ? errors[`source.${index}.url`][0] : ''" class="text-xs mt-1 text-red-500"></div>
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

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="status">Status <span class="text-red-500">*</span></label>
                            <select id="status" class="form-select w-full" name="status" required>
                                <option value="draft" {{ old('status', $post->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $post->status) == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            @error('status') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                        </div>

                        <!-- Cover Image -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="cover_image">Gambar Cover</label>
                            @if($post->cover_image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($post->cover_image) }}" alt="Cover" class="h-32 rounded object-cover">
                                </div>
                            @endif
                            <input id="cover_image" class="form-input w-full" type="file" name="cover_image" accept="image/*" />
                            @error('cover_image') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 mt-6">
                        <!-- Content -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="content">Isi Tulisan <span class="text-red-500">*</span></label>
                            <textarea id="content" class="form-textarea w-full" name="content" rows="10" required>{{ old('content', $post->content) }}</textarea>
                            @error('content') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
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
</x-dashboard-layout>
