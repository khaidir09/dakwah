<div>
    <!-- Filters -->
    <div class="mb-6 flex flex-col md:flex-row gap-4">
        <div class="w-full md:w-1/3">
            <input wire:model.live.debounce.300ms="search" type="text" class="form-input w-full bg-white dark:bg-gray-800" placeholder="Cari judul pustaka...">
        </div>
        <div class="w-full md:w-1/4">
            <select wire:model.live="category" class="form-select w-full bg-white dark:bg-gray-800">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full md:w-1/4">
            <select wire:model.live="price_type" class="form-select w-full bg-white dark:bg-gray-800">
                <option value="">Semua Tipe</option>
                <option value="free">Gratis</option>
                <option value="paid">Berbayar</option>
            </select>
        </div>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($libraries as $library)
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden flex flex-col h-full border border-gray-100 dark:border-gray-700/60">
                <!-- Cover Image -->
                <a href="{{ route('pustaka-detail', $library->slug) }}" class="block aspect-[2/3] relative overflow-hidden group">
                    @if($library->cover_image)
                        <img class="w-full h-full object-cover transition duration-700 ease-out group-hover:scale-105" src="{{ Storage::url($library->cover_image) }}" alt="{{ $library->title }}">
                    @else
                        <div class="w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400">
                             <svg class="w-12 h-12 fill-current" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                            </svg>
                        </div>
                    @endif

                    <div class="absolute top-2 right-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $library->price_type == 'free' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} shadow-sm">
                            {{ $library->price_type == 'free' ? 'Gratis' : 'Berbayar' }}
                        </span>
                    </div>
                </a>

                <div class="p-4 flex-1 flex flex-col">
                    <div class="mb-2">
                         <span class="text-xs font-semibold text-indigo-500 uppercase tracking-wide">{{ $library->category }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2 line-clamp-2">
                        <a href="{{ route('pustaka-detail', $library->slug) }}" class="hover:text-indigo-500 transition duration-150 ease-in-out">{{ $library->title }}</a>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-3 mb-4 flex-1">
                        {{ $library->description }}
                    </p>

                    <div class="mt-auto">
                        <a href="{{ route('pustaka-detail', $library->slug) }}" class="btn-sm w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-indigo-500">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="inline-flex rounded-full bg-gray-100 dark:bg-gray-700 p-4 mb-4">
                     <svg class="w-8 h-8 fill-current text-gray-400 dark:text-gray-500" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2">Tidak ada data pustaka</h3>
                <p class="text-gray-500 dark:text-gray-400">Belum ada pustaka yang ditambahkan atau tidak ditemukan.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $libraries->links() }}
    </div>
</div>
