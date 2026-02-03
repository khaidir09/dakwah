<x-user-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Favorit Saya</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn-sm px-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300" href="{{ route('beranda') }}">
                                                <svg class="fill-current text-gray-400 dark:text-gray-500 mr-2" width="7" height="12" viewBox="0 0 7 12">
                                                    <path d="M5.4.6 6.8 2l-4 4 4 4-1.4 1.4L0 6z" />
                                                </svg>
                                                <span>Kembali ke Halaman Utama</span>
                                            </a>
            </div>
        </div>

        <!-- Content -->
        <livewire:user.favorite-wirid-list />
    </div>
</x-user-layout>
