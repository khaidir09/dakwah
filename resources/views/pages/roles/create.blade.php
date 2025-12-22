<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Tambah Role</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('roles.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                    Kembali
                </a>
            </div>
        </div>

        <div>
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid grid-cols-1 gap-6">
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
                            <label class="block text-sm font-medium mb-2" for="name">Nama Role <span class="text-red-500">*</span></label>
                            <input id="name" class="form-input w-full" type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Administrator"/>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">Permissions</label>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                @foreach($permissions as $value)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="permission[]" value="{{ $value->name }}" class="form-checkbox text-indigo-600">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $value->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                    <button type="submit" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
