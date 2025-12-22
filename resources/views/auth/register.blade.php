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
