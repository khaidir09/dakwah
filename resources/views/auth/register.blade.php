<x-authentication-layout>
    <div class="mb-8">
        <h1 class="text-3xl text-emerald-900 dark:text-emerald-400 font-serif font-bold mb-2">Mari Bergabung</h1>
        <p class="text-gray-600 dark:text-gray-400">Daftarkan akun baru untuk menikmati fitur lengkap.</p>
    </div>
    <!-- Form -->
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <x-label for="name">{{ __('Nama Lengkap') }} <span class="text-red-500">*</span></x-label>
                <x-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" class="focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div>
                <x-label for="email">{{ __('Alamat Email') }} <span class="text-red-500">*</span></x-label>
                <x-input id="email" type="email" name="email" :value="old('email')" required class="focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div>
                <x-label for="password">Password <span class="text-red-500">*</span></x-label>
                <x-input id="password" type="password" name="password" required autocomplete="new-password" class="focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div>
                <x-label for="password_confirmation">Konfirmasi Password <span class="text-red-500">*</span></x-label>
                <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
        </div>

        <div class="mt-8 mb-4 border-t border-gray-200 dark:border-gray-700 pt-6">
            <h3 class="text-lg font-medium text-emerald-900 dark:text-emerald-400">Informasi Tambahan</h3>
            <p class="text-sm text-gray-500 mb-4">Informasi tambahan ini berguna untuk memberikan personalisasi fitur nantinya.</p>
        </div>

        <div class="space-y-4" x-data="{
            province: '{{ old('province_code') }}',
            city: '{{ old('city_code') }}',
            district: '{{ old('district_code') }}',
            village: '{{ old('village_code') }}',
            cities: [],
            districts: [],
            villages: [],
            isLoadingCities: false,
            isLoadingDistricts: false,
            isLoadingVillages: false,

            async fetchCities(provinceCode) {
                if (!provinceCode) return;
                this.isLoadingCities = true;
                this.cities = [];
                try {
                    const url = "{{ route('get-cities', ':code') }}".replace(':code', provinceCode);
                    const response = await fetch(url);
                    this.cities = await response.json();
                } catch (e) { console.error(e); }
                this.isLoadingCities = false;
            },
            async fetchDistricts(cityCode) {
                if (!cityCode) return;
                this.isLoadingDistricts = true;
                this.districts = [];
                try {
                    const url = "{{ route('get-districts', ':code') }}".replace(':code', cityCode);
                    const response = await fetch(url);
                    this.districts = await response.json();
                } catch (e) { console.error(e); }
                this.isLoadingDistricts = false;
            },
            async fetchVillages(districtCode) {
                if (!districtCode) return;
                this.isLoadingVillages = true;
                this.villages = [];
                try {
                    const url = "{{ route('get-villages', ':code') }}".replace(':code', districtCode);
                    const response = await fetch(url);
                    this.villages = await response.json();
                } catch (e) { console.error(e); }
                this.isLoadingVillages = false;
            },

            init() {
                if(this.province) this.fetchCities(this.province).then(() => {
                    this.city = '{{ old('city_code') }}';
                    if(this.city) this.fetchDistricts(this.city).then(() => {
                        this.district = '{{ old('district_code') }}';
                         if(this.district) this.fetchVillages(this.district).then(() => {
                            this.village = '{{ old('village_code') }}';
                         });
                    });
                });

                $watch('province', value => {
                    this.fetchCities(value);
                    this.districts = [];
                    this.villages = [];
                    this.city = '';
                    this.district = '';
                    this.village = '';
                });
                $watch('city', value => {
                    this.fetchDistricts(value);
                    this.villages = [];
                    this.district = '';
                    this.village = '';
                });
                $watch('district', value => {
                    this.fetchVillages(value);
                    this.village = '';
                });
            }
        }">
            @php
                $provinces = \Laravolt\Indonesia\Models\Province::pluck('name', 'code');
            @endphp

            <div>
                <x-label for="province_code">{{ __('Provinsi') }} <span class="text-red-500">*</span></x-label>
                <select id="province_code" name="province_code" x-model="province" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm block w-full mt-1" required>
                    <option value="">{{ __('Pilih Provinsi') }}</option>
                    @foreach($provinces as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="city_code">{{ __('Kabupaten/Kota') }} <span class="text-red-500">*</span></x-label>
                <select id="city_code" name="city_code" x-model="city" :disabled="!province || isLoadingCities" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm block w-full mt-1 disabled:opacity-50" required>
                    <option value="">{{ __('Pilih Kabupaten/Kota') }}</option>
                    <template x-for="(name, code) in cities" :key="code">
                        <option :value="code" x-text="name"></option>
                    </template>
                </select>
            </div>

            <div>
                <x-label for="district_code">{{ __('Kecamatan') }} <span class="text-red-500">*</span></x-label>
                <select id="district_code" name="district_code" x-model="district" :disabled="!city || isLoadingDistricts" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm block w-full mt-1 disabled:opacity-50" required>
                    <option value="">{{ __('Pilih Kecamatan') }}</option>
                     <template x-for="(name, code) in districts" :key="code">
                        <option :value="code" x-text="name"></option>
                    </template>
                </select>
            </div>

            <div>
                <x-label for="village_code">{{ __('Desa/Kelurahan') }} <span class="text-red-500">*</span></x-label>
                <select id="village_code" name="village_code" x-model="village" :disabled="!district || isLoadingVillages" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm block w-full mt-1 disabled:opacity-50" required>
                    <option value="">{{ __('Pilih Desa/Kelurahan') }}</option>
                     <template x-for="(name, code) in villages" :key="code">
                        <option :value="code" x-text="name"></option>
                    </template>
                </select>
            </div>

            <div>
                <x-label for="gender">{{ __('Jenis Kelamin') }} <span class="text-red-500">*</span></x-label>
                <select id="gender" name="gender" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm block w-full mt-1" required>
                    <option value="">{{ __('Pilih Jenis Kelamin') }}</option>
                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>

            <div>
                <x-label for="birth_year">{{ __('Tahun Lahir') }} <span class="text-red-500">*</span></x-label>
                <x-input id="birth_year" type="number" name="birth_year" :value="old('birth_year')" required min="1900" max="{{ date('Y') }}" class="focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: 1990" />
            </div>

        </div>
        <div class="flex items-center justify-end mt-6">
            <x-button class="bg-emerald-600 hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900">
                {{ __('Daftar') }}
            </x-button>                
        </div>
            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-6">
                    <label class="flex items-start">
                        <input type="checkbox" class="form-checkbox mt-1 text-emerald-600 focus:border-emerald-500 focus:ring-emerald-500" name="terms" id="terms" />
                        <span class="text-sm ml-2">
                            {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="text-sm underline hover:no-underline text-emerald-600">'.__('Terms of Service').'</a>',
                                'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="text-sm underline hover:no-underline text-emerald-600">'.__('Privacy Policy').'</a>',
                            ]) !!}                        
                        </span>
                    </label>
                </div>
            @endif        
    </form>
    <x-validation-errors class="mt-4" />  
    <!-- Footer -->
    <div class="pt-5 mt-6 border-t border-gray-100 dark:border-gray-700/60">
        <div class="text-sm">
            {{ __('Sudah punya akun?') }} <a class="font-medium text-emerald-600 hover:text-emerald-700 dark:hover:text-emerald-400" href="{{ route('login') }}">{{ __('Masuk Sekarang') }}</a>
        </div>
    </div>
</x-authentication-layout>
