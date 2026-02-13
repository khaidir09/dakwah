<x-user-layout>
    @section('title', 'Jadwal Majelis')
    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">

        <div class="xl:flex">

            <!-- Left + Middle content -->
            <div class="md:flex flex-1">

                <!-- Left content -->
                <x-community.feed-left-content />

                <!-- Middle content -->
                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">

                        <!-- Blocks -->
                        <div class="space-y-4">

                            <!-- Title -->
                            <header>
                                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Jadwal Majelis</h1>
                            </header>

                            @if(isset($isRamadhan) && $isRamadhan)
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 dark:bg-yellow-900/30 dark:border-yellow-600">
                                    <div class="flex">
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700 dark:text-yellow-400">
                                                Mohon maaf, jadwal rutinan majelis ini diliburkan selama bulan Ramadhan dan akan aktif kembali pada bulan Syawal.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Posts -->
                            <livewire:list-jadwal-majelis />

                        </div>

                    </div>
                </div>

            </div>

            <!-- Right content -->
            <x-community.feed-right-content />

        </div>

    </div>
</x-user-layout>
