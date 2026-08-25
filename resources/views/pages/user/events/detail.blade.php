<x-user-layout>
    @php
        $tanggalAcara = \Carbon\Carbon::parse($event->date);
        $wilayahAcara = collect([$event->village?->name, $event->district?->name, $event->city?->name])
            ->filter()
            ->implode(', ');
        $deskripsiAcara = trim($event->category.' '.$event->name.' di '.$event->location
            .($wilayahAcara !== '' ? ', '.$wilayahAcara : '')
            .', '.$tanggalAcara->locale('id')->translatedFormat('d F Y').'.');
    @endphp
    @section('title', $seo->title($event->name))
    @section('meta_description', $seo->description(null, $deskripsiAcara))
    @section('meta_image', $seo->image($event->image, 'acara'))
    @section('og_type', 'article')

    @push('jsonld')
        {{ $schema->event($event) }}
    @endpush

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

                            <div class="flex justify-between items-center mb-6">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Detail Acara</div>
                                <a class="text-sm font-medium text-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400" href="{{ route('event-list') }}">&lt;- Kembali</a>
                            </div>

                            @unless ($event->isPubliclyVisible())
                                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/60 text-amber-800 dark:text-amber-300 rounded-lg px-4 py-3 text-sm">
                                    Pratinjau: acara ini belum tayang untuk umum.
                                </div>
                            @endunless

                            <article class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                                @if ($event->image)
                                    <img class="w-full max-h-[28rem] object-cover"
                                        src="{{ Storage::url($event->image) }}"
                                        alt="Poster acara {{ $event->name }}">
                                @endif

                                <div class="p-5">
                                    <div class="text-sm font-semibold text-emerald-500 uppercase mb-2">
                                        {{ $tanggalAcara->locale('id')->translatedFormat('l, d F Y') }} &middot; {{ $tanggalAcara->format('H:i') }} WITA
                                    </div>

                                    <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold mb-4">{{ $event->name }}</h1>

                                    <div class="grid sm:grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400 block">Lokasi</span>
                                            <span class="font-medium text-gray-800 dark:text-gray-100">{{ $event->location }}</span>
                                        </div>
                                        @if ($wilayahAcara !== '')
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400 block">Wilayah</span>
                                                <span class="font-medium text-gray-800 dark:text-gray-100">{{ $wilayahAcara }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400 block">Kategori</span>
                                            <span class="font-medium text-gray-800 dark:text-gray-100">{{ $event->category }}</span>
                                        </div>
                                        @if ($event->assembly)
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400 block">Penyelenggara</span>
                                                <a href="{{ route('majelis-detail', $event->assembly->route_slug) }}" class="font-medium text-emerald-500 hover:text-emerald-600">{{ $event->assembly->nama_majelis }}</a>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($event->maps_link)
                                        <div class="mt-5">
                                            <a href="{{ $event->maps_link }}" target="_blank" rel="noopener"
                                                class="btn bg-emerald-500 hover:bg-emerald-600 text-white">
                                                Buka Lokasi di Maps
                                            </a>
                                        </div>
                                    @endif

                                    <div class="mt-6">
                                        <x-kontributor.attribution :user="$event->contributor" />
                                    </div>
                                </div>
                            </article>

                        </div>

                    </div>
                </div>

            </div>

            <!-- Right content -->
            <x-community.feed-right-content />

        </div>

    </div>
</x-user-layout>
