<div>
    <!-- Search form -->
    <div class="mb-6">
        <form class="relative">
            <label for="feed-search-mobile" class="sr-only">Search</label>
            <input wire:model.live="search" id="feed-search-mobile" class="form-input w-full pl-9 bg-white dark:bg-gray-800" type="search" placeholder="Cari nama guru" />
            <button class="absolute inset-0 right-auto group" type="submit" aria-label="Search">
                <svg class="shrink-0 fill-current text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400 ml-3 mr-2" width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                    <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                </svg>
            </button>
        </form>
    </div>

    <!-- Region Filters -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Province Filter -->
        <div>
            <label for="province" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provinsi</label>
            <select wire:model.live="selectedProvince" id="province" class="block w-full form-select rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 sm:text-sm">
                <option value="">Semua Provinsi</option>
                @foreach($provinces as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <!-- City Filter -->
        <div>
            <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kabupaten/Kota</label>
            <select wire:model.live="selectedCity" id="city" class="block w-full form-select rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 sm:text-sm" {{ empty($selectedProvince) ? 'disabled' : '' }}>
                <option value="">{{ empty($selectedProvince) ? 'Pilih Provinsi Terlebih Dahulu' : 'Semua Kabupaten/Kota' }}</option>
                @foreach($cities as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <!-- District Filter -->
        <div>
            <label for="district" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kecamatan</label>
            <select wire:model.live="selectedDistrict" id="district" class="block w-full form-select rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 sm:text-sm" {{ empty($selectedCity) ? 'disabled' : '' }}>
                <option value="">{{ empty($selectedCity) ? 'Pilih Kabupaten/Kota Terlebih Dahulu' : 'Semua Kecamatan' }}</option>
                @foreach($districts as $code => $name)
                    <option value="{{ $code }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4">
        @foreach ($teachers as $teacher)
            <div class="col-span-full sm:col-span-6 xl:col-span-3 bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <div class="flex flex-col h-full">
                    <!-- Card top -->
                    <div class="grow p-5">
                        <!-- Image + name -->
                        <header>                
                            <div class="flex justify-center mb-2">
                                <a class="relative inline-flex items-start" href="#0">
                                    <img class="rounded-full" src="{{ Storage::url($teacher->foto) }}" width="64" height="64" alt="{{ $teacher->name }}" />
                                </a>
                            </div>
                            <div class="text-center">
                                <a class="inline-flex text-gray-800 dark:text-gray-100 hover:text-gray-900 dark:hover:text-white" href="{{ route('guru-detail', $teacher->id) }}">
                                    <h2 class="text-md leading-snug justify-center font-semibold">{{ $teacher->name }} </h2>
                                </a>
                            </div>
                            @if ($teacher->tahun_lahir != null)
                                <div class="flex justify-center items-center text-xs">({{ date('Y') - $teacher->tahun_lahir }} tahun)</div>
                            @endif
                            <div class="flex justify-center items-center text-sm font-medium mt-2">{{ $teacher->village?->name }}</div>
                        </header>
                        <!-- Bio -->
                        {{-- <div class="text-center mt-2">
                            <div class="text-sm truncate">{{ $teacher->biografi }}</div>
                        </div> --}}
                    </div>
                    <!-- Card footer -->
                    {{-- <div>
                        <a class="block text-center text-sm text-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400 font-medium px-3 py-4" href="{{ route('messages') }}">
                            <div class="flex items-center justify-center">
                                <svg class="fill-current shrink-0 mr-2" width="16" height="16" viewBox="0 0 16 16">
                                    <path d="M8 0C3.6 0 0 3.1 0 7s3.6 7 8 7h.6l5.4 2v-4.4c1.2-1.2 2-2.8 2-4.6 0-3.9-3.6-7-8-7zm4 10.8v2.3L8.9 12H8c-3.3 0-6-2.2-6-5s2.7-5 6-5 6 2.2 6 5c0 2.2-2 3.8-2 3.8z" />
                                </svg>
                                <span>Send Message</span>
                            </div>
                        </a>
                    </div> --}}
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $teachers->links() }}
    </div>
</div>