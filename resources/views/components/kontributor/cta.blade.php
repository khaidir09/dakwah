{{-- Ajakan menjadi kontributor. Disembunyikan untuk pengguna yang sudah ber-role Kontributor. --}}
@unless(auth()->check() && auth()->user()->hasRole('Kontributor'))
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3']) }}>
        <div>
            <h3 class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Punya data guru, majelis, amalan atau catatan pengajian?</h3>
            <p class="text-sm text-emerald-700/80 dark:text-emerald-400/80">Jadilah Kontributor Syaikhuna dan bantu memperkaya data dakwah di Kalimantan.</p>
        </div>
        <a href="{{ route('kontributor.index') }}" class="btn bg-emerald-500 hover:bg-emerald-600 text-white whitespace-nowrap shrink-0">
            Program Kontributor
        </a>
    </div>
@endunless
