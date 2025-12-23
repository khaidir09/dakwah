<x-user-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="xl:flex">
            <div class="md:flex flex-1">
                <div class="flex-1 lg:mx-12">
                    <div class="md:py-8">
                        <!-- Page header -->
                        <div class="mb-8 justify-between items-center md:flex">

                            <!-- Title -->
                            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Pengaturan Akun</h1>

                            <div class="mt-3 md:mt-0">
                                <a class="btn-sm px-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300" href="{{ route('beranda') }}">
                                    <svg class="fill-current text-gray-400 dark:text-gray-500 mr-2" width="7" height="12" viewBox="0 0 7 12">
                                        <path d="M5.4.6 6.8 2l-4 4 4 4-1.4 1.4L0 6z" />
                                    </svg>
                                    <span>Kembali ke Halaman Utama</span>
                                </a>
                            </div>

                        </div>

                        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl mb-8">
                            <div class="flex flex-col md:flex-row md:-mr-px">

                                <!-- Sidebar -->
                                <x-settings.settings-sidebar />

                                <!-- Panel -->
                                <x-settings.account-panel />

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-user-layout>
