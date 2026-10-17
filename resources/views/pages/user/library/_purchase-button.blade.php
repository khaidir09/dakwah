{{-- Tombol beli pustaka berbayar untuk pengguna yang belum punya akses. --}}
@auth
    @if($library->purchases()->where('user_id', auth()->id())->where('status', 'pending')->exists())
        <button disabled class="btn w-full bg-yellow-100 text-yellow-800 cursor-not-allowed">
            Menunggu verifikasi pembayaran
        </button>
    @else
        <form method="POST" action="{{ route('pustaka-purchase', $library) }}">
            @csrf
            <button type="submit" class="btn w-full bg-indigo-500 hover:bg-indigo-600 text-white">
                @if($library->show_price)
                    Beli (Rp {{ number_format($library->price, 0, ',', '.') }})
                @else
                    Beli
                @endif
            </button>
        </form>
        @unless($library->show_price)
            <p class="text-xs text-gray-400 mt-2 text-center">Harga diinformasikan via WhatsApp.</p>
        @endunless
    @endif
@else
    <a href="{{ route('login') }}" class="btn w-full bg-indigo-500 hover:bg-indigo-600 text-white">
        <span>Login untuk Membeli</span>
    </a>
@endauth
