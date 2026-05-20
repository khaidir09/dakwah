<x-user-layout>
    @section('title', 'Catatan Pengajian: ' . ($note->schedule->assembly->nama_majelis ?? 'Majelis'))
    @section('meta_description', Str::limit(strip_tags($note->content), 150))

    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">

        <div class="xl:flex">

            <!-- Left + Middle content -->
            <div class="md:flex flex-1">

                <!-- Left content -->
                <x-community.feed-left-content />

                <!-- Middle content -->
                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">
                        <div class="space-y-4">
                            <!-- Back Button -->
                            <div class="mb-4">
                                <a href="{{ route('catatan-pengajian.list') }}" class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Kembali ke Daftar Catatan
                                </a>
                            </div>

                            <article class="bg-white dark:bg-gray-800 rounded-xl shadow-xs border border-gray-100 dark:border-gray-700/60 overflow-hidden">
                                <header class="px-5 py-6 border-b border-gray-100 dark:border-gray-700/60">
                                    <div class="mb-2 flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                            {{ $note->schedule->nama_jadwal ?? 'Jadwal Majelis' }}
                                        </span>
                                    </div>
                                    <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold mb-4">
                                        {{ $note->schedule->assembly->nama_majelis ?? 'Majelis' }}
                                    </h1>
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            <span>Oleh: <strong class="text-gray-800 dark:text-gray-200">{{ $note->user->name }}</strong></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <span>{{ $note->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</span>
                                        </div>
                                    </div>
                                </header>

                                <div class="p-5 md:p-8">
                                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 text-justify leading-relaxed text-base md:text-lg">
                                        {!! nl2br(e($note->content)) !!}
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right content -->
            <x-community.feed-right-content />

        </div>

    </div>
</x-user-layout>