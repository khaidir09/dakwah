<x-user-layout>
    @section('title', $biography->nama)

    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">

        <div class="xl:flex">

            <!-- Left + Middle content -->
            <div class="md:flex flex-1">

                <!-- Left content -->
                <x-community.feed-left-content />

                <!-- Middle content -->
                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">

                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">

                            <!-- Header -->
                            <header class="mb-6">
                                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-gray-100 mb-4">{{ $biography->nama }}</h1>

                                @if($biography->foto)
                                    <div class="relative w-full h-64 md:h-96 rounded-xl overflow-hidden mb-6">
                                        <img src="{{ asset('storage/' . $biography->foto) }}" alt="{{ $biography->nama }}" class="object-cover w-full h-full">
                                    </div>
                                @endif

                                <div class="flex flex-col gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    @if($biography->tanggal_wafat_masehi)
                                        <div class="flex items-center">
                                            <span class="font-semibold w-32">Wafat (Masehi):</span>
                                            <span>{{ \Carbon\Carbon::parse($biography->tanggal_wafat_masehi)->isoFormat('D MMMM Y') }}</span>
                                        </div>
                                    @endif

                                    @if($biography->tanggal_wafat_hijriah)
                                        <div class="flex items-center">
                                            <span class="font-semibold w-32">Wafat (Hijriah):</span>
                                            <span>{{ $biography->tanggal_wafat_hijriah }}</span>
                                        </div>
                                    @endif
                                </div>
                            </header>

                            <!-- Description -->
                            <div class="prose dark:prose-invert max-w-none mb-8 text-gray-800 dark:text-gray-200 text-justify">
                                {!! $biography->deskripsi !!}
                            </div>

                            <!-- Map -->
                            @if($biography->maps)
                                <div class="mt-8">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-3">Lokasi Makam</h3>
                                    <div class="w-full h-96 rounded-xl overflow-hidden [&>iframe]:w-full [&>iframe]:h-full">
                                        {!! $biography->maps !!}
                                    </div>
                                </div>
                            @endif

                        </div>

                    </div>
                </div>

            </div>

            <!-- Right content -->
            <x-community.feed-right-content />

        </div>

    </div>
</x-user-layout>
