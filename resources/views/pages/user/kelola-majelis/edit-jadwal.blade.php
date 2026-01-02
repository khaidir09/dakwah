<x-dashboard-layout>
    <div class="grow">

        <div class="p-6 space-y-6">
            <!-- Page header -->
            <div class="sm:flex sm:justify-between sm:items-center mb-8">
                <div class="mb-4 sm:mb-0">
                    <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold">Edit Jadwal Majelis</h2>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('kelola-jadwal-majelis') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                        Kembali
                    </a>
                </div>
            </div>

            <div>
                <form action="{{ route('kelola-jadwal-majelis.update', $schedule->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    {{-- assembly_id tidak perlu di-update biasanya, tapi jika perlu validasi bisa disertakan hidden. 
                         Namun controller tidak memvalidasi assembly_id pada update, jadi aman di-skip atau dibiarkan saja. --}}
                    
                    <div class="text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20 border-t border-b border-gray-100 dark:border-gray-700/60 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                        <div class="grid md:grid-cols-2 gap-6">
                            @if (session('status'))
                                <div class="px-4 py-2 rounded-lg text-sm bg-green-500 text-white relative" role="alert">
                                    <span class="block sm:inline">{{ session('status') }}</span>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium mb-2" for="nama_jadwal">Nama Jadwal <span class="text-red-500">*</span></label>
                                <input id="nama_jadwal" class="form-input w-full @error('nama_jadwal') is-invalid @enderror" type="text" name="nama_jadwal" value="{{ old('nama_jadwal', $schedule->nama_jadwal) }}" required/>
                                @error('nama_jadwal')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="teacher_id">Nama Guru <span class="text-red-500">*</span></label>
                                <select id="teacher_id" class="form-select w-full @error('teacher_id') is-invalid @enderror" name="teacher_id" required>
                                    <option value="">Pilih Guru</option>
                                    @foreach($teachers as $item)
                                        <option value="{{ $item->id }}" {{ old('teacher_id', $schedule->teacher_id) == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                @error('teacher_id')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="hari">Hari <span class="text-red-500">*</span></label>
                                <select id="hari" class="form-select w-full @error('hari') is-invalid @enderror" name="hari" required>
                                    <option value="">Pilih Hari</option>
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $hari)
                                        <option value="{{ $hari }}" {{ old('hari', $schedule->hari) == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                                    @endforeach
                                </select>
                                @error('hari')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="access">Akses Jamaah <span class="text-red-500">*</span></label>
                                <select id="access" class="form-select w-full @error('access') is-invalid @enderror" name="access" required>
                                    <option value="">Pilih Akses</option>
                                    @foreach(['Umum', 'Ikhwan', 'Akhwat'] as $access)
                                        <option value="{{ $access }}" {{ old('access', $schedule->access) == $access ? 'selected' : '' }}>{{ $access }}</option>
                                    @endforeach
                                </select>
                                @error('access')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-2" for="waktu">Waktu <span class="text-red-500">*</span></label>
                                <input id="waktu" class="form-input w-full @error('waktu') is-invalid @enderror" type="time" name="waktu" value="{{ old('waktu', $schedule->waktu) }}" required/>
                                @error('waktu')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                        
                        <div class="grid grid-cols-1 mt-6">
                            <div>
                                <label class="block text-sm font-medium mb-2" for="deskripsi">Deskripsi</label>
                                <textarea class="form-input w-full @error('deskripsi') is-invalid @enderror" name="deskripsi" id="deskripsi" cols="30" rows="10">{{ old('deskripsi', $schedule->deskripsi) }}</textarea>
                                @error('deskripsi')
                                    <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                                @enderror
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
