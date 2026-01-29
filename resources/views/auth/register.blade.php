<x-authentication-layout content-class="max-w-xl mx-auto w-full px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl text-emerald-900 dark:text-emerald-400 font-serif font-bold mb-2">Mari Bergabung</h1>
        <p class="text-gray-600 dark:text-gray-400">Daftarkan akun baru untuk menikmati fitur lengkap.</p>
    </div>
    <!-- Form -->
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div>
                <x-label for="name">{{ __('Nama Lengkap') }} <span class="text-red-500">*</span></x-label>
                <x-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" class="focus:border-emerald-500 focus:ring-emerald-500 w-full" />
            </div>

            <div>
                <x-label for="email">{{ __('Alamat Email') }} <span class="text-red-500">*</span></x-label>
                <x-input id="email" type="email" name="email" :value="old('email')" required class="focus:border-emerald-500 focus:ring-emerald-500" />
            </div>

            <div>
                <x-label for="password">Password <span class="text-red-500">*</span></x-label>
                <x-input id="password" type="password" name="password" required autocomplete="new-password" class="focus:border-emerald-500 focus:ring-emerald-500 w-full" />
            </div>

            <div>
                <x-label for="password_confirmation">Konfirmasi Password <span class="text-red-500">*</span></x-label>
                <x-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="focus:border-emerald-500 focus:ring-emerald-500 w-full" />
            </div>
        </div>

        <div class="mt-8 mb-4 border-t border-gray-200 dark:border-gray-700 pt-6">
            <h3 class="text-lg font-medium text-emerald-900 dark:text-emerald-400">Informasi Tambahan</h3>
            <p class="text-sm text-gray-500 mb-4">Informasi tambahan ini berguna untuk memberikan personalisasi fitur nantinya.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" x-data="registrationForm">
            @php
                $provinces = \Laravolt\Indonesia\Models\Province::whereIn('code', [62, 63, 64])->pluck('name', 'code');
            @endphp
            
            <div>
                <x-label for="province_code">{{ __('Provinsi') }} <span class="text-red-500">*</span></x-label>
                <select id="province_code" name="province_code" x-model="province" class="form-select border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm block w-full mt-1" required>
                    <option value="">{{ __('Pilih Provinsi') }}</option>
                    @foreach($provinces as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-label for="city_code">{{ __('Kabupaten/Kota') }} <span class="text-red-500">*</span></x-label>
                <select id="city_code" name="city_code" x-model="city" :disabled="!province || isLoadingCities" class="form-select border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm block w-full mt-1 disabled:opacity-50" required>
                    <option value="">{{ __('Pilih Kabupaten/Kota') }}</option>
                    <template x-for="(name, code) in cities" :key="code">
                        <option :value="code" x-text="name"></option>
                    </template>
                </select>
            </div>

            <div>
                <x-label for="district_code">{{ __('Kecamatan') }} <span class="text-red-500">*</span></x-label>
                <select id="district_code" name="district_code" x-model="district" :disabled="!city || isLoadingDistricts" class="form-select border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm block w-full mt-1 disabled:opacity-50" required>
                    <option value="">{{ __('Pilih Kecamatan') }}</option>
                     <template x-for="(name, code) in districts" :key="code">
                        <option :value="code" x-text="name"></option>
                    </template>
                </select>
            </div>

            <div>
                <x-label for="village_code">{{ __('Desa/Kelurahan') }} <span class="text-red-500">*</span></x-label>
                <select id="village_code" name="village_code" x-model="village" :disabled="!district || isLoadingVillages" class="form-select border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm block w-full mt-1 disabled:opacity-50" required>
                    <option value="">{{ __('Pilih Desa/Kelurahan') }}</option>
                     <template x-for="(name, code) in villages" :key="code">
                        <option :value="code" x-text="name"></option>
                    </template>
                </select>
            </div>
            
            <div>
                <x-label for="gender">{{ __('Jenis Kelamin') }} <span class="text-red-500">*</span></x-label>
                <select id="gender" name="gender" class="form-select border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm block w-full mt-1" required>
                    <option value="">{{ __('Pilih Jenis Kelamin') }}</option>
                    <option value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="Perempuan" {{ old('gender') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
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

        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white dark:bg-gray-900 text-gray-500">Atau daftar dengan</span>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                    <svg class="h-5 w-5 mr-2" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.84z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Google
                </a>
            </div>
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
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('registrationForm', () => ({
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
                    
                    this.$watch('province', value => {
                        this.fetchCities(value);
                        this.districts = [];
                        this.villages = [];
                        this.city = '';
                        this.district = '';
                        this.village = '';
                    });
                    this.$watch('city', value => {
                        this.fetchDistricts(value);
                        this.villages = [];
                        this.district = '';
                        this.village = '';
                    });
                    this.$watch('district', value => {
                        this.fetchVillages(value);
                        this.village = '';
                    });
                }
            }));
        });
    </script>
</x-authentication-layout>
