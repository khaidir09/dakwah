<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Tambah Guru</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('guru.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                    Kembali
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="px-4 py-2 rounded-lg text-sm bg-green-500 text-white relative" role="alert">
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif

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
            <form action="{{ route('guru.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-sm font-medium mb-2" for="name">Nama Guru <span class="text-red-500">*</span></label>
                            <input id="name" class="form-input w-full @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" required/>
                            @error('name')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="tahun_lahir">Tahun Lahir</label>
                            <input id="tahun_lahir" class="form-input w-full @error('tahun_lahir') is-invalid @enderror" type="number" name="tahun_lahir" value="{{ old('tahun_lahir') }}"/>
                            @error('tahun_lahir')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="foto">Foto</label>
                            <input id="foto" class="form-input w-full @error('foto') is-invalid @enderror" type="file" name="foto" />
                            @error('foto')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 mt-6" x-data="{ sources: [{ name: '', url: '' }] }">
                        <label class="block text-sm font-medium mb-2">Sumber Biografi</label>
                        <template x-for="(source, index) in sources" :key="index">
                            <div class="flex gap-4 mb-2 items-start">
                                <div class="flex-1">
                                    <input type="text" :name="`source[${index}][name]`" x-model="source.name" class="form-input w-full" placeholder="Nama Sumber (Contoh: Wikipedia)">
                                </div>
                                <div class="flex-1">
                                    <input type="text" :name="`source[${index}][url]`" x-model="source.url" class="form-input w-full" placeholder="Link URL (Opsional)">
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

                    <div class="grid grid-cols-1 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="biografi">Biografi <span class="text-red-500">*</span></label>
                            <textarea class="form-textarea w-full @error('biografi') is-invalid @enderror" name="biografi" id="biografi" cols="30" rows="10" required>{{ old('biografi') }}</textarea>
                            @error('biografi')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-2" for="maps">Maps (Embed)</label>
                            <textarea class="form-input w-full @error('maps') is-invalid @enderror" name="maps" id="maps" cols="30" rows="3">{{ old('maps') }}</textarea>
                            @error('maps')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold my-4">Domisili</h2>
                    <div class="grid grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="province">Provinsi <span class="text-red-500">*</span></label>
                            {{-- ID harus "province" --}}
                            <select id="province" class="form-select w-full @error('province') is-invalid @enderror" name="province" required>
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinces as $code => $name)
                                    <option value="{{ $code ?? '' }}">{{ $name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="city">Kabupaten/Kota <span class="text-red-500">*</span></label>
                            {{-- ID harus "city" --}}
                            <select id="city" class="form-select w-full @error('city') is-invalid @enderror" name="city" required>
                                <option value="">Pilih Kabupaten/Kota</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" for="district">Kecamatan</label>
                            {{-- ID harus "district" --}}
                            <select id="district" class="form-select w-full" name="district">
                                <option value="">Pilih Kecamatan</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" for="village">Desa/Kelurahan</label>
                            {{-- ID harus "village" --}}
                            <select id="village" class="form-select w-full" name="village">
                                <option value="">Pilih Desa/Kelurahan</option>
                            </select>
                        </div>
                    </div>

                    <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold my-4">Tanggal Wafat</h2>
                    <div class="grid grid-cols-4 gap-4 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="wafat_masehi">Wafat Masehi</label>
                            <input id="wafat_masehi" class="form-input w-full @error('wafat_masehi') is-invalid @enderror" type="date" name="wafat_masehi" value="{{ old('wafat_masehi') }}"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" for="wafat_hijriah_day">Tanggal Wafat</label>
                            <select id="wafat_hijriah_day" name="wafat_hijriah_day" class="form-select w-full">
                                <option value="">Tanggal</option>
                                @for ($i = 1; $i <= 30; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" for="wafat_hijriah_month">Bulan Wafat</label>
                            <select id="wafat_hijriah_month" name="wafat_hijriah_month" class="form-select w-full">
                                <option value="">Bulan</option>
                                <option value="1">Muharram</option>
                                <option value="2">Safar</option>
                                <option value="3">Rabi'ul Awal</option>
                                <option value="4">Rabi'ul Akhir</option>
                                <option value="5">Jumadal Awal</option>
                                <option value="6">Jumadal Akhir</option>
                                <option value="7">Rajab</option>
                                <option value="8">Sya'ban</option>
                                <option value="9">Ramadhan</option>
                                <option value="10">Syawwal</option>
                                <option value="11">Dzulqa'dah</option>
                                <option value="12">Dzulhijjah</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" for="wafat_hijriah_year">Tahun Wafat</label>
                            <select id="wafat_hijriah_year" name="wafat_hijriah_year" class="form-select w-full">
                                <option value="">Tahun</option>
                                @for ($i = 1400; $i <= 1447; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
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

    <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>

    <script>
        // Jalankan saat dokumen siap
        $(document).ready(function() {

            // 1. Event Listener untuk PROVINSI
            $('#province').on('change', function() {
                var provinceCode = $(this).val(); // Ambil code provinsi

                // Kosongkan dropdown di bawahnya
                $('#city').empty().append('<option value="">Pilih Kabupaten/Kota</option>');
                $('#district').empty().append('<option value="">Pilih Kecamatan</option>');
                $('#village').empty().append('<option value="">Pilih Desa/Kelurahan</option>');

                if (provinceCode) {
                    // Gunakan AJAX GET, sama seperti di file contoh
                    $.ajax({
                        type: 'GET',
                        // Panggil route baru kita, masukkan code provinsi ke URL
                        url: '/get-cities/' + provinceCode, 
                        dataType: 'json',
                        success: function(data) {
                            // Isi dropdown 'city'
                            $.each(data, function(code, name) {
                                $('#city').append('<option value="' + code + '">' + name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("Error memuat kota:", error);
                        }
                    });
                }
            });

            // 2. Event Listener untuk KOTA/KABUPATEN
            $('#city').on('change', function() {
                var cityCode = $(this).val(); // Ambil code kota

                // Kosongkan dropdown di bawahnya
                $('#district').empty().append('<option value="">Pilih Kecamatan</option>');
                $('#village').empty().append('<option value="">Pilih Desa/Kelurahan</option>');

                if (cityCode) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-districts/' + cityCode, // Panggil route 'districts'
                        dataType: 'json',
                        success: function(data) {
                            // Isi dropdown 'district'
                            $.each(data, function(code, name) {
                                $('#district').append('<option value="' + code + '">' + name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("Error memuat kecamatan:", error);
                        }
                    });
                }
            });

            // 3. Event Listener untuk KECAMATAN
            $('#district').on('change', function() {
                var districtCode = $(this).val(); // Ambil code kecamatan

                // Kosongkan dropdown di bawahnya
                $('#village').empty().append('<option value="">Pilih Desa/Kelurahan</option>');

                if (districtCode) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-villages/' + districtCode, // Panggil route 'villages'
                        dataType: 'json',
                        success: function(data) {
                            // Isi dropdown 'village'
                            $.each(data, function(code, name) {
                                $('#village').append('<option value="' + code + '">' + name + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("Error memuat desa:", error);
                        }
                    });
                }
            });

        });
    </script>
</x-app-layout>