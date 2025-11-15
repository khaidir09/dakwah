<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Tambah Majelis</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('majelis.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                    Kembali
                </a>
            </div>
        </div>

        <div>
            <form action="{{ route('majelis.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
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
                            <label class="block text-sm font-medium mb-2" for="nama_majelis">Nama Majelis <span class="text-red-500">*</span></label>
                            <input id="nama_majelis" class="form-input w-full @error('nama_majelis') is-invalid @enderror" type="text" name="nama_majelis" value="{{ old('nama_majelis') }}" required/>
                            @error('nama_majelis')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="teacher_id">Nama Guru <span class="text-red-500">*</span></label>
                            <select id="teacher_id" class="form-select w-full @error('teacher_id') is-invalid @enderror" name="teacher_id" required>
                                <option value="">Pilih Guru</option>
                                @foreach($teachers as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="alamat">Alamat <span class="text-red-500">*</span></label>
                            <input id="alamat" class="form-input w-full @error('alamat') is-invalid @enderror" type="text" name="alamat" value="{{ old('alamat') }}" required/>
                            @error('alamat')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2" for="maps">Maps</label>
                            <input id="maps" class="form-input w-full @error('maps') is-invalid @enderror" type="text" name="maps" value="{{ old('maps') }}" required/>
                            @error('maps')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    
                    <div class="grid grid-cols-1 mt-6">
                        <div>
                            <label class="block text-sm font-medium mb-2" for="deskripsi">Deskripsi <span class="text-red-500">*</span></label>
                            <textarea class="form-input w-full @error('deskripsi') is-invalid @enderror" name="deskripsi" id="deskripsi" cols="30" rows="10" required>{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                </div>

                <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                    <x-button>
                        Simpan
                    </x-button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
