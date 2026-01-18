<x-authentication-layout>
    <div class="mb-8">
        <h1 class="text-3xl text-emerald-900 dark:text-emerald-400 font-serif font-bold mb-2">{{ __('Verifikasi Email Anda') }}</h1>
        <p class="text-gray-600 dark:text-gray-400">
            {{ __('Terima kasih telah mendaftar! Sebelum memulai, bisakah Anda memverifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan kepada Anda? Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan yang baru.') }}
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-emerald-600">
            {{ __('Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.') }}
        </div>
    @endif

    <div class="mt-6 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div>
                <x-button type="submit" class="bg-emerald-600 hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900">
                    {{ __('Kirim Ulang Email Verifikasi') }}
                </x-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <div class="ml-1">
                <button type="submit" class="text-sm underline hover:no-underline text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                    {{ __('Keluar') }}
                </button>
            </div>
        </form>   
    </div>
</x-authentication-layout>
