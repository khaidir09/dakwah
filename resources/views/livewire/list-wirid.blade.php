<div>
    <!-- Search form -->
    <div>
        <form class="relative">
            <label for="feed-search-mobile" class="sr-only">Search</label>
            <input wire:model.live="search" id="feed-search-mobile" class="form-input w-full pl-9 bg-white dark:bg-gray-800" type="search" placeholder="Cari nama amalan/waktu baca" />
            <button class="absolute inset-0 right-auto group" type="submit" aria-label="Search">
                <svg class="shrink-0 fill-current text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 ml-3 mr-2" width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                    <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                </svg>
            </button>
        </form>
    </div>
    <!-- Middle content -->
    <div class="flex-1 mt-5">
        <!-- Blocks -->
            <div class="space-y-4">

                <!-- Wirid -->
                @foreach ($wirids as $wirid)
                    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-5">
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
                            <div>
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