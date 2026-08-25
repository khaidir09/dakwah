<x-user-layout>
    @section('title', $seo->title('Manaqib Ulama'))
    @section('meta_description', $seo->description(null, 'Manaqib dan riwayat hidup para ulama Kalimantan dalam silsilah keilmuan Banjar.'))
    @section('meta_image', $seo->image(null, 'manaqib'))
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
                                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Manaqib Ulama</h1>
                            </header>

                            <!-- Posts -->
                            <livewire:list-biography />

                        </div>

                    </div>
                </div>

            </div>

            <!-- Right content -->
            <x-community.feed-right-content />

        </div>

    </div>
</x-user-layout>
