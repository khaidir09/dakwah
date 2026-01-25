<div>
    <!-- Progress Bar -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-emerald-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $step === 1 ? '33%' : ($step === 2 ? '66%' : '100%') }}"></div>
            </div>
        </div>
        <div class="flex justify-between mt-2 text-sm text-gray-600">
            <span class="{{ $step >= 1 ? 'font-bold text-emerald-700' : '' }}">Cari Guru</span>
            <span class="{{ $step >= 2 ? 'font-bold text-emerald-700' : '' }}">Data Guru</span>
            <span class="{{ $step >= 3 ? 'font-bold text-emerald-700' : '' }}">Data Majelis</span>
        </div>
    </div>

    <!-- Step 1: Search Teacher -->
    @if($step === 1)
        <div class="space-y-6">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900">Pilih Guru Pembina</h2>
                <p class="mt-1 text-sm text-gray-600">Cari nama guru yang akan menjadi pembina majelis ini.</p>
            </div>

            <div class="max-w-xl mx-auto">
                <input wire:model.live="searchKeyword" type="text" placeholder="Ketik nama guru..." class="w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm">

                @if(strlen($searchKeyword) < 2)
                    <p class="mt-2 text-sm text-gray-500 text-center">Ketik minimal 2 karakter untuk mencari.</p>
                @endif

                @if(count($teachers) > 0)
                    <div class="mt-4 bg-white border border-gray-200 rounded-md shadow-sm divide-y divide-gray-100">
                        @foreach($teachers as $teacher)
                            <div wire:click="selectTeacher({{ $teacher->id }}, '{{ $teacher->name }}')"
                                    class="p-4 cursor-pointer hover:bg-emerald-50 transition flex items-center justify-between {{ $selectedTeacherId === $teacher->id ? 'bg-emerald-50 ring-2 ring-emerald-500' : '' }}">
                                <div class="flex items-center space-x-3">
                                    @if($teacher->foto)
                                        <img src="{{ Storage::url($teacher->foto) }}" alt="" class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500 text-xs">No Img</span>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $teacher->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $teacher->domisili }}</p>
                                    </div>
                                </div>
                                @if($selectedTeacherId === $teacher->id)
                                    <span class="text-emerald-600 font-bold">Terpilih</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif(strlen($searchKeyword) >= 2)
                    <div class="mt-4 p-4 text-center text-gray-500 bg-gray-50 rounded-md">
                        Guru tidak ditemukan.
                    </div>
                @endif
            </div>

            <div class="flex justify-between items-center mt-8 pt-4 border-t">
                <button wire:click="goToStep2" class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">
                    Guru belum terdaftar? Tambah Baru
                </button>

                <button wire:click="proceedToStep3WithTeacher"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150"
                        {{ !$selectedTeacherId ? 'disabled' : '' }}>
                    Lanjut
                </button>
            </div>
        </div>
    @endif

    <!-- Step 2: Create Teacher -->
    @if($step === 2)
        <div class="space-y-6">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900">Tambah Data Guru</h2>
                <p class="mt-1 text-sm text-gray-600">Lengkapi data guru pembina majelis.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block font-medium text-sm text-gray-700">Nama Guru / Syaikh</label>
                    <input wire:model="teacherName" type="text" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full">
                    @error('teacherName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block font-medium text-sm text-gray-700">Biografi Singkat</label>
                    <textarea wire:model="teacherBio" rows="3" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full"></textarea>
                    @error('teacherBio') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Provinsi</label>
                    <select wire:model.live="selectedTeacherProvince" class="form-select border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full">
                        <option value="">Pilih Provinsi</option>
                        @foreach($teacherProvinces as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('selectedTeacherProvince') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Kota/Kabupaten</label>
                    <select wire:model.live="selectedTeacherCity" class="form-select border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full" {{ empty($teacherCities) ? 'disabled' : '' }}>
                        <option value="">Pilih Kota/Kab</option>
                        @foreach($teacherCities as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('selectedTeacherCity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Kecamatan</label>
                    <select wire:model.live="selectedTeacherDistrict" class="form-select border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full" {{ empty($teacherDistricts) ? 'disabled' : '' }}>
                        <option value="">Pilih Kecamatan</option>
                        @foreach($teacherDistricts as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('selectedTeacherDistrict') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Desa/Kelurahan</label>
                    <select wire:model.live="selectedTeacherVillage" class="form-select border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full" {{ empty($teacherVillages) ? 'disabled' : '' }}>
                        <option value="">Pilih Kelurahan</option>
                        @foreach($teacherVillages as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('selectedTeacherVillage') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="block font-medium text-sm text-gray-700">Tahun Lahir <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <input wire:model="teacherBirthYear" type="number" placeholder="Contoh: 1980" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full">
                    @error('teacherBirthYear') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Foto</label>
                    <input wire:model="teacherPhoto" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    @error('teacherPhoto') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-between items-center mt-8 pt-4 border-t">
                <button wire:click="backToStep1" class="text-gray-600 hover:text-gray-900">
                    &larr; Kembali Cari
                </button>

                <button wire:click="saveTeacherAndProceed" wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <span wire:loading.remove wire:target="saveTeacherAndProceed">Simpan & Lanjut</span>
                    <span wire:loading wire:target="saveTeacherAndProceed">Menyimpan...</span>
                </button>
            </div>
        </div>
    @endif

    <!-- Step 3: Create Majelis -->
    @if($step === 3)
        <div class="space-y-6">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900">Data Majelis</h2>
                <p class="mt-1 text-sm text-gray-600">Lengkapi informasi majelis Anda.</p>
            </div>

            <div class="bg-emerald-50 p-4 rounded-md mb-6">
                <p class="text-sm text-emerald-800">
                    <strong>Guru Pembina:</strong> {{ $selectedTeacherName }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block font-medium text-sm text-gray-700">Nama Majelis</label>
                    <input wire:model="majelisName" type="text" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full">
                    @error('majelisName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block font-medium text-sm text-gray-700">Deskripsi</label>
                    <textarea wire:model="majelisDesc" rows="3" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full"></textarea>
                    @error('majelisDesc') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Region Selectors -->
                <div>
                    <label class="block font-medium text-sm text-gray-700">Provinsi</label>
                    <select wire:model.live="selectedProvince" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full">
                        <option value="">Pilih Provinsi</option>
                        @foreach($provinces as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('selectedProvince') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Kota/Kabupaten</label>
                    <select wire:model.live="selectedCity" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full" {{ empty($cities) ? 'disabled' : '' }}>
                        <option value="">Pilih Kota/Kab</option>
                        @foreach($cities as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('selectedCity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Kecamatan</label>
                    <select wire:model.live="selectedDistrict" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full" {{ empty($districts) ? 'disabled' : '' }}>
                        <option value="">Pilih Kecamatan</option>
                        @foreach($districts as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('selectedDistrict') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-medium text-sm text-gray-700">Desa/Kelurahan</label>
                    <select wire:model.live="selectedVillage" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full" {{ empty($villages) ? 'disabled' : '' }}>
                        <option value="">Pilih Kelurahan</option>
                        @foreach($villages as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('selectedVillage') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block font-medium text-sm text-gray-700">Alamat Lengkap</label>
                    <textarea wire:model="majelisAddress" rows="2" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full"></textarea>
                    @error('majelisAddress') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block font-medium text-sm text-gray-700">Link Google Maps</label>
                    <input wire:model="majelisMaps" type="url" placeholder="https://maps.google.com/..." class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full">
                    @error('majelisMaps') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Media Sosial</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-medium text-sm text-gray-700">YouTube</label>
                            <input wire:model="youtube" type="text" placeholder="Link/Username Channel" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('youtube') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Instagram</label>
                            <input wire:model="instagram" type="text" placeholder="Link/Username Profile" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('instagram') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700">Facebook</label>
                            <input wire:model="facebook" type="text" placeholder="Link/Username Page" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('facebook') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block font-medium text-sm text-gray-700">TikTok</label>
                            <input wire:model="tiktok" type="text" placeholder="Link/Username Account" class="border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm mt-1 block w-full">
                            @error('tiktok') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="col-span-2">
                    <label class="block font-medium text-sm text-gray-700">Foto Majelis</label>
                    <input wire:model="gambar" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    @error('gambar') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-between items-center mt-8 pt-4 border-t">
                <button wire:click="backToStep1" class="text-gray-600 hover:text-gray-900">
                    &larr; Kembali
                </button>

                <button wire:click="saveMajelis" wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <span wire:loading.remove wire:target="saveMajelis">Selesai & Simpan</span>
                    <span wire:loading wire:target="saveMajelis">Menyimpan...</span>
                </button>
            </div>
        </div>
    @endif
</div>