<x-dashboard-layout>
    <div class="grow">

        <div class="p-6 space-y-6">
            <!-- Page header -->
            <div class="sm:flex sm:justify-between sm:items-center mb-8">
                <div class="mb-4 sm:mb-0">
                    <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold">Edit Acara Majelis</h2>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('kelola-acara-majelis') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                        Kembali
                    </a>
                </div>
            </div>

            <div>
                <form action="{{ route('kelola-acara-majelis.update', $schedule->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    {{-- assembly_id tidak perlu di-update biasanya, tapi jika perlu validasi bisa disertakan hidden. 
                         Namun controller tidak memvalidasi assembly_id pada update, jadi aman di-skip atau dibiarkan saja. --}}
                    
                    <div class="text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20 border-t border-b border-gray-100 dark:border-gray-700/60 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                        <div class="grid md:grid-cols-2 gap-6">

                            <div>
                                <label class="block text-sm font-medium mb-2" for="name">Nama Event <span class="text-red-500">*</span></label>
                                <input id="name" class="form-input w-full @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name', $event->name) }}" required/>
                                @error('name')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="category">Kategori <span class="text-red-500">*</span></label>
                                <select id="category" name="category" class="form-select w-full @error('category') is-invalid @enderror" required>
                                    <option value="Taklim" {{ old('category', $event->category) == 'Taklim' ? 'selected' : '' }}>Taklim</option>
                                    <option value="Maulid" {{ old('category', $event->category) == 'Maulid' ? 'selected' : '' }}>Maulid</option>
                                    <option value="Dzikir" {{ old('category', $event->category) == 'Dzikir' ? 'selected' : '' }}>Dzikir</option>
                                    <option value="Haul" {{ old('category', $event->category) == 'Haul' ? 'selected' : '' }}>Haul</option>
                                    <option value="Tabligh Akbar" {{ old('category', $event->category) == 'Tabligh Akbar' ? 'selected' : '' }}>Tabligh Akbar</option>
                                </select>
                                @error('category')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="date">Tanggal & Waktu <span class="text-red-500">*</span></label>
                                <input id="date" class="form-input w-full @error('date') is-invalid @enderror" type="datetime-local" name="date" value="{{ old('date', $event->date ? date('Y-m-d\TH:i', strtotime($event->date)) : '') }}" required/>
                                @error('date')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="access">Akses <span class="text-red-500">*</span></label>
                                <select id="access" name="access" class="form-select w-full @error('access') is-invalid @enderror" required>
                                    <option value="Umum" {{ old('access', $event->access) == 'Umum' ? 'selected' : '' }}>Umum</option>
                                    <option value="Khusus" {{ old('access', $event->access) == 'Khusus' ? 'selected' : '' }}>Khusus</option>
                                </select>
                                @error('access')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="image">Poster</label>
                                @if ($event->image)
                                    <div class="my-2">
                                        <img src="{{ Storage::url($event->image) }}" alt="Poster Saat Ini" class="w-32 h-20 object-cover rounded">
                                        <p class="text-xs text-gray-500 mt-1">Poster saat ini. Kosongkan jika tidak ingin ganti.</p>
                                    </div>
                                @endif
                                <input id="image" class="form-input w-full @error('image') is-invalid @enderror" type="file" name="image" accept="image/*" />
                                @error('image')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="location">Nama Lokasi (Tempat) <span class="text-red-500">*</span></label>
                                <input id="location" class="form-input w-full @error('location') is-invalid @enderror" type="text" name="location" value="{{ old('location', $event->location) }}" required placeholder="Contoh: Masjid Raya"/>
                                @error('location')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold my-4">Alamat Lengkap</h2>
                        <div class="grid grid-cols-4 gap-4">

                            <div>
                                <label class="block text-sm font-medium mb-2" for="province">Provinsi</label>
                                <select id="province" class="form-select w-full @error('province') is-invalid @enderror" name="province">
                                    <option value="">==Pilih Salah Satu==</option>
                                    @foreach($provinces as $code => $name)
                                        <option value="{{ $code }}" {{ old('province', $event->province_code) == $code ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('province')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="city">Kabupaten/Kota</label>
                                <select id="city" class="form-select w-full @error('city') is-invalid @enderror" name="city" data-selected="{{ old('city', $event->city_code) }}">
                                    <option value="">==Pilih Salah Satu==</option>
                                </select>
                                @error('city')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="district">Kecamatan</label>
                                <select id="district" class="form-select w-full" name="district" data-selected="{{ old('district', $event->district_code) }}">
                                    <option value="">==Pilih Salah Satu==</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="village">Desa/Kelurahan</label>
                                <select id="village" class="form-select w-full" name="village" data-selected="{{ old('village', $event->village_code) }}">
                                    <option value="">==Pilih Salah Satu==</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                        <x-button>
                            Simpan Perubahan
                        </x-button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-dashboard-layout>
