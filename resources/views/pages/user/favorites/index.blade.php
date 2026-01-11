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
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl mb-8">
            <div class="p-6">
                <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                        <li class="mr-2" role="presentation">
                            <button class="inline-block p-4 border-b-2 rounded-t-lg text-violet-600 hover:text-violet-600 dark:text-violet-500 dark:hover:text-violet-500 border-violet-600 dark:border-violet-500" id="wirid-tab" data-tabs-target="#wirid" type="button" role="tab" aria-controls="wirid" aria-selected="true">Wirid</button>
                        </li>
                        <!-- Placeholder for future tabs like "Do'a" -->
                        {{--
                        <li class="mr-2" role="presentation">
                            <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300" id="doa-tab" data-tabs-target="#doa" type="button" role="tab" aria-controls="doa" aria-selected="false">Do'a</button>
                        </li>
                        --}}
                    </ul>
                </div>
                <div id="myTabContent">
                    <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800" id="wirid" role="tabpanel" aria-labelledby="wirid-tab" style="display: block;"> <!-- Forced block for now as it's the only tab -->
                        <livewire:user.favorite-wirid-list />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-user-layout>
