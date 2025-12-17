<section>
    <h3 class="text-xl leading-snug text-gray-800 dark:text-gray-100 font-bold mb-1">Bacaan Sholat Harian</h3>
    <div class="text-sm">Tampilkan rekomendasi bacaan surah sholat lima waktu di dashboard anda.</div>
    <div class="flex items-center mt-5">
        <div class="form-switch">
            <input type="checkbox" id="toggle-daily-surah" class="sr-only" wire:model.live="enabled" />
            <label for="toggle-daily-surah">
                <span class="bg-white shadow-xs" aria-hidden="true"></span>
                <span class="sr-only">Aktifkan tampilan bacaan surah</span>
            </label>
        </div>
        <div class="text-sm text-gray-400 dark:text-gray-500 italic ml-2">
            {{ $enabled ? 'Aktif' : 'Nonaktif' }}
        </div>
    </div>
</section>
