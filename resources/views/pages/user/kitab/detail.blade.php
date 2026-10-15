<x-user-layout>
    @php($pengarang = $book->primaryTeacher())
    @section('title', $seo->title($book->title))
    @section('meta_description', $seo->description($book->description, 'Kitab '.$book->title.($pengarang ? ' karya '.$pengarang->name : '').' di Syaikhuna.'))
    @section('meta_image', $seo->image($book->cover_image, 'kitab'))
    @section('og_type', 'book')
    @unless ($book->is_active)
        @section('meta_robots', 'noindex, nofollow')
    @endunless

    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">
        <div class="xl:flex">
            <div class="md:flex flex-1">
                <x-community.feed-left-content />

                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">
                        <div class="mb-4">
                            <a href="{{ route('kitab-list') }}" class="text-sm font-medium text-emerald-500 hover:text-emerald-600">&lt;- Kembali ke Kitab Ulama</a>
                        </div>

                        @unless ($book->is_active)
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 text-sm text-yellow-700">
                                Pratinjau admin: kitab ini masih draf dan belum tampil ke publik.
                            </div>
                        @endunless

                        <article class="bg-white dark:bg-gray-800 p-6 shadow-md rounded-xl border border-gray-100 dark:border-gray-700/60">
                            <div class="flex flex-col md:flex-row gap-8">
                                <!-- Cover -->
                                <div class="w-full md:w-1/3 shrink-0">
                                    @if ($book->cover_url)
                                        <img class="w-full rounded-lg shadow-lg" src="{{ $book->cover_url }}" alt="Sampul {{ $book->title }}">
                                    @else
                                        <div class="w-full aspect-[3/4] bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400 rounded-lg">
                                            <svg class="w-16 h-16 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M4 6h2v14c0 1.1.9 2 2 2h14v-2H8V6h14V4H5c-1.1 0-2 .9-2 2zm16-4H5c-1.1 0-2 .9-2 2v15c0 1.1.9 2 2 2h15c.55 0 1-.45 1-1V3c0-.55-.45-1-1-1zm-1 16H8V4h11v14z"/>
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="mt-6 space-y-2">
                                        @if ($book->library && $book->library->is_active)
                                            <a href="{{ route('pustaka-detail', $book->library) }}" class="btn w-full bg-emerald-500 hover:bg-emerald-600 text-white">
                                                Baca di Pustaka
                                                @if ($book->library->isPaid())
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Berbayar</span>
                                                @endif
                                            </a>
                                        @endif
                                        @if ($book->external_url)
                                            <a href="{{ $book->external_url }}" target="_blank" rel="noopener nofollow" class="btn w-full bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 text-gray-800 dark:text-gray-300">
                                                Tautan Eksternal
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        @if ($book->specialization)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-500/30 dark:text-emerald-200">{{ $book->specialization->name }}</span>
                                        @endif
                                        @if ($book->language_label)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ $book->language_label }}</span>
                                        @endif
                                        @if ($book->publication_status_label)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{ $book->publication_status_label }}</span>
                                        @endif
                                    </div>

                                    <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $book->title }}</h1>
                                    @if ($book->title_arabic)
                                        <p class="text-2xl text-gray-700 dark:text-gray-200 mt-2 break-words" dir="rtl" lang="ar">{{ $book->title_arabic }}</p>
                                    @endif
                                    @if ($book->publisher)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Penerbit: {{ $book->publisher }}</p>
                                    @endif

                                    @if ($teachersByRole->isNotEmpty())
                                        <div class="mt-6 space-y-4">
                                            @foreach (\App\Models\Book::ROLES as $role => $label)
                                                @if ($teachersByRole->has($role))
                                                    <div>
                                                        <h2 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $label }}</h2>
                                                        <div class="space-y-2">
                                                            @foreach ($teachersByRole[$role] as $teacher)
                                                                <a href="{{ route('guru-detail', $teacher) }}" class="flex items-center group">
                                                                    @if ($teacher->foto_url)
                                                                        <img class="w-10 h-10 rounded-full object-cover mr-3" src="{{ $teacher->foto_url }}" alt="{{ $teacher->name }}" loading="lazy">
                                                                    @else
                                                                        <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 mr-3"></div>
                                                                    @endif
                                                                    <div>
                                                                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100 group-hover:text-emerald-500">{{ $teacher->name }}</div>
                                                                        @if ($teacher->isWafat())
                                                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                                rahimahullah{{ $teacher->tahunWafat() ? ' · wafat '.$teacher->tahunWafat() : '' }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($book->description)
                                        <div class="mt-6 format lg:format-lg dark:format-invert format-blue max-w-none prose dark:prose-invert text-gray-600 dark:text-gray-300 text-justify">
                                            {!! clean($book->description, 'rich_text') !!}
                                        </div>
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
