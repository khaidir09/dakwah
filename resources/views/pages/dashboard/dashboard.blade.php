<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Dashboard actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Dashboard Admin</h1>
            </div>
        </div>

        <!-- Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Card 1: Users -->
            <div class="flex flex-col col-span-1 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="px-5 pt-5">
                    <header class="flex justify-between items-start mb-2">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Total Pengguna</h2>
                        <!-- Icon -->
                        <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-full">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                    </header>
                    <div class="text-3xl font-bold text-gray-800 dark:text-gray-100 mr-2">{{ $totalUsers }}</div>
                </div>
                <div class="px-5 pb-5 mt-auto">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Terdaftar di platform</div>
                </div>
            </div>

            <!-- Card 2: Majelis -->
            <div class="flex flex-col col-span-1 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="px-5 pt-5">
                    <header class="flex justify-between items-start mb-2">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Total Majelis</h2>
                         <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-full">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                    </header>
                    <div class="text-3xl font-bold text-gray-800 dark:text-gray-100 mr-2">{{ $totalAssemblies }}</div>
                </div>
                <div class="px-5 pb-5 mt-auto">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Majelis terdaftar</div>
                </div>
            </div>

            <!-- Card 3: Teachers -->
            <div class="flex flex-col col-span-1 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="px-5 pt-5">
                    <header class="flex justify-between items-start mb-2">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Total Guru</h2>
                         <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-full">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                    </header>
                    <div class="text-3xl font-bold text-gray-800 dark:text-gray-100 mr-2">{{ $totalTeachers }}</div>
                </div>
                <div class="px-5 pb-5 mt-auto">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Guru/Ustadz terdata</div>
                </div>
            </div>

            <!-- Card 4: Upcoming Schedules -->
            <div class="flex flex-col col-span-1 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="px-5 pt-5">
                    <header class="flex justify-between items-start mb-2">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Jadwal Mendatang</h2>
                         <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-full">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    </header>
                    <div class="text-3xl font-bold text-gray-800 dark:text-gray-100 mr-2">{{ $upcomingSchedules }}</div>
                </div>
                <div class="px-5 pb-5 mt-auto">
                     <div class="text-sm text-gray-500 dark:text-gray-400">Acara yang akan datang</div>
                </div>
            </div>

             <!-- Card 5: Wirid -->
            <div class="flex flex-col col-span-1 bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="px-5 pt-5">
                    <header class="flex justify-between items-start mb-2">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Total Wirid</h2>
                         <div class="p-2 bg-emerald-100 dark:bg-emerald-900/50 rounded-full">
                            <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                    </header>
                    <div class="text-3xl font-bold text-gray-800 dark:text-gray-100 mr-2">{{ $totalWirid }}</div>
                </div>
                <div class="px-5 pb-5 mt-auto">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Koleksi wirid/doa</div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
