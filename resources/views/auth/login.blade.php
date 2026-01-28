<x-authentication-layout>
    @if (session('message'))
        <div x-show="open" x-data="{ open: true }" role="alert" class="mb-8">
            <div class="inline-flex min-w-80 px-4 py-2 rounded-lg text-sm bg-violet-500 text-white">
                <div class="flex w-full justify-between items-start">
                    <div class="flex">
                        <svg class="shrink-0 fill-current opacity-80 mt-[3px] mr-3" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                        </svg>
                        <div class="font-medium">{{ session('message') }}</div>
                    </div>
                    <button class="opacity-60 hover:opacity-70 ml-3 mt-[3px]" @click="open = false">
                        <div class="sr-only">Close</div>
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif
    <div class="mb-8">
        <h1 class="text-3xl text-emerald-900 dark:text-emerald-400 font-serif font-bold mb-2">Assalamu'alaikum</h1>
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
