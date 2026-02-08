<x-user-layout>
    @section('title', $library->title)

    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">
        <div class="xl:flex">
             <div class="md:flex flex-1">
                <x-community.feed-left-content />

                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">
                        <div class="mb-4">
                            <a href="{{ route('pustaka-list') }}" class="text-sm font-medium text-indigo-500 hover:text-indigo-600 flex items-center">
                                <svg class="w-3 h-3 fill-current mr-2" viewBox="0 0 12 12">
                                    <path d="M5.4 10.6L.8 6l4.6-4.6L6.8 2.8 3.6 6l3.2 3.2z" />
                                </svg>
                                <span>Kembali ke Pustaka</span>
                            </a>
                        </div>

                        <article class="bg-white dark:bg-gray-800 p-6 shadow-md rounded-xl border border-gray-100 dark:border-gray-700/60">
                            <div class="flex flex-col md:flex-row gap-8">
                                <!-- Cover -->
                                <div class="w-full md:w-1/3 flex-shrink-0">
                                    @if($library->cover_image)
                                        <img class="w-full rounded-lg shadow-lg" src="{{ Storage::url($library->cover_image) }}" alt="{{ $library->title }}">
                                    @else
                                        <div class="w-full aspect-[3/4] bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400 rounded-lg">
                                             <svg class="w-16 h-16 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="mt-6">
                                        @if($library->file_path)
                                            <a href="{{ Storage::url($library->file_path) }}" target="_blank" class="btn w-full bg-indigo-500 hover:bg-indigo-600 text-white">
                                                <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                                                    <path d="M15 15H1a1 1 0 01-1-1V2a1 1 0 011-1h4v2H2v10h12V3h-3V1h4a1 1 0 011 1v12a1 1 0 01-1 1zM9 7h3l-4 4-4-4h3V1h2v6z" />
                                                </svg>
                                                <span>Download / Baca PDF</span>
                                            </a>
                                        @else
                                            <button disabled class="btn w-full bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed">
                                                File Tidak Tersedia
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="flex-1">
                                    <div class="mb-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-500/30 dark:text-indigo-200">
                                                {{ $library->category }}
                                            </span>
                                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $library->price_type == 'free' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $library->price_type == 'free' ? 'Gratis' : 'Berbayar' }}
                                            </span>
                                        </div>
                                        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-4">{{ $library->title }}</h1>

                                        <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-400">
                                            {!! nl2br(e($library->description)) !!}
                                        </div>
                                    </div>

                                    @if($library->file_path)
                                        <livewire:library.chat :library="$library" />
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
             <x-community.feed-right-content />
        </div>
    </div>
</x-user-layout>
