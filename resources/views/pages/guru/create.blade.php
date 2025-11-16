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

        <div>
            <form action="{{ route('guru.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
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
                            <label class="block text-sm font-medium mb-2" for="name">Nama Guru <span class="text-red-500">*</span></label>
                            <input id="name" class="form-input w-full @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" required/>
                            @error('name')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="domisili">Domisili <span class="text-red-500">*</span></label>
                            <input id="domisili" class="form-input w-full @error('domisili') is-invalid @enderror" type="text" name="domisili" value="{{ old('domisili') }}" required/>
                            @error('domisili')
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

                    <div class="grid grid-cols-1 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="biografi">Biografi <span class="text-red-500">*</span></label>
                            <textarea class="form-input w-full @error('biografi') is-invalid @enderror" name="biografi" id="biografi" cols="30" rows="10" required>{{ old('biografi') }}</textarea>
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
                                <option>==Pilih Salah Satu==</option>
                                @foreach($provinces as $code => $name)
                                    <option value="{{ $code ?? '' }}">{{ $name ?? '' }}</option>
                                @endforeach
                            </select>
                            @error('province')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="city">Kabupaten/Kota <span class="text-red-500">*</span></label>
                            <select id="city" class="form-select w-full @error('city') is-invalid @enderror" name="city" required>
                                <option>==Pilih Salah Satu==</option>
                            </select>
                            @error('city')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-2" for="district">Kecamatan</label>
                            <select id="district" class="form-select w-full" name="district">
                                <option>==Pilih Salah Satu==</option>
                            </select>
                        </div>
                        
                        <div>
                            {{-- Ubah 'for' dari "desa" menjadi "village" --}}
                            <label class="block text-sm font-medium mb-2" for="village">Desa/Kelurahan</label>
                            {{-- Ubah 'id' dari "desa" menjadi "village" --}}
                            {{-- Ubah 'name' dari "desa" menjadi "village" --}}
                            <select id="village" class="form-select w-full" name="village">
                                <option>==Pilih Salah Satu==</option>
                            </select>
                        </div>
                    </div>

                    <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold my-4">Tanggal Wafat</h2>
                    <div class="grid grid-cols-4 gap-4 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="wafat_masehi">Wafat Masehi</label>
                            <input id="wafat_masehi" class="form-input w-full @error('wafat_masehi') is-invalid @enderror" type="number" name="wafat_masehi" value="{{ old('wafat_masehi') }}"/>
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
</x-app-layout>

@push('scripts')
    <script>
        // Menunggu DOM siap
        document.addEventListener("DOMContentLoaded", function() {
            
            // Ambil elemen dropdown
            const provinceSelect = document.getElementById('province');
            const citySelect = document.getElementById('city');
            const districtSelect = document.getElementById('district');

            // Fungsi untuk mengosongkan dan mengisi ulang dropdown
            function populateSelect(selectElement, items) {
                // Kosongkan
                selectElement.innerHTML = ''; 
                
                // Tambah opsi default
                const defaultOption = document.createElement('option');
                defaultOption.text = '==Pilih Salah Satu==';
                defaultOption.value = '';
                selectElement.add(defaultOption);

                // Isi dengan data baru
                Object.entries(items).forEach(([code, name]) => {
                    const option = document.createElement('option');
                    option.text = name;
                    option.value = code;
                    selectElement.add(option);
                });
            }

            // Fungsi untuk mengosongkan select
            function clearSelect(selectElement) {
                selectElement.innerHTML = '<option>==Pilih Salah Satu==</option>';
            }

            // 1. Pasang Event Listener ke Provinsi
            if (provinceSelect) {
                provinceSelect.addEventListener('change', function () {
                    const provinceCode = this.value;

                    // Kosongkan kota dan kecamatan
                    clearSelect(citySelect);
                    clearSelect(districtSelect);
                    
                    if (provinceCode) {
                        // Panggil route 'cities' menggunakan POST
                        axios.post('{{ route("cities") }}', { code: provinceCode })
                            .then(function (response) {
                                // Isi dropdown kota
                                populateSelect(citySelect, response.data);
                            })
                            .catch(function (error) {
                                console.error('Error memuat kota:', error);
                            });
                    }
                });
            }

            // 2. Pasang Event Listener ke Kota
            if (citySelect) {
                citySelect.addEventListener('change', function () {
                    const cityCode = this.value;

                    // Kosongkan kecamatan
                    clearSelect(districtSelect);

                    if (cityCode) {
                        // Panggil route 'districts' menggunakan POST
                        axios.post('{{ route("districts") }}', { code: cityCode })
                            .then(function (response) {
                                // Isi dropdown kecamatan
                                populateSelect(districtSelect, response.data);
                            })
                            .catch(function (error) {
                                console.error('Error memuat kecamatan:', error);
                            });
                    }
                });
            }

        });
    </script>
@endpush