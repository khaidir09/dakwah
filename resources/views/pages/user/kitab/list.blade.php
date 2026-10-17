<x-user-layout>
    @section('title', $seo->title('Kitab Ulama Banjar & Kalimantan'))
    @section('meta_description', $seo->description(null, 'Katalog karya kitab para ulama Banjar dan Kalimantan, terhubung dengan profil guru dan pustaka digital Syaikhuna.'))
    @section('meta_image', $seo->image(null, 'kitab'))
    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">

        <div class="xl:flex">

            <!-- Left + Middle content -->
            <div class="md:flex flex-1">

                <!-- Left content -->
                <x-community.feed-left-content />

                <!-- Middle content -->
                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">

                        <div class="space-y-4">

                            <header>
                                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Kitab Ulama</h1>
                            </header>

                            @if ($usulanUrl)
                                <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div>
                                        <h3 class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Mengetahui kitab karya ulama yang belum tercatat?</h3>
                                        <p class="text-sm text-emerald-700/80 dark:text-emerald-400/80">Sampaikan usulan Anda agar karya para ulama Banjar dan Kalimantan terhimpun dan makin dikenal masyarakat.</p>
                                    </div>
                                    <a href="{{ $usulanUrl }}" target="_blank" rel="noopener noreferrer" class="btn bg-emerald-500 hover:bg-emerald-600 text-white whitespace-nowrap shrink-0">
                                        Usulkan Kitab
                                    </a>
                                </div>
                            @endif

                            <livewire:list-kitab />

                        </div>

                    </div>
                </div>

            </div>

            <!-- Right content -->
            <x-community.feed-right-content />

        </div>

    </div>
</x-user-layout>
