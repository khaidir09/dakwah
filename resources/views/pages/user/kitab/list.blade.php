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
