<x-user-layout>
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
                            <header class="mb-6">
                                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Majelis Hari Ini</h1>
                            </header>

                            <!-- Posts -->
                            <div class="grid grid-cols-12 gap-4">
                                <x-home.jadwal-majelis :schedules="$schedules" />
                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <!-- Right content -->
            <x-community.feed-right-content />

        </div>

    </div>
</x-user-layout>
