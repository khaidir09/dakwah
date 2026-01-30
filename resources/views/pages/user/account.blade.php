<x-dashboard-layout>
    <div class="grow">
        <form action="{{ route('pengaturan-akun.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- Panel body -->
            <div class="p-6 space-y-6">
                <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">Profil Saya</h2>

                @if (session('incomplete_profile'))
                    <div role="alert">
                        <div class="mb-4 px-4 py-2 rounded-lg text-sm bg-yellow-500 text-white">
                            <div class="flex w-full justify-between items-start">
                                <div class="flex">
                                    <svg class="shrink-0 fill-current opacity-80 mt-[3px] mr-3" width="16" height="16" viewBox="0 0 16 16">
                                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM7 11.4L3.6 8 5 6.6l2 2 4-4L12.4 6 7 11.4z" />
                                    </svg>
                                    <div class="font-medium">Silahkan lengkapi data informasi seperti Domisili, Jenis Kelamin, Tahun Lahir agar sistem dapat melakukan personalisasi.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('status') === 'profile-updated')
                    <div role="alert">
                        <div class="mb-4 px-4 py-2 rounded-lg text-sm bg-green-500 text-white">
                            <div class="flex w-full justify-between items-start">
                                <div class="flex">
                                    <svg class="shrink-0 fill-current opacity-80 mt-[3px] mr-3" width="16" height="16" viewBox="0 0 16 16">
                                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM7 11.4L3.6 8 5 6.6l2 2 4-4L12.4 6 7 11.4z" />
                                    </svg>
                                    <div class="font-medium">Profil berhasil diperbarui.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
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

                <!-- Picture -->
                <section>
                    <div class="flex items-center">
                        <div class="mr-4">
                            @if (Auth::user()->profile_photo_path != null)
                                <img class="w-20 h-20 rounded-full object-cover" src="{{ Storage::url(Auth::user()->profile_photo_path) }}" width="80" height="80" alt="{{ Auth::user()->name }}" />
                            @else
                                <img class="w-20 h-20 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" width="80" height="80" alt="{{ Auth::user()->name }}" />
                            @endif
                        </div>
                        <label class="btn-sm dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300 cursor-pointer">
                            <span>Ganti Foto Profil</span>
                            <input type="file" name="photo" class="hidden" accept="image/*" />
                        </label>
                    </div>
                </section>

                <!-- General Info -->
                <section>
                    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                        <div class="sm:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="name">Nama Lengkap</label>
                            <input id="name" name="name" class="form-input w-full" type="text" value="{{ old('name', Auth::user()->name) }}" />
                        </div>
                        <div class="sm:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="email">Alamat Email</label>
                            <input id="email" name="email" class="form-input w-full" type="email" value="{{ old('email', Auth::user()->email) }}" />
                        </div>
                        <div class="sm:w-1/3">
                            <label class="block text-sm font-medium mb-1" for="phone">Nomor Telepon/WA</label>
                            <input id="phone" name="phone" class="form-input w-full" type="number" placeholder="Awali dengan 62 untuk pengganti 0." value="{{ old('phone', Auth::user()->phone) }}" />
                        </div>
                    </div>
                </section>

                <!-- Data Personal -->
                <section>
                    <h3 class="text-xl leading-snug text-gray-800 dark:text-gray-100 font-bold mb-1 mt-6">Data Personal</h3>
                    <div class="text-sm mb-4">
                        Lengkapi data personal anda.
                    </div>
                    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                        <div class="sm:w-1/2">
                            <label class="block text-sm font-medium mb-1" for="gender">Jenis Kelamin</label>
                            <select id="gender" name="gender" class="form-select w-full">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki" {{ old('gender', Auth::user()->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ old('gender', Auth::user()->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="sm:w-1/2">
                            <label class="block text-sm font-medium mb-1" for="birth_year">Tahun Lahir</label>
                            <input id="birth_year" name="birth_year" class="form-input w-full" type="number" min="1900" max="{{ date('Y') }}" value="{{ old('birth_year', Auth::user()->birth_year) }}" />
                        </div>
                    </div>
                </section>

                <!-- Domisili -->
                <section x-data="regionalDropdowns({
                    province: '{{ old('province_code', Auth::user()->province_code) }}',
                    city: '{{ old('city_code', Auth::user()->city_code) }}',
                    district: '{{ old('district_code', Auth::user()->district_code) }}',
                    village: '{{ old('village_code', Auth::user()->village_code) }}'
                })">
                    <h3 class="text-xl leading-snug text-gray-800 dark:text-gray-100 font-bold mb-1 mt-6">Domisili</h3>
                    <div class="text-sm mb-4">
                        Lengkapi data domisili anda.
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="province">Provinsi</label>
                            <select id="province" class="form-select w-full" name="province_code" x-model="province">
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinces as $code => $name)
                                    <option value="{{ $code }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="city">Kabupaten/Kota</label>
                            <select id="city" class="form-select w-full" name="city_code" x-model="city">
                            <option value="{{ Auth::user()->city_code }}">{{ Auth::user()->city_code ? Auth::user()->city->name : 'Pilih Kabupaten/Kota'}}</option>
                                <template x-for="(name, code) in cities" :key="code">
                                    <option :value="code" x-text="name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="district">Kecamatan</label>
                            <select id="district" class="form-select w-full" name="district_code" x-model="district">
                                <option value="{{ Auth::user()->district_code }}">{{ Auth::user()->district_code ? Auth::user()->district->name : 'Pilih Kecamatan'}}</option>
                                <template x-for="(name, code) in districts" :key="code">
                                    <option :value="code" x-text="name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="village">Desa/Kelurahan</label>
                            <select id="village" class="form-select w-full" name="village_code" x-model="village">
                                <option value="{{ Auth::user()->village_code }}">{{ Auth::user()->village_code ? Auth::user()->village->name : 'Pilih Desa/Kelurahan'}}</option>
                                <template x-for="(name, code) in villages" :key="code">
                                    <option :value="code" x-text="name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- Password -->
                <section>
                    <h3 class="text-xl leading-snug text-gray-800 dark:text-gray-100 font-bold mb-1 mt-6">Password</h3>
                    <div class="text-sm">
                        Kosongkan jika tidak ingin mengubah password.
                    </div>
                    <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                        <div class="sm:w-1/2">
                            <label class="block text-sm font-medium mb-1" for="current_password">Password Saat Ini</label>
                            <input id="current_password" name="current_password" class="form-input w-full" type="password" autocomplete="current-password" />
                        </div>
                        <div class="sm:w-1/2">
                            <label class="block text-sm font-medium mb-1" for="password">Password Baru</label>
                            <input id="password" name="password" class="form-input w-full" type="password" autocomplete="new-password" />
                        </div>
                    </div>
                </section>
            </div>

            <!-- Panel footer -->
            <footer>
                <div class="flex flex-col px-6 py-5 border-t border-gray-200 dark:border-gray-700/60">
                    <div class="flex self-end">
                        <button type="submit" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white ml-3">Simpan Perubahan</button>
                    </div>
                </div>
            </footer>
        </form>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('regionalDropdowns', (initialData) => ({
                    province: initialData.province,
                    city: initialData.city,
                    district: initialData.district,
                    village: initialData.village,
                    cities: initialData.city ? { [initialData.city]: 'Loading...' } : {},
                    districts: initialData.district ? { [initialData.district]: 'Loading...' } : {},
                    villages: initialData.village ? { [initialData.village]: 'Loading...' } : {},

                    init() {
                        // Initial load chain
                        if (this.province) {
                            this.fetchCities(this.province).then(() => {
                                if (this.city) {
                                    this.fetchDistricts(this.city).then(() => {
                                        if (this.district) {
                                            this.fetchVillages(this.district);
                                        }
                                    });
                                }
                            });
                        }

                        // Watchers
                        this.$watch('province', (value) => {
                            this.city = '';
                            this.district = '';
                            this.village = '';
                            this.cities = [];
                            this.districts = [];
                            this.villages = [];
                            if (value) this.fetchCities(value);
                        });

                        this.$watch('city', (value) => {
                            this.district = '';
                            this.village = '';
                            this.districts = [];
                            this.villages = [];
                            if (value) this.fetchDistricts(value);
                        });

                        this.$watch('district', (value) => {
                            this.village = '';
                            this.villages = [];
                            if (value) this.fetchVillages(value);
                        });
                    },

                    async fetchCities(provinceCode) {
                        try {
                            let response = await fetch(`/get-cities/${provinceCode}`);
                            this.cities = await response.json();
                        } catch (e) {
                            console.error('Error fetching cities', e);
                        }
                    },

                    async fetchDistricts(cityCode) {
                        try {
                            let response = await fetch(`/get-districts/${cityCode}`);
                            this.districts = await response.json();
                        } catch (e) {
                            console.error('Error fetching districts', e);
                        }
                    },

                    async fetchVillages(districtCode) {
                        try {
                            let response = await fetch(`/get-villages/${districtCode}`);
                            this.villages = await response.json();
                        } catch (e) {
                            console.error('Error fetching villages', e);
                        }
                    }
                }));
            });
        </script>
    </div>
</x-user-layout>
