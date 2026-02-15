<x-dashboard-layout>
    <div class="grow">
        <form action="{{ route('kelola-mitra.update', $foundation->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- Panel body -->
            <div class="p-6 space-y-6">
                <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">Profil Mitra</h2>
                @if (session('status'))
                    <div role="alert">
                        <div class="mb-4 px-4 py-2 rounded-lg text-sm bg-green-500 text-white">
                            <div class="flex w-full justify-between items-start">
                                <div class="flex">
                                    <svg class="shrink-0 fill-current opacity-80 mt-[3px] mr-3" width="16" height="16" viewBox="0 0 16 16">
                                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM7 11.4L3.6 8 5 6.6l2 2 4-4L12.4 6 7 11.4z" />
                                    </svg>
                                    <div class="font-medium">Data profil berhasil diperbarui.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
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
                    <div>
                        <label class="block text-sm font-medium mb-2" for="name">Nama <span class="text-red-500">*</span></label>
                        <input id="name" class="form-input w-full @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name', $foundation->name) }}" required/>
                        @error('name')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="website_url">Website / Sosmed</label>
                        <input id="website_url" class="form-input w-full @error('website_url') is-invalid @enderror" type="url" name="website_url" value="{{ old('website_url', $foundation->website_url) }}" placeholder="https://example.com" />
                        @error('website_url')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="logo_path">Logo</label>

                        {{-- Tampilkan logo lama jika ada --}}
                        @if($foundation->logo_path)
                            <div class="mb-4">
                                <img src="{{ Storage::url($foundation->logo_path) }}" alt="Logo Yayasan" class="w-32 h-auto rounded-lg shadow-sm border border-gray-200">
                                <p class="text-xs text-gray-500 mt-1">Logo saat ini.</p>
                            </div>
                        @else
                            <div class="mb-4 p-4 border border-dashed border-gray-300 rounded-lg bg-gray-50 text-center">
                                <p class="text-sm text-gray-500">Belum ada logo yang diunggah.</p>
                            </div>
                        @endif

                        <input id="logo_path" class="form-input w-full @error('logo_path') is-invalid @enderror" type="file" name="logo_path" accept="image/*"/>
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah logo.</p>
                        @error('logo_path')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

            </div>

            <footer>
                <div class="flex flex-col px-6 py-5 border-t border-gray-200 dark:border-gray-700/60">
                    <div class="flex self-end">
                        <button type="submit" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white ml-3">Simpan Perubahan</button>
                    </div>
                </div>
            </footer>
        </form>
    </div>
</x-dashboard-layout>
