<div class="grow">
    <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Panel body -->
        <div class="p-6 space-y-6">
            <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">Profil Saya</h2>

            @if (session('status') === 'profile-updated')
                <div class="mb-4 bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">Profil berhasil diperbarui.</span>
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
                        <img class="w-20 h-20 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" width="80" height="80" alt="{{ Auth::user()->name }}" />
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
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="name">Nama Lengkap</label>
                        <input id="name" name="name" class="form-input w-full" type="text" value="{{ old('name', Auth::user()->name) }}" />
                    </div>
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="email">Alamat Email</label>
                        <input id="email" name="email" class="form-input w-full" type="email" value="{{ old('email', Auth::user()->email) }}" />
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
                            <option value="">Pilih Kabupaten/Kota</option>
                            <template x-for="(name, code) in cities" :key="code">
                                <option :value="code" x-text="name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="district">Kecamatan</label>
                        <select id="district" class="form-select w-full" name="district_code" x-model="district">
                            <option value="">Pilih Kecamatan</option>
                            <template x-for="(name, code) in districts" :key="code">
                                <option :value="code" x-text="name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="village">Desa/Kelurahan</label>
                        <select id="village" class="form-select w-full" name="village_code" x-model="village">
                            <option value="">Pilih Desa/Kelurahan</option>
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
                <div class="mt-5 space-y-4">
                    <div>
                         <label class="block text-sm font-medium mb-1" for="current_password">Password Saat Ini</label>
                         <input id="current_password" name="current_password" class="form-input w-full md:w-1/2" type="password" autocomplete="current-password" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="password">Password Baru</label>
                        <input id="password" name="password" class="form-input w-full md:w-1/2" type="password" autocomplete="new-password" />
                    </div>
                </div>
            </section>

            <!-- Daily Surah Reading Preference -->
            <livewire:settings.toggle-daily-surah />
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
                cities: [],
                districts: [],
                villages: [],

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