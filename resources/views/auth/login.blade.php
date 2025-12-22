<x-authentication-layout>
    <div class="mb-8">
        <h1 class="text-3xl text-emerald-900 dark:text-emerald-400 font-serif font-bold mb-2">Ahlan Wa Sahlan</h1>
        <p class="text-gray-600 dark:text-gray-400">Silahkan masuk untuk mengakses layanan Syaikhuna.</p>
    </div>

    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-emerald-600">
            {{ session('status') }}
        </div>
    @endif   
    <!-- Form -->
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" name="email" :value="old('email')" required autofocus class="focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
            <div>
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" type="password" name="password" required autocomplete="current-password" class="focus:border-emerald-500 focus:ring-emerald-500" />
            </div>
        </div>
        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <div class="mr-1">
                    <a class="text-sm text-emerald-600 hover:text-emerald-700 underline hover:no-underline" href="{{ route('password.request') }}">
                        {{ __('Lupa Password?') }}
                    </a>
                </div>
            @endif            
            <x-button class="ml-3 bg-emerald-600 hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900">
                {{ __('Masuk') }}
            </x-button>            
        </div>
    </form>
    <x-validation-errors class="mt-4" />   
    <!-- Footer -->
    <div class="pt-5 mt-6 border-t border-gray-100 dark:border-gray-700/60">
        <div class="text-sm">
            {{ __('Belum punya akun?') }} <a class="font-medium text-emerald-600 hover:text-emerald-700 dark:hover:text-emerald-400" href="{{ route('register') }}">{{ __('Daftar Sekarang') }}</a>
        </div>
    </div>
</x-authentication-layout>
