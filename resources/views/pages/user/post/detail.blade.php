<x-user-layout>
    @section('title', $post->title)
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
                            <a href="{{ route('tulisan.list') }}" class="btn-sm px-3 bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-600 dark:text-gray-300">
                                <svg class="fill-current text-gray-400 dark:text-gray-500 mr-2" width="7" height="12" viewBox="0 0 7 12">
                                    <path d="M5.4 1.4L4 0l-4 4 4 4 1.4-1.4L2.8 4z" />
                                </svg>
                                <span>Kembali</span>
                            </a>
                        </div>

                        <article class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 md:p-8">
                            <header class="mb-6">
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach ($post->labels as $label)
                                        <a href="{{ route('tulisan.list', ['label' => $label->slug]) }}" class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                                            {{ $label->name }}
                                        </a>
                                    @endforeach
                                </div>
                                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800 dark:text-gray-100 mb-4">{{ $post->title }}</h1>
                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-medium text-gray-800 dark:text-gray-100 mr-2">{{ $post->user->name }}</span>
                                    <span class="mx-1">&middot;</span>
                                    <span>{{ $post->published_at ? $post->published_at->format('d M Y') : $post->created_at->format('d M Y') }}</span>
                                </div>
                            </header>

                            @if ($post->cover_image)
                                <figure class="mb-8">
                                    <img class="w-full rounded-lg" src="{{ Storage::url($post->cover_image) }}" alt="{{ $post->title }}">
                                </figure>
                            @endif

                            <div class="prose max-w-none text-gray-600 dark:text-gray-300 text-justify">
                                {!! nl2br(e($post->content)) !!}
                            </div>
                        </article>

                    </div>
                </div>
            </div>

            <!-- Right content -->
            <x-community.feed-right-content />
        </div>
    </div>
</x-user-layout>
