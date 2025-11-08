<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Profil Anda</h1>
                <p>Perbarui informasi profil Anda di sini.</p>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('nasabah.dashboard') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>

        <div>
            <form action="{{ route('nasabah.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid grid-cols-6 gap-6">
                        @if (session('status'))
                            <div class="col-span-6 px-4 py-2 rounded-lg text-sm bg-green-500 text-white relative" role="alert">
                                <span class="block sm:inline">{{ session('status') }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="col-span-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Birth Place -->
                        <div class="col-span-6">
                            <x-label for="birth_place" value="Tempat Lahir" />
                            <x-input id="birth_place" type="text" class="mt-1 block w-full" name="birth_place" :value="old('birth_place', $customer->birth_place)" required />
                            <x-input-error for="birth_place" class="mt-2" />
                        </div>

                        <!-- Birthday -->
                        <div class="col-span-6">
                            <x-label for="birthday" value="Tanggal Lahir" />
                            <x-input id="birthday" type="date" class="mt-1 block w-full" name="birthday" :value="old('birthday', $customer->birthday ? \Carbon\Carbon::parse($customer->birthday)->format('Y-m-d') : '')" required />
                            <x-input-error for="birthday" class="mt-2" />
                        </div>

                        <!-- Parent -->
                        <div class="col-span-6">
                            <x-label for="parent" value="Nama Orang Tua" />
                            <x-input id="parent" type="text" class="mt-1 block w-full" name="parent" :value="old('parent', $customer->parent)" required />
                            <x-input-error for="parent" class="mt-2" />
                        </div>

                        <!-- PIN -->
                        <div class="col-span-6">
                            <x-label for="pin" value="PIN Baru (opsional)" />
                            <x-input id="pin" type="password" class="mt-1 block w-full" name="pin" />
                            <x-input-error for="pin" class="mt-2" />
                            <p class="mt-2 text-sm text-gray-500">Kosongkan jika tidak ingin mengubah PIN.</p>
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
