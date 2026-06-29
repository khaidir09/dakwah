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
        </style>
    @endpush

    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('pustaka-detail', $library) }}" class="text-sm font-medium text-indigo-500 hover:text-indigo-600 flex items-center">
                <svg class="w-3 h-3 fill-current mr-2" viewBox="0 0 12 12">
                    <path d="M5.4 10.6L.8 6l4.6-4.6L6.8 2.8 3.6 6l3.2 3.2z" />
                </svg>
                <span>Kembali ke Detail</span>
            </a>
            <span class="text-xs text-gray-400">Dokumen dilindungi &mdash; baca online saja</span>
        </div>

        <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">{{ $library->title }}</h1>

        <div id="pdf-viewer" data-src="{{ route('pustaka-stream', $library) }}">
            <p id="pdf-status" class="text-center text-gray-500 py-10">Memuat dokumen…</p>
            <div id="pdf-pages" class="w-full"></div>
        </div>
    </div>
</x-user-layout>
