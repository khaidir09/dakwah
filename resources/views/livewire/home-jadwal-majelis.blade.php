<div>
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

    @if ($schedules->count() > 0)
        <div class="grid grid-cols-12 gap-4">
            @foreach ($schedules as $schedule)
                <div class="col-span-full xl:col-span-6 bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
                    <div class="flex flex-col h-full">
                        <!-- Card top -->
                        <div class="grow p-5">
                            <div class="flex justify-between items-start">
                                <!-- Image + name -->
                                <header>                
                                    <div class="flex mb-2">
                                        <a class="relative inline-flex items-start mr-5" href="{{ route('guru-detail', $schedule->teacher) }}">
                                            @if($schedule->teacher->foto != null)
                                                <img class="rounded-full w-16 h-16 object-cover" src="{{ Storage::url($schedule->teacher->foto) }}" alt="{{ $schedule->teacher->name }}" />
                                            @else
                                                <div class="w-16 h-16 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-full text-gray-400">
                                                    <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </a>
                                        <div class="mt-1 pr-1">
                                            <a class="inline-flex text-gray-800 dark:text-gray-100 hover:text-gray-900 dark:hover:text-white" href="{{ route('guru-detail', $schedule->teacher) }}">
                                                <h2 class="text-xl leading-snug justify-center font-semibold">{{ $schedule->teacher->name }}</h2>
                                            </a>
                                            <div>{{ $schedule->nama_jadwal }}</div>
                                        </div>
                                    </div>
                                </header>
                                <div class="relative inline-flex shrink-0">
                                    @php
                                        $accessColor = match($schedule->access) {
                                            'Umum' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                            'Ikhwan' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                            'Akhwat' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $accessColor }}">
                                        {{ $schedule->access }}
                                    </span>
                                </div>             
                            </div>
                            <!-- Bio -->
                            <div class="mt-2">
                                <div class="text-sm">{{ $schedule->deskripsi }}</div>
                            </div>
                        </div>
                        <!-- Card footer -->
                        <div class="border-t border-gray-100 dark:border-gray-700/60">
                            <div class="flex divide-x divide-gray-100 dark:divide-gray-700/60">
                                <a class="block flex-1 text-center text-sm text-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400 font-medium px-3 py-4" href="{{ route('majelis-detail', $schedule->assembly->id) }}">
                                    <div class="flex items-center justify-center">
                                        <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 24 24"> <path fill-rule="evenodd" clip-rule="evenodd" d="M13 11V19H11V11H13Z" fill="#059669"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M6 7C6 3.68629 8.68629 1 12 1C15.3137 1 18 3.68629 18 7C18 10.3137 15.3137 13 12 13C8.68629 13 6 10.3137 6 7Z" fill="#059669"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M8.1893 15.7653L7.21204 15.9774C5.78358 16.2873 4.65768 16.7189 3.91598 17.1909C3.13673 17.6867 3 18.0745 3 18.2492C3 18.3785 3.06629 18.6213 3.44935 18.961C3.83125 19.2997 4.44093 19.6504 5.28013 19.9652C6.95116 20.592 9.32677 21.0001 12 21.0001C14.6732 21.0001 17.0488 20.592 18.7199 19.9652C19.5591 19.6504 20.1687 19.2997 20.5507 18.961C20.9337 18.6213 21 18.3785 21 18.2492C21 18.0745 20.8633 17.6867 20.084 17.1909C19.3423 16.7189 18.2164 16.2873 16.788 15.9774L15.8107 15.7653L16.2348 13.8108L17.212 14.0228C18.7726 14.3614 20.1467 14.8602 21.1577 15.5035C22.1312 16.123 23 17.0355 23 18.2492C23 19.1557 22.5066 19.8996 21.8776 20.4574C21.2475 21.0162 20.3927 21.4738 19.4223 21.8378C17.474 22.5686 14.8496 23.0001 12 23.0001C9.15039 23.0001 6.52599 22.5686 4.57773 21.8378C3.60729 21.4738 2.7525 21.0162 2.12235 20.4574C1.49336 19.8996 1 19.1557 1 18.2492C1 17.0355 1.86876 16.123 2.84227 15.5035C3.85331 14.8602 5.22741 14.3614 6.78796 14.0228L7.76522 13.8108L8.1893 15.7653Z" fill="#059669" data-color="color-2"></path> </svg>
                                        <span>{{ $schedule->assembly->nama_majelis }}</span>
                                    </div>
                                </a>
                                <div class="block flex-1 text-center text-sm text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-200 font-medium px-3 py-4 group" href="#">
                                    <div class="flex items-center justify-center">
                                    <svg class="mr-2" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="16px" height="16px" viewBox="0 0 24 24"><rect x="6" width="2" height="5" fill="rgba(75, 85, 99, 1)" stroke-width="0" data-color="color-2"></rect><rect x="16" width="2" height="5" fill="rgba(75, 85, 99, 1)" stroke-width="0" data-color="color-2"></rect><path d="m20,3H4c-1.654,0-3,1.346-3,3v12c0,1.654,1.346,3,3,3h16c1.654,0,3-1.346,3-3V6c0-1.654-1.346-3-3-3Zm0,16H4c-.551,0-1-.448-1-1v-9h18v9c0,.552-.449,1-1,1Z" stroke-width="0" fill="rgba(75, 85, 99, 1)"></path></svg>
                                        <span>{{ $schedule->hari }}, {{ $schedule->waktu_formatted }} WITA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $schedules->links() }}
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Tidak ada jadwal rutinan majelis hari ini</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Silakan lihat koleksi video pengajian kami.</p>
            <a href="{{ route('video-list') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Lihat Video
            </a>
        </div>
    @endif
</div>
