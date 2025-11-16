<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                {{-- DIUBAH --}}
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Edit Guru</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('guru.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
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
            {{-- DIUBAH: action ke route 'update' dan tambah $guru->id --}}
            <form action="{{ route('guru.update', $guru->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') {{-- DIUBAH: Tambahkan method PUT --}}
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-sm font-medium mb-2" for="name">Nama Guru <span class="text-red-500">*</span></label>
                            {{-- DIUBAH: Tambah old() dengan data $guru --}}
                            <input id="name" class="form-input w-full @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name', $guru->name) }}" required/>
                            @error('name')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="tahun_lahir">Tahun Lahir</label>
                            {{-- DIUBAH: Tambah old() dengan data $guru --}}
                            <input id="tahun_lahir" class="form-input w-full @error('tahun_lahir') is-invalid @enderror" type="number" name="tahun_lahir" value="{{ old('tahun_lahir', $guru->tahun_lahir) }}"/>
                            @error('tahun_lahir')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="foto">Foto</label>
                            {{-- TAMBAHAN: Tampilkan foto saat ini --}}
                            @if ($guru->foto)
                                <div class="my-2">
                                    <img src="{{ Storage::url($guru->foto) }}" alt="Foto Saat Ini" class="w-32 h-32 object-cover rounded">
                                    <p class="text-xs text-gray-500 mt-1">Foto saat ini. Kosongkan jika tidak ingin ganti.</p>
                                </div>
                            @endif
                            <input id="foto" class="form-input w-full @error('foto') is-invalid @enderror" type="file" name="foto" />
                            @error('foto')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="biografi">Biografi <span class="text-red-500">*</span></label>
                            {{-- DIUBAH: Tambah old() dengan data $guru --}}
                            <textarea class="form-input w-full @error('biografi') is-invalid @enderror" name="biografi" id="biografi" cols="30" rows="10" required>{{ old('biografi', $guru->biografi) }}</textarea>
                            @error('biografi')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold my-4">Domisili</h2>
                    <div class="grid grid-cols-4 gap-4">
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" for="province">Provinsi <span class="text-red-500">*</span></label>
                            <select id="province" class="form-select w-full @error('province') is-invalid @enderror" name="province" required>
                                <option value="">==Pilih Salah Satu==</option>
                                @foreach($provinces as $code => $name)
                                    {{-- DIUBAH: Tambah logika 'selected' --}}
                                    <option value="{{ $code }}" {{ old('province', $guru->province_code) == $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('province')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="city">Kabupaten/Kota <span class="text-red-500">*</span></label>
                            {{-- DIUBAH: Tambah data-selected untuk JS --}}
                            <select id="city" class="form-select w-full @error('city') is-invalid @enderror" name="city" required data-selected="{{ old('city', $guru->city_code) }}">
                                <option value="">==Pilih Salah Satu==</option>
                            </select>
                            @error('city')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" for="district">Kecamatan</label>
                            {{-- DIUBAH: Tambah data-selected untuk JS --}}
                            <select id="district" class="form-select w-full" name="district" data-selected="{{ old('district', $guru->district_code) }}">
                                <option value="">==Pilih Salah Satu==</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" for="village">Desa/Kelurahan</label>
                            {{-- DIUBAH: Tambah data-selected untuk JS --}}
                            <select id="village" class="form-select w-full" name="village" data-selected="{{ old('village', $guru->village_code) }}">
                                <option value="">==Pilih Salah Satu==</option>
                            </select>
                        </div>
                    </div>

                    <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold my-4">Tanggal Wafat</h2>
                    <div class="grid grid-cols-4 gap-4 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="wafat_masehi">Wafat Masehi</label>
                            {{-- DIUBAH: Tambah old() dengan data $guru --}}
                            <input id="wafat_masehi" class="form-input w-full @error('wafat_masehi') is-invalid @enderror" type="number" name="wafat_masehi" value="{{ old('wafat_masehi', $guru->wafat_masehi) }}"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" for="wafat_hijriah_day">Tanggal Wafat</label>
                            <select id="wafat_hijriah_day" name="wafat_hijriah_day" class="form-select w-full">
                                <option value="">Tanggal</option>
                                @for ($i = 1; $i <= 30; $i++)
                                    {{-- DIUBAH: Tambah logika 'selected' --}}
                                    <option value="{{ $i }}" {{ old('wafat_hijriah_day', $guru->wafat_hijriah_day) == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" for="wafat_hijriah_month">Bulan Wafat</label>
                            <select id="wafat_hijriah_month" name="wafat_hijriah_month" class="form-select w-full">
                                <option value="">Bulan</option>
                                {{-- DIUBAH: Tambah logika 'selected' --}}
                                <option value="1" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 1 ? 'selected' : '' }}>Muharram</option>
                                <option value="2" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 2 ? 'selected' : '' }}>Safar</option>
                                <option value="3" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 3 ? 'selected' : '' }}>Rabi'ul Awal</option>
                                <option value="4" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 4 ? 'selected' : '' }}>Rabi'ul Akhir</option>
                                <option value="5" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 5 ? 'selected' : '' }}>Jumadal Awal</option>
                                <option value="6" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 6 ? 'selected' : '' }}>Jumadal Akhir</option>
                                <option value="7" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 7 ? 'selected' : '' }}>Rajab</option>
                                <option value="8" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 8 ? 'selected' : '' }}>Sya'ban</option>
                                <option value="9" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 9 ? 'selected' : '' }}>Ramadhan</option>
                                <option value="10" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 10 ? 'selected' : '' }}>Syawwal</option>
                                <option value="11" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 11 ? 'selected' : '' }}>Dzulqa'dah</option>
                                <option value="12" {{ old('wafat_hijriah_month', $guru->wafat_hijriah_month) == 12 ? 'selected' : '' }}>Dzulhijjah</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" for="wafat_hijriah_year">Tahun Wafat</label>
                            <select id="wafat_hijriah_year" name="wafat_hijriah_year" class="form-select w-full">
                                <option value="">Tahun</option>
                                @for ($i = 1400; $i <= 1447; $i++)
                                    {{-- DIUBAH: Tambah logika 'selected' --}}
                                    <option value="{{ $i }}" {{ old('wafat_hijriah_year', $guru->wafat_hijriah_year) == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
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

    <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            // 1. Ambil semua data yang tersimpan dari atribut data-selected
            let provinceCode = $('#province').val();
            let selectedCity = $('#city').data('selected');
            let selectedDistrict = $('#district').data('selected');
            let selectedVillage = $('#village').data('selected');

            // 2. Jika ada provinsi yang dipilih, muat daftar kotanya
            if (provinceCode) {
                $.ajax({
                    type: 'GET',
                    url: '/get-cities/' + provinceCode,
                    dataType: 'json',
                    success: function(data) {
                        $('#city').empty().append('<option value="">==Pilih Salah Satu==</option>');
                        $.each(data, function(code, name) {
                            $('#city').append('<option value="' + code + '">' + name + '</option>');
                        });
                        
                        // 3. Setelah daftar kota dimuat, pilih kota yang tersimpan
                        if (selectedCity) {
                            $('#city').val(selectedCity);
                            
                            // 4. Jika ada kota yang dipilih, muat daftar kecamatannya
                            $.ajax({
                                type: 'GET',
                                url: '/get-districts/' + selectedCity,
                                dataType: 'json',
                                success: function(data) {
                                    $('#district').empty().append('<option value="">==Pilih Salah Satu==</option>');
                                    $.each(data, function(code, name) {
                                        $('#district').append('<option value="' + code + '">' + name + '</option>');
                                    });

                                    // 5. Setelah daftar kecamatan dimuat, pilih kecamatan yang tersimpan
                                    if (selectedDistrict) {
                                        $('#district').val(selectedDistrict);

                                        // 6. Jika ada kecamatan, muat daftar desanya
                                        $.ajax({
                                            type: 'GET',
                                            url: '/get-villages/' + selectedDistrict,
                                            dataType: 'json',
                                            success: function(data) {
                                                $('#village').empty().append('<option value="">==Pilih Salah Satu==</option>');
                                                $.each(data, function(code, name) {
                                                    $('#village').append('<option value="' + code + '">' + name + '</option>');
                                                });

                                                // 7. Pilih desa yang tersimpan
                                                if (selectedVillage) {
                                                    $('#village').val(selectedVillage);
                                                }
                                            }
                                        });
                                    }
                                }
                            });
                        }
                    }
                });
            }

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