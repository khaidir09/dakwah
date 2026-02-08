<div>
    <!-- Tabs -->
    <div class="mb-5 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block p-4 border-b-2 rounded-t-lg {{ $kategori == 'wirid' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-500' : 'hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 border-transparent' }}"
                    wire:click="setKategori('wirid')"
                    type="button"
                    role="tab">
                    Wirid
                </button>
            </li>
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block p-4 border-b-2 rounded-t-lg {{ $kategori == 'doa' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-500' : 'hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 border-transparent' }}"
                    wire:click="setKategori('doa')"
                    type="button"
                    role="tab">
                    Doa
                </button>
            </li>
        </ul>
    </div>

    <!-- Search & Filter -->
    <div class="flex flex-col md:flex-row gap-4 mb-5">
        <!-- Filter -->
        <div class="w-full md:w-1/3">
             <select wire:model.live="waktu" class="form-select w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 text-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-emerald-300 focus:ring focus:ring-emerald-200 focus:ring-opacity-50">
                 <option value="">Semua Waktu</option>
                 <option value="Seharian">Seharian</option>
                 <option value="Pagi & Petang">Pagi & Petang</option>
                 <option value="Ba'da Sholat">Ba'da Sholat</option>
                 <option value="Malam Jum'at">Malam Jum'at</option>
             </select>
        </div>

        <!-- Search form -->
        <div class="w-full md:w-2/3">
            <form class="relative">
                <label for="feed-search-mobile" class="sr-only">Search</label>
                <input wire:model.live="search" id="feed-search-mobile" class="form-input w-full pl-9 bg-white dark:bg-gray-800" type="search" placeholder="Cari nama amalan..." />
                <button class="absolute inset-0 right-auto group" type="submit" aria-label="Search">
                    <svg class="shrink-0 fill-current text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 ml-3 mr-2" width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                        <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
    <!-- Middle content -->
    <div class="flex-1 mt-5">
        <!-- Blocks -->
            <div class="space-y-4">

                <!-- Wirid -->
                @foreach ($wirids as $wirid)
                    <div id="wirid-{{ $wirid->id }}" class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5">
                        <!-- Header -->
                        <header class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-3">
                            {{ $wirid->nama }}
                        </header>
                        <!-- Body -->
                        <div class="text-sm text-gray-800 dark:text-gray-100 space-y-3 mb-5">
                            <p class="text-justify">{!! $wirid->deskripsi !!}</p>
                            {{-- Arab --}}
                            <div class="text-right text-2xl font-semibold">
                                {!! $wirid->arab !!}
                            </div>
                            {{-- Arti --}}
                            @if ($wirid->arti != null)
                                <div>
                                    <h3 class="font-semibold mb-1">Artinya:</h3>
                                    <p class="text-justify italic">"{!! $wirid->arti !!}"</p>
                                </div> 
                            @endif
                        </div>
                        <!-- Footer -->
                        <footer class="flex justify-between items-center space-x-3">
                            <div class="flex items-start space-x-3">
                                <div class="text-xs inline-flex font-medium bg-green-500/20 text-green-700 rounded-full text-center px-2.5 py-1">Dibaca {{ $wirid->jumlah }} kali pada waktu {{ $wirid->waktu }}</div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <!-- Share button -->
                                <a href="https://wa.me/?text={{ urlencode('Assalamualaikum, mari amalkan *' . $wirid->nama . '*\n\nSelengkapnya: ' . route('wirid-list', ['search' => $wirid->nama])) }}" target="_blank" class="flex items-center text-gray-400 dark:text-gray-500 hover:text-green-500 dark:hover:text-green-500">
                                    <svg class="shrink-0 fill-current mr-1.5" width="16" height="16" viewBox="0 0 16 16">
                                        <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                                    </svg>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Share</div>
                                </a>

                                <!-- Like button -->
                                <button
                                    wire:click="toggleLike({{ $wirid->id }})"
                                    class="flex items-center {{ isset($wirid->is_liked) && $wirid->is_liked ? 'text-red-500 hover:text-red-600' : 'text-gray-400 dark:text-gray-500 hover:text-emerald-500 dark:hover:text-emerald-500' }}">
                                    <svg class="shrink-0 fill-current mr-1.5" width="16" height="16" viewBox="0 0 16 16">
                                        <path d="M14.682 2.318A4.485 4.485 0 0011.5 1 4.377 4.377 0 008 2.707 4.383 4.383 0 004.5 1a4.5 4.5 0 00-3.182 7.682L8 15l6.682-6.318a4.5 4.5 0 000-6.364zm-1.4 4.933L8 12.247l-5.285-5A2.5 2.5 0 014.5 3c1.437 0 2.312.681 3.5 2.625C9.187 3.681 10.062 3 11.5 3a2.5 2.5 0 011.785 4.251h-.003z" />
                                    </svg>
                                    <div class="text-sm {{ isset($wirid->is_liked) && $wirid->is_liked ? 'text-red-500' : 'text-gray-500 dark:text-gray-400' }}">{{ $wirid->likes }}</div>
                                </button>
                            </div>
                        </footer>
                    </div>
                @endforeach

            </div>
    </div>

    <div class="mt-8">
        {{ $wirids->links() }}
    </div>
</div>