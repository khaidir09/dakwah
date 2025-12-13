<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Edit Event</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('event.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
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
            <form action="{{ route('event.update', $event->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-sm font-medium mb-2" for="name">Nama Event <span class="text-red-500">*</span></label>
                            <input id="name" class="form-input w-full @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name', $event->name) }}" required/>
                            @error('name')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                         <div>
                            <label class="block text-sm font-medium mb-2" for="category">Kategori <span class="text-red-500">*</span></label>
                            <select id="category" name="category" class="form-select w-full @error('category') is-invalid @enderror" required>
                                <option value="Taklim" {{ old('category', $event->category) == 'Taklim' ? 'selected' : '' }}>Taklim</option>
                                <option value="Maulid" {{ old('category', $event->category) == 'Maulid' ? 'selected' : '' }}>Maulid</option>
                                <option value="Dzikir" {{ old('category', $event->category) == 'Dzikir' ? 'selected' : '' }}>Dzikir</option>
                                <option value="Haul" {{ old('category', $event->category) == 'Haul' ? 'selected' : '' }}>Haul</option>
                                <option value="Tabligh Akbar" {{ old('category', $event->category) == 'Tabligh Akbar' ? 'selected' : '' }}>Tabligh Akbar</option>
                            </select>
                            @error('category')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="date">Tanggal & Waktu <span class="text-red-500">*</span></label>
                            <input id="date" class="form-input w-full @error('date') is-invalid @enderror" type="datetime-local" name="date" value="{{ old('date', $event->date ? date('Y-m-d\TH:i', strtotime($event->date)) : '') }}" required/>
                            @error('date')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                         <div>
                            <label class="block text-sm font-medium mb-2" for="access">Akses <span class="text-red-500">*</span></label>
                            <select id="access" name="access" class="form-select w-full @error('access') is-invalid @enderror" required>
                                <option value="Umum" {{ old('access', $event->access) == 'Umum' ? 'selected' : '' }}>Umum</option>
                                <option value="Khusus" {{ old('access', $event->access) == 'Khusus' ? 'selected' : '' }}>Khusus</option>
                            </select>
                            @error('access')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="image">Poster</label>
                            @if ($event->image)
                                <div class="my-2">
                                    <img src="{{ Storage::url($event->image) }}" alt="Poster Saat Ini" class="w-32 h-20 object-cover rounded">
                                    <p class="text-xs text-gray-500 mt-1">Poster saat ini. Kosongkan jika tidak ingin ganti.</p>
                                </div>
                            @endif
                            <input id="image" class="form-input w-full @error('image') is-invalid @enderror" type="file" name="image" accept="image/*" />
                            @error('image')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                         <div>
                            <label class="block text-sm font-medium mb-2" for="location">Nama Lokasi (Tempat) <span class="text-red-500">*</span></label>
                            <input id="location" class="form-input w-full @error('location') is-invalid @enderror" type="text" name="location" value="{{ old('location', $event->location) }}" required placeholder="Contoh: Masjid Raya"/>
                            @error('location')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold my-4">Alamat Lengkap</h2>
                    <div class="grid grid-cols-4 gap-4">

                        <div>
                            <label class="block text-sm font-medium mb-2" for="province">Provinsi</label>
                            <select id="province" class="form-select w-full @error('province') is-invalid @enderror" name="province">
                                <option value="">==Pilih Salah Satu==</option>
                                @foreach($provinces as $code => $name)
                                    <option value="{{ $code }}" {{ old('province', $event->province_code) == $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('province')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="city">Kabupaten/Kota</label>
                            <select id="city" class="form-select w-full @error('city') is-invalid @enderror" name="city" data-selected="{{ old('city', $event->city_code) }}">
                                <option value="">==Pilih Salah Satu==</option>
                            </select>
                            @error('city')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="district">Kecamatan</label>
                            <select id="district" class="form-select w-full" name="district" data-selected="{{ old('district', $event->district_code) }}">
                                <option value="">==Pilih Salah Satu==</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="village">Desa/Kelurahan</label>
                            <select id="village" class="form-select w-full" name="village" data-selected="{{ old('village', $event->village_code) }}">
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
                    $.ajax({
                        type: 'GET',
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
                var cityCode = $(this).val();

                $('#district').empty().append('<option value="">Pilih Kecamatan</option>');
                $('#village').empty().append('<option value="">Pilih Desa/Kelurahan</option>');

                if (cityCode) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-districts/' + cityCode,
                        dataType: 'json',
                        success: function(data) {
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
                var districtCode = $(this).val();

                $('#village').empty().append('<option value="">Pilih Desa/Kelurahan</option>');

                if (districtCode) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-villages/' + districtCode,
                        dataType: 'json',
                        success: function(data) {
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
