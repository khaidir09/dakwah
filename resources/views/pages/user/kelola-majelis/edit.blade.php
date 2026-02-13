<x-dashboard-layout>
    <div class="grow">
        <form action="{{ route('kelola-majelis.update', $majelis->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- Panel body -->
            <div class="p-6 space-y-6">
                <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">Kelola Majelis</h2>
                @if (session('status'))
                    <div role="alert">
                        <div class="mb-4 px-4 py-2 rounded-lg text-sm bg-green-500 text-white">
                            <div class="flex w-full justify-between items-start">
                                <div class="flex">
                                    <svg class="shrink-0 fill-current opacity-80 mt-[3px] mr-3" width="16" height="16" viewBox="0 0 16 16">
                                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM7 11.4L3.6 8 5 6.6l2 2 4-4L12.4 6 7 11.4z" />
                                    </svg>
                                    <div class="font-medium">Data Majelis berhasil diperbarui.</div>
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
                        <label class="block text-sm font-medium mb-2" for="nama_majelis">Nama Majelis <span class="text-red-500">*</span></label>
                        <input id="nama_majelis" class="form-input w-full @error('nama_majelis') is-invalid @enderror" type="text" name="nama_majelis" value="{{ old('nama_majelis', $majelis->nama_majelis) }}" required/>
                        @error('nama_majelis')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="tipe">Tipe Majelis</label>
                        <select id="tipe" class="form-select w-full @error('tipe') is-invalid @enderror" name="tipe">
                            <option value="">Pilih Tipe</option>
                            <option value="Majelis" {{ old('tipe', $majelis->tipe) == "Majelis" ? 'selected' : '' }}>Majelis</option>
                            <option value="Mesjid" {{ old('tipe', $majelis->tipe) == "Mesjid" ? 'selected' : '' }}>Mesjid</option>
                            <option value="Langgar" {{ old('tipe', $majelis->tipe) == "Langgar" ? 'selected' : '' }}>Langgar</option>
                            <option value="Musholla" {{ old('tipe', $majelis->tipe) == "Musholla" ? 'selected' : '' }}>Musholla</option>
                        </select>
                        @error('tipe')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="teacher_id">Nama Guru <span class="text-red-500">*</span></label>
                        <input id="teacher_id" class="form-input w-full dark:disabled:placeholder:text-gray-600 disabled:border-gray-200 dark:disabled:border-gray-700 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-400 dark:disabled:text-gray-600 disabled:cursor-not-allowed shadow-none @error('teacher_id') is-invalid @enderror" type="text" name="teacher_id" value="{{ old('teacher_id', $majelis->teacher->name) }}" disabled/>
                        @error('teacher_id')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="alamat">Alamat <span class="text-red-500">*</span></label>
                        <input id="alamat" class="form-input w-full @error('alamat') is-invalid @enderror" type="text" name="alamat" value="{{ old('alamat', $majelis->alamat) }}" required/>
                        @error('alamat')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="maps">Maps</label>
                        <input id="maps" class="form-input w-full @error('maps') is-invalid @enderror" type="text" name="maps" value="{{ old('maps', $majelis->maps) }}"/>
                        @error('maps')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="gambar">Gambar</label>

                        {{-- Tampilkan gambar lama jika ada --}}
                        @if($majelis->gambar)
                            <div class="mb-4">
                                <img src="{{ Storage::url($majelis->gambar) }}" alt="Gambar Majelis" class="w-48 h-auto rounded-lg shadow-sm border border-gray-200">
                                <p class="text-xs text-gray-500 mt-1">Gambar saat ini.</p>
                            </div>
                        @else
                            <div class="mb-4 p-4 border border-dashed border-gray-300 rounded-lg bg-gray-50 text-center">
                                <p class="text-sm text-gray-500">Belum ada gambar yang diunggah.</p>
                            </div>
                        @endif

                        <input id="gambar" class="form-input w-full @error('gambar') is-invalid @enderror" type="file" name="gambar" accept="image/*"/>
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah gambar.</p>
                        @error('gambar')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">Media Sosial</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="youtube">YouTube</label>
                            <input id="youtube" class="form-input w-full @error('youtube') is-invalid @enderror" type="text" name="youtube" value="{{ old('youtube', $majelis->youtube) }}" placeholder="Link atau Username Channel" />
                            @error('youtube')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="instagram">Instagram</label>
                            <input id="instagram" class="form-input w-full @error('instagram') is-invalid @enderror" type="text" name="instagram" value="{{ old('instagram', $majelis->instagram) }}" placeholder="Link atau Username Profile" />
                            @error('instagram')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="facebook">Facebook</label>
                            <input id="facebook" class="form-input w-full @error('facebook') is-invalid @enderror" type="text" name="facebook" value="{{ old('facebook', $majelis->facebook) }}" placeholder="Link atau Username Page" />
                            @error('facebook')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="tiktok">TikTok</label>
                            <input id="tiktok" class="form-input w-full @error('tiktok') is-invalid @enderror" type="text" name="tiktok" value="{{ old('tiktok', $majelis->tiktok) }}" placeholder="Link atau Username Account" />
                            @error('tiktok')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 mt-6">
                    <div>
                        <label class="block text-sm font-medium mb-2" for="deskripsi">Deskripsi <span class="text-red-500">*</span></label>
                        <textarea class="form-textarea w-full @error('deskripsi') is-invalid @enderror" name="deskripsi" id="deskripsi" cols="30" rows="10" required>{{ old('deskripsi', $majelis->deskripsi) }}</textarea>
                        @error('deskripsi')
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
