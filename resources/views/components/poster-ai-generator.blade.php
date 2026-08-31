@props([
    'quota',
    'eventId' => null,
    'styles' => \App\Services\GeminiPosterService::STYLES,
])

@if ($quota['eligible'])
    <div
        class="md:col-span-2 rounded-lg border border-violet-200 dark:border-violet-500/30 bg-violet-50/60 dark:bg-violet-500/10 p-4"
        x-data="{
            style: @js(array_key_first($styles)),
            eventId: @js($eventId),
            endpoint: @js(route('poster-acara.generate')),
            available: @js($quota['available']),
            remaining: @js($quota['remaining']),
            total: @js($quota['total']),
            loading: false,
            error: '',
            previewUrl: '',
            generationId: '',

            get canGenerate() {
                return this.available && this.remaining > 0 && ! this.loading;
            },

            async generate() {
                this.error = '';

                const form = this.$root.closest('form');
                const value = (name) => form?.querySelector(`[name='${name}']`)?.value ?? '';

                const payload = {
                    name: value('name'),
                    category: value('category'),
                    date: value('date'),
                    style: this.style,
                    assembly_id: value('assembly_id') || null,
                    event_id: this.eventId,
                };

                if (! payload.name || ! payload.category || ! payload.date) {
                    this.error = 'Isi dulu Nama Acara, Kategori, dan Tanggal sebelum membuat poster.';
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch(this.endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (! response.ok) {
                        this.error = data.message || 'Pembuatan poster gagal. Silakan coba lagi beberapa saat atau unggah poster sendiri.';
                        if (data.quota) {
                            this.remaining = data.quota.remaining;
                            this.total = data.quota.total;
                        }
                        return;
                    }

                    this.previewUrl = data.preview_url;
                    this.generationId = data.generation_id;
                    this.remaining = data.quota.remaining;
                    this.total = data.quota.total;
                } catch (e) {
                    this.error = 'Tidak dapat terhubung ke server. Periksa koneksi lalu coba lagi.';
                } finally {
                    this.loading = false;
                }
            },
        }"
    >
        <input type="hidden" name="generation_id" :value="generationId" />

        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Buat Poster dengan AI</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Poster dibuat dari Nama Acara, Kategori, dan Tanggal yang Anda isi di atas. Ukuran potret 9:16, siap dijadikan status WhatsApp.
                </p>
            </div>
            <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400" x-show="available">
                Sisa kuota: <span class="font-semibold" x-text="remaining"></span> dari <span x-text="total"></span>
            </span>
        </div>

        <template x-if="! available">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Fitur pembuatan poster sedang tidak tersedia. Silakan unggah poster sendiri.
            </p>
        </template>

        <template x-if="available">
            <div>
                <label class="block text-sm font-medium mb-2">Gaya Visual</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                    @foreach ($styles as $key => $label)
                        <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition text-sm"
                            :class="style === @js($key)
                                ? 'border-violet-500 bg-white dark:bg-gray-800 text-violet-600 dark:text-violet-400'
                                : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-gray-300'">
                            <input type="radio" class="form-radio" value="{{ $key }}" x-model="style" />
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="button"
                        class="btn bg-violet-500 hover:bg-violet-600 text-white disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="! canGenerate"
                        @click="generate()">
                        <svg x-show="loading" class="animate-spin w-4 h-4 mr-2 fill-current shrink-0" viewBox="0 0 16 16">
                            <path d="M8 16a7.928 7.928 0 0 1-3.428-.77l.857-1.807A6.006 6.006 0 0 0 14 8c0-3.309-2.691-6-6-6a6.006 6.006 0 0 0-5.422 8.572l-1.806.859A7.929 7.929 0 0 1 0 8c0-4.411 3.589-8 8-8s8 3.589 8 8-3.589 8-8 8Z" />
                        </svg>
                        <span x-text="previewUrl ? 'Buat Ulang' : 'Buat Poster'"></span>
                    </button>

                    <p class="text-xs text-gray-500 dark:text-gray-400" x-show="loading">
                        Sedang membuat poster, mohon tunggu hingga 1 menit&hellip;
                    </p>

                    <p class="text-xs text-gray-500 dark:text-gray-400" x-show="! loading && remaining < 1">
                        Kuota pembuatan poster bulan ini sudah habis (<span x-text="total"></span> dari <span x-text="total"></span>).
                        Kuota diperbarui setiap awal bulan. Anda tetap dapat mengunggah poster sendiri.
                    </p>
                </div>

                <div class="mt-3 p-3 text-xs text-red-800 bg-red-50 border border-red-200 rounded-lg" role="alert"
                    x-show="error" x-text="error"></div>

                <div class="mt-4" x-show="previewUrl">
                    <img :src="previewUrl" alt="Pratinjau poster" class="w-40 rounded-lg border border-gray-200 dark:border-gray-700" />
                    <p class="text-xs text-amber-700 dark:text-amber-500 mt-2 max-w-lg">
                        Periksa ejaan nama, tanggal, dan tulisan Arab pada poster sebelum menyimpan. Hasil AI dapat keliru menulis teks.
                        Poster baru tersimpan setelah Anda menekan tombol Simpan.
                    </p>
                </div>
            </div>
        </template>
    </div>
@endif
