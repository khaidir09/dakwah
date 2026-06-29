<x-user-layout>
    @section('title', $library->title)

    @push('styles')
        <style>
            /* Cegah pencetakan halaman penampil dokumen. */
            @media print {
                body { display: none !important; }
            }
            #pdf-viewer, #pdf-viewer canvas {
                -webkit-user-select: none;
                user-select: none;
            }
            /* Mode baca per-halaman: sembunyikan halaman selain yang aktif. */
            #pdf-viewer.is-paged #pdf-pages > .pdf-page:not(.is-current) {
                display: none;
            }
            #pdf-viewer.is-paged #pdf-pages {
                display: flex;
                align-items: flex-start;
                justify-content: center;
            }
        </style>
    @endpush

    <div class="px-4 sm:px-6 lg:px-8 py-6 w-full max-w-6xl mx-auto">
        <div class="mb-3 flex items-center justify-between">
            <a href="{{ route('pustaka-detail', $library) }}" class="text-sm font-medium text-indigo-500 hover:text-indigo-600 flex items-center">
                <svg class="w-3 h-3 fill-current mr-2" viewBox="0 0 12 12">
                    <path d="M5.4 10.6L.8 6l4.6-4.6L6.8 2.8 3.6 6l3.2 3.2z" />
                </svg>
                <span>Kembali ke Detail</span>
            </a>
            <span class="text-xs text-gray-400 hidden sm:inline">Dokumen dilindungi &mdash; baca online saja</span>
        </div>

        <h1 class="text-lg md:text-xl font-bold text-gray-800 dark:text-gray-100 mb-4 truncate">{{ $library->title }}</h1>

        <div id="pdf-viewer"
             data-src="{{ route('pustaka-stream', $library) }}"
             class="flex flex-col h-[calc(100vh-11rem)] min-h-[420px] bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-700/60 rounded-xl overflow-hidden">

            {{-- Toolbar --}}
            <div class="shrink-0 flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-2 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700/60">
                <button type="button" id="pdf-toc-toggle" title="Daftar Isi"
                        class="hidden p-1.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/60">
                    <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M3 5h18v2H3V5zm0 6h18v2H3v-2zm0 6h18v2H3v-2z"/></svg>
                </button>

                <div class="flex items-center gap-1 ml-auto">
                    <button type="button" id="pdf-prev" title="Halaman sebelumnya"
                            class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/60 disabled:opacity-40">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M15.4 7.4 14 6l-6 6 6 6 1.4-1.4-4.6-4.6z"/></svg>
                    </button>
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                        <input type="number" id="pdf-page-input" min="1" value="1"
                               class="w-12 text-center form-input px-1 py-0.5 text-sm" />
                        <span class="mx-1 text-gray-400">/</span>
                        <span id="pdf-page-count">–</span>
                    </div>
                    <button type="button" id="pdf-next" title="Halaman berikutnya"
                            class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/60 disabled:opacity-40">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="m8.6 16.6 1.4 1.4 6-6-6-6-1.4 1.4 4.6 4.6z"/></svg>
                    </button>
                </div>

                <div class="flex items-center gap-1 ml-auto">
                    <button type="button" id="pdf-zoom-out" title="Perkecil"
                            class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/60">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M5 11h14v2H5z"/></svg>
                    </button>
                    <span id="pdf-zoom-label" class="text-xs text-gray-500 w-10 text-center tabular-nums">100%</span>
                    <button type="button" id="pdf-zoom-in" title="Perbesar"
                            class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/60">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/></svg>
                    </button>
                    <button type="button" id="pdf-fit" title="Sesuaikan lebar/halaman"
                            class="hidden sm:inline-flex p-1.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/60">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M9 3H3v6h2V5h4V3zm12 0h-6v2h4v4h2V3zM5 15H3v6h6v-2H5v-4zm16 0h-2v4h-4v2h6v-6z"/></svg>
                    </button>
                    <button type="button" id="pdf-mode" title="Mode baca (scroll / per-halaman)"
                            class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/60">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M4 4h7v16H4V4zm9 0h7v16h-7V4z"/></svg>
                    </button>
                </div>
            </div>

            {{-- Body: TOC + area scroll --}}
            <div class="flex flex-1 min-h-0">
                <aside id="pdf-toc" class="hidden w-64 shrink-0 overflow-y-auto border-r border-gray-200 dark:border-gray-700/60 bg-white dark:bg-gray-800 p-2">
                    <p class="px-2 py-1 text-xs font-semibold text-gray-400 uppercase">Daftar Isi</p>
                    <ul id="pdf-toc-list" class="text-sm"></ul>
                </aside>

                <div id="pdf-scroll" class="relative flex-1 overflow-y-auto p-3 sm:p-6">
                    <p id="pdf-status" class="text-center text-gray-500 py-10">Memuat dokumen…</p>
                    <div id="pdf-pages" class="w-full"></div>
                </div>
            </div>
        </div>
    </div>
</x-user-layout>
