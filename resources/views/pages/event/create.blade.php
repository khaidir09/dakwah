<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Tambah Acara</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('event.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
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
            <form action="{{ route('event.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">

                        <div>
                            <label class="block text-sm font-medium mb-2" for="name">Nama Acara <span class="text-red-500">*</span></label>
                            <input id="name" class="form-input w-full @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Maulid Akbar"/>
                            @error('name')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="category">Kategori <span class="text-red-500">*</span></label>
                            <select id="category" class="form-select w-full @error('category') is-invalid @enderror" name="category" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Taklim">Taklim</option>
                                <option value="Maulid">Maulid</option>
                                <option value="Dzikir">Dzikir</option>
                                <option value="Haul">Haul</option>
                                <option value="Tabligh Akbar">Tabligh Akbar</option>
                                <option value="Peringatan Hari Besar Islam">Peringatan Hari Besar Islam</option>
                            </select>
                            @error('category')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="date">Tanggal & Waktu <span class="text-red-500">*</span></label>
                            <input id="date" class="form-input w-full @error('date') is-invalid @enderror" type="datetime-local" name="date" value="{{ old('date') }}" required/>
                            @error('date')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                         <div>
                            <label class="block text-sm font-medium mb-2" for="access">Akses <span class="text-red-500">*</span></label>
                            <select id="access" name="access" class="form-select w-full @error('access') is-invalid @enderror" required>
                                <option value="Umum" {{ old('access') == 'Umum' ? 'selected' : '' }}>Umum</option>
                                <option value="Khusus" {{ old('access') == 'Khusus' ? 'selected' : '' }}>Khusus</option>
                            </select>
                            @error('access')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Assembly Dropdown -->
                        <div>
                            <label class="block text-sm font-medium mb-2" for="assembly_id">Pilih Majelis (Opsional)</label>
                            <select id="assembly_id" class="form-select w-full @error('assembly_id') is-invalid @enderror" name="assembly_id">
                                <option value="">Tidak Ada / Lokasi Manual</option>
                                @foreach($assemblies as $id => $name)
                                    <option value="{{ $id }}" {{ old('assembly_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Jika dipilih, lokasi akan mengikuti data Majelis.</p>
                             @error('assembly_id')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="image">Poster (Gambar)</label>
                            <input id="image" class="form-input w-full @error('image') is-invalid @enderror" type="file" name="image" accept="image/*" />
                            @error('image')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Location Name (Conditional) -->
                        <div id="location-wrapper">
                            <label class="block text-sm font-medium mb-2" for="location">Nama Lokasi (Tempat) <span class="text-red-500">*</span></label>
                            <input id="location" class="form-input w-full @error('location') is-invalid @enderror" type="text" name="location" value="{{ old('location') }}" placeholder="Contoh: Masjid Raya"/>
                            @error('location')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <!-- Address/Regions (Conditional) -->
                    <div id="region-wrapper">
                        <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold my-4">Alamat Lengkap</h2>
                        <div class="grid grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2" for="province">Provinsi</label>
                                <select id="province" class="form-select w-full @error('province') is-invalid @enderror" name="province">
                                    <option value="">Pilih Provinsi</option>
                                    @foreach($provinces as $code => $name)
                                        <option value="{{ $code ?? '' }}">{{ $name ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="city">Kabupaten/Kota</label>
                                <select id="city" class="form-select w-full @error('city') is-invalid @enderror" name="city">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="district">Kecamatan</label>
                                <select id="district" class="form-select w-full" name="district">
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="village">Desa/Kelurahan</label>
                                <select id="village" class="form-select w-full" name="village">
                                    <option value="">Pilih Desa/Kelurahan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Inherited Info Message -->
                    <div id="inherited-info" class="hidden mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <p class="text-blue-700 font-medium">Lokasi dan alamat akan otomatis diambil dari data Majelis yang dipilih.</p>
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

            function toggleLocationFields() {
                var assemblyId = $('#assembly_id').val();
                if (assemblyId) {
                    $('#location-wrapper').hide();
                    $('#region-wrapper').hide();
                    $('#inherited-info').removeClass('hidden');

                    // Remove required attribute when hidden
                    $('#location').removeAttr('required');
                } else {
                    $('#location-wrapper').show();
                    $('#region-wrapper').show();
                    $('#inherited-info').addClass('hidden');

                    // Add required attribute when shown
                    $('#location').attr('required', 'required');
                }
            }

            // Init on load
            toggleLocationFields();

            // On Change Assembly
            $('#assembly_id').on('change', function() {
                toggleLocationFields();
            });

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
