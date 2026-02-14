<x-user-layout>
    @section('title', $article->title)
    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">
        <div class="xl:flex">
            <!-- Left + Middle content -->
            <div class="md:flex flex-1">
                <!-- Left content -->
                <x-community.feed-left-content />

                <!-- Middle content -->
                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">
                        <div class="mb-6">
                            <a href="{{ route('tulisan.list', ['tab' => 'article']) }}" class="btn-sm px-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-600 dark:text-gray-300">
                                <svg class="fill-current text-gray-400 dark:text-gray-500 mr-2" width="7" height="12" viewBox="0 0 7 12">
                                    <path d="M5.4 1.4L4 0l-4 4 4 4 1.4-1.4L2.8 4z" />
                                </svg>
                                <span>Kembali ke Artikel</span>
                            </a>
                        </div>

                        <article class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 md:p-8">
                            <header class="mb-6">
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $article->category }}
                                    </span>
                                </div>
                                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800 dark:text-gray-100 mb-2">{{ $article->title }}</h1>
                                @if($article->subtitle)
                                    <h2 class="text-xl text-gray-600 dark:text-gray-300 mb-4">{{ $article->subtitle }}</h2>
                                @endif

                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    <span class="font-medium text-gray-800 dark:text-gray-100 mr-2">{{ $article->author_name }}</span>
                                    <span class="mx-1">&middot;</span>
                                    <span>{{ $article->published_at ? $article->published_at->locale('id')->translatedFormat('d M Y') : $article->created_at->locale('id')->translatedFormat('d M Y') }}</span>
                                    @if($article->foundation)
                                        <span class="mx-1">&middot;</span>
                                        <span>{{ $article->foundation->name }}</span>
                                    @endif
                                </div>

                                <div class="mt-4">
                                    <a href="https://wa.me/?text={{ urlencode($article->title . ' ' . url()->current()) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                        </svg>
                                        Bagikan ke WhatsApp
                                    </a>
                                </div>
                            </header>

                            @if ($article->cover_image)
                                <figure class="mb-8">
                                    <img class="w-full rounded-lg" src="{{ Storage::url($article->cover_image) }}" alt="{{ $article->title }}">
                                </figure>
                            @endif

                            <!-- Sections -->
                            <div class="prose max-w-none text-gray-600 dark:text-gray-300 text-justify space-y-8">
                                @foreach ($article->sections as $section)
                                    <section>
                                        @if($section->heading)
                                            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-3">{{ $section->heading }}</h3>
                                        @endif
                                        <div class="whitespace-pre-wrap">{!! nl2br(e($section->content)) !!}</div>
                                    </section>
                                @endforeach
                            </div>

                            <!-- Citations -->
                            @if ($article->citations && count($article->citations) > 0)
                                <div class="mt-10 pt-6 border-t border-gray-100 dark:border-gray-700">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Kutipan / Dalil</h3>
                                    <div class="space-y-4">
                                        @foreach ($article->citations as $citation)
                                            <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg border-l-4 border-blue-500">
                                                <div class="flex justify-between items-start mb-2">
                                                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-100 text-blue-800">{{ $citation->type }}</span>
                                                    <span class="text-sm font-medium text-gray-500">{{ $citation->reference }}</span>
                                                </div>
                                                @if($citation->source_text_arabic)
                                                    <p class="text-right font-arabic text-xl mb-2 leading-loose">{{ $citation->source_text_arabic }}</p>
                                                @endif
                                                @if($citation->translation)
                                                    <p class="text-sm text-gray-600 dark:text-gray-300 italic">"{{ $citation->translation }}"</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Bibliography -->
                            @if ($article->bibliography && count($article->bibliography) > 0)
                                <div class="mt-10 pt-6 border-t border-gray-100 dark:border-gray-700">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Daftar Pustaka</h3>
                                    <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                        @foreach ($article->bibliography as $bib)
                                            <li>{{ $bib->full_citation }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                        </article>

                    </div>
                </div>
            </div>

            <!-- Right content -->
            <x-community.feed-right-content />
        </div>
    </div>
</x-user-layout>
