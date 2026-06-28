<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
        <div class="grid md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-medium mb-2">Nama Acara <span class="text-red-500">*</span></label>
                <input class="form-input w-full" type="text" name="name" value="{{ old('name', $acara?->name) }}" required/>
                @error('name')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Kategori <span class="text-red-500">*</span></label>
                <select class="form-select w-full" name="category" required>
                    <option value="">Pilih Kategori</option>
                    @foreach(['Taklim','Maulid','Dzikir','Haul','Tabligh Akbar','Peringatan Hari Besar Islam'] as $k)
                        <option value="{{ $k }}" {{ old('category', $acara?->category) == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
                @error('category')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Tanggal & Waktu <span class="text-red-500">*</span></label>
                <input class="form-input w-full" type="datetime-local" name="date" value="{{ old('date', $acara?->date ? \Carbon\Carbon::parse($acara->date)->format('Y-m-d\TH:i') : '') }}" required/>
                <p class="text-xs text-gray-500 mt-1">Tanggal acara minimal 7 hari dari hari ini.</p>
                @error('date')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Akses <span class="text-red-500">*</span></label>
                <select class="form-select w-full" name="access" required>
                    <option value="Umum" {{ old('access', $acara?->access) == 'Umum' ? 'selected' : '' }}>Umum</option>
                    <option value="Khusus" {{ old('access', $acara?->access) == 'Khusus' ? 'selected' : '' }}>Khusus</option>
                </select>
            </div>

            {{-- Majelis: hanya yang dimiliki atau diikuti kontributor. Opsional — kosongkan untuk lokasi manual. --}}
            <div>
                <label class="block text-sm font-medium mb-2" for="assembly_id">Pilih Majelis (Opsional)</label>
                <select id="assembly_id" class="form-select w-full" name="assembly_id">
                    <option value="">Tidak Ada / Lokasi Manual</option>
                    @foreach($majelisList as $m)
                        <option value="{{ $m->id }}" {{ old('assembly_id', $acara?->assembly_id) == $m->id ? 'selected' : '' }}>{{ $m->nama_majelis }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Jika dipilih, lokasi akan mengikuti data Majelis.</p>
                @error('assembly_id')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Poster (Gambar)</label>
                @if($acara?->image)
                    <img src="{{ Storage::url($acara->image) }}" class="w-24 h-16 object-cover rounded mb-2">
                @endif
                <input class="form-input w-full" type="file" name="image" accept="image/*"/>
                @error('image')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            {{-- Nama lokasi manual (disembunyikan jika majelis dipilih) --}}
            <div id="location-wrapper">
                <label class="block text-sm font-medium mb-2" for="location">Nama Lokasi (Tempat) <span class="text-red-500">*</span></label>
                <input id="location" class="form-input w-full" type="text" name="location" value="{{ old('location', $acara?->location) }}" placeholder="Contoh: Masjid Raya"/>
                @error('location')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2" for="maps_link">Link Maps (Opsional)</label>
                <input id="maps_link" class="form-input w-full" type="url" name="maps_link" value="{{ old('maps_link', $acara?->maps_link) }}" placeholder="Contoh: https://goo.gl/maps/xyz"/>
                @error('maps_link')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

        </div>

        {{-- Alamat lengkap manual (disembunyikan jika majelis dipilih) --}}
        <div id="region-wrapper">
            <h2 class="text-lg text-gray-800 dark:text-gray-100 font-bold my-4">Alamat Lengkap</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2" for="province">Provinsi</label>
                    <select id="province" class="form-select w-full" name="province">
                        <option value="">Pilih Provinsi</option>
                        @foreach($provinces as $code => $name)
                            <option value="{{ $code }}" {{ old('province', $acara?->province_code) == $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" for="city">Kabupaten/Kota</label>
                    <select id="city" class="form-select w-full" name="city" data-selected="{{ old('city', $acara?->city_code) }}">
                        <option value="">Pilih Kabupaten/Kota</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" for="district">Kecamatan</label>
                    <select id="district" class="form-select w-full" name="district" data-selected="{{ old('district', $acara?->district_code) }}">
                        <option value="">Pilih Kecamatan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2" for="village">Desa/Kelurahan</label>
                    <select id="village" class="form-select w-full" name="village" data-selected="{{ old('village', $acara?->village_code) }}">
                        <option value="">Pilih Desa/Kelurahan</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="inherited-info" class="hidden mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
            <p class="text-blue-700 font-medium">Lokasi dan alamat akan otomatis diambil dari data Majelis yang dipilih.</p>
        </div>
    </div>

    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 sm:rounded-bl-md sm:rounded-br-md shadow">
        <x-button>Kirim Kontribusi</x-button>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {

        function toggleLocationFields() {
            if ($('#assembly_id').val()) {
                $('#location-wrapper').hide();
                $('#region-wrapper').hide();
                $('#inherited-info').removeClass('hidden');
                $('#location').removeAttr('required');
            } else {
                $('#location-wrapper').show();
                $('#region-wrapper').show();
                $('#inherited-info').addClass('hidden');
                $('#location').attr('required', 'required');
            }
        }

        toggleLocationFields();
        $('#assembly_id').on('change', toggleLocationFields);

        // Pre-load daftar wilayah tersimpan (mode edit / setelah validasi gagal)
        var provinceCode = $('#province').val();
        var selectedCity = $('#city').data('selected');
        var selectedDistrict = $('#district').data('selected');
        var selectedVillage = $('#village').data('selected');

        if (provinceCode) {
            $.getJSON('/get-cities/' + provinceCode, function(data) {
                $.each(data, function(code, name) {
                    $('#city').append('<option value="' + code + '">' + name + '</option>');
                });
                if (selectedCity) {
                    $('#city').val(selectedCity);
                    $.getJSON('/get-districts/' + selectedCity, function(data) {
                        $.each(data, function(code, name) {
                            $('#district').append('<option value="' + code + '">' + name + '</option>');
                        });
                        if (selectedDistrict) {
                            $('#district').val(selectedDistrict);
                            $.getJSON('/get-villages/' + selectedDistrict, function(data) {
                                $.each(data, function(code, name) {
                                    $('#village').append('<option value="' + code + '">' + name + '</option>');
                                });
                                if (selectedVillage) {
                                    $('#village').val(selectedVillage);
                                }
                            });
                        }
                    });
                }
            });
        }

        $('#province').on('change', function() {
            $('#city').empty().append('<option value="">Pilih Kabupaten/Kota</option>');
            $('#district').empty().append('<option value="">Pilih Kecamatan</option>');
            $('#village').empty().append('<option value="">Pilih Desa/Kelurahan</option>');

            if ($(this).val()) {
                $.getJSON('/get-cities/' + $(this).val(), function(data) {
                    $.each(data, function(code, name) {
                        $('#city').append('<option value="' + code + '">' + name + '</option>');
                    });
                });
            }
        });

        $('#city').on('change', function() {
            $('#district').empty().append('<option value="">Pilih Kecamatan</option>');
            $('#village').empty().append('<option value="">Pilih Desa/Kelurahan</option>');

            if ($(this).val()) {
                $.getJSON('/get-districts/' + $(this).val(), function(data) {
                    $.each(data, function(code, name) {
                        $('#district').append('<option value="' + code + '">' + name + '</option>');
                    });
                });
            }
        });

        $('#district').on('change', function() {
            $('#village').empty().append('<option value="">Pilih Desa/Kelurahan</option>');

            if ($(this).val()) {
                $.getJSON('/get-villages/' + $(this).val(), function(data) {
                    $.each(data, function(code, name) {
                        $('#village').append('<option value="' + code + '">' + name + '</option>');
                    });
                });
            }
        });
    });
</script>
