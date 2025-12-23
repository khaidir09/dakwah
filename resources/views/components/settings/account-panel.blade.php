<div class="grow">

    <!-- Panel body -->
    <div class="p-6 space-y-6">
        <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold mb-5">Profil Saya</h2>

        <!-- Picture -->
        <section>
            <div class="flex items-center">
                <div class="mr-4">
                    <img class="w-20 h-20 rounded-full" src="{{ asset('images/user-avatar-80.png') }}" width="80" height="80" alt="User upload" />
                </div>
                <button class="btn-sm dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">Ganti Foto Profil</button>
            </div>
        </section>

        <!-- Business Profile -->
        <section>
            <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                <div class="sm:w-1/3">
                    <label class="block text-sm font-medium mb-1" for="name">Nama Lengkap</label>
                    <input id="name" class="form-input w-full" type="text" value="{{ Auth::user()->name }}" />
                </div>
                <div class="sm:w-1/3">
                    <label class="block text-sm font-medium mb-1" for="location">Location</label>
                    <input id="location" class="form-input w-full" type="text" value="London, UK" />
                </div>
            </div>
        </section>

        <!-- Email -->
        <section>
            <h3 class="text-xl leading-snug text-gray-800 dark:text-gray-100 font-bold mb-1">Alamat Email</h3>
            <div class="text-sm">
                Email digunakan untuk keperluan masuk dan notifikasi penting lainnya.
            </div>
            <div class="flex flex-wrap mt-5">
                <div class="mr-2">
                    <label class="sr-only" for="email">Alamat Email</label>
                    <input id="email" class="form-input" type="email" value="{{ Auth::user()->email }}" />
                </div>
                <button class="btn dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">Ganti</button>
            </div>
        </section>

        <!-- Password -->
        <section>
            <h3 class="text-xl leading-snug text-gray-800 dark:text-gray-100 font-bold mb-1">Password</h3>
            <div class="text-sm">
                Ubah password akun anda secara berkala untuk menjaga keamanan akun.
            </div>
            <div class="mt-5">
                <button class="btn dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">Ubah Password</button>
            </div>
        </section>

        <!-- Daily Surah Reading Preference -->
        <livewire:settings.toggle-daily-surah />
    </div>

    <!-- Panel footer -->
    <footer>
        <div class="flex flex-col px-6 py-5 border-t border-gray-200 dark:border-gray-700/60">
            <div class="flex self-end">
                <button class="btn dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">Batal</button>
                <button class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white ml-3">Simpan Perubahan</button>
            </div>
        </div>
    </footer>

</div>