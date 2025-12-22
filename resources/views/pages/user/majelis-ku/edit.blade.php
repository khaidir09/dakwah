<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Edit Majelis Saya</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('majelis-ku.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                    Kembali
                </a>
            </div>
        </div>

        <div>
            <form action="{{ route('majelis-ku.update', $majelis->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">
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
                            <label class="block text-sm font-medium mb-2" for="nama_majelis">Nama Majelis <span class="text-red-500">*</span></label>
                            <input id="nama_majelis" class="form-input w-full @error('nama_majelis') is-invalid @enderror" type="text" name="nama_majelis" value="{{ old('nama_majelis', $majelis->nama_majelis) }}" required/>
                            @error('nama_majelis')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="teacher_id">Nama Guru <span class="text-red-500">*</span></label>
                            <select id="teacher_id" class="form-select w-full @error('teacher_id') is-invalid @enderror" name="teacher_id" required>
                                <option value="">Pilih Guru</option>
                                @foreach($teachers as $item)
                                    <option value="{{ $item->id }}" @if($item->id == $majelis->teacher_id) selected @endif>{{ $item->name }}</option>
                                @endforeach
                            </select>
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
                            <input id="maps" class="form-input w-full @error('maps') is-invalid @enderror" type="text" name="maps" value="{{ old('maps', $majelis->maps) }}" required/>
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

                    <div class="grid grid-cols-1 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="deskripsi">Deskripsi <span class="text-red-500">*</span></label>
                            <textarea class="form-input w-full @error('deskripsi') is-invalid @enderror" name="deskripsi" id="deskripsi" cols="30" rows="10" required>{{ old('deskripsi', $majelis->deskripsi) }}</textarea>
                            @error('deskripsi')
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
                                    <option value="{{ $code }}" {{ old('province', $majelis->province_code) == $code ? 'selected' : '' }}>
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
                            <select id="city" class="form-select w-full @error('city') is-invalid @enderror" name="city" required data-selected="{{ old('city', $majelis->city_code) }}">
                                <option value="">==Pilih Salah Satu==</option>
                            </select>
                            @error('city')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="district">Kecamatan</label>
                            <select id="district" class="form-select w-full" name="district" data-selected="{{ old('district', $majelis->district_code) }}">
                                <option value="">==Pilih Salah Satu==</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="village">Desa/Kelurahan</label>
                            <select id="village" class="form-select w-full" name="village" data-selected="{{ old('village', $majelis->village_code) }}">
                                <option value="">==Pilih Salah Satu==</option>
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
