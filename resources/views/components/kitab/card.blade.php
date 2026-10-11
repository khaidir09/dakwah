@props(['book', 'roles' => null])

{{-- $roles: label peran guru tertentu pada kitab ini (dipakai di profil guru). Bila null, kartu menampilkan pengarang utama. --}}
@php($author = $roles === null ? $book->primaryTeacher() : null)

<a href="{{ route('kitab-detail', $book) }}" class="flex h-full bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700/60 hover:shadow-lg transition-shadow duration-200">
    <div class="w-24 shrink-0 bg-gray-100 dark:bg-gray-700">
        @if ($book->cover_thumb_url)
            <img class="w-full h-full object-cover" src="{{ $book->cover_thumb_url }}" loading="lazy" alt="Sampul {{ $book->title }}">
        @else
            <div class="w-full h-full min-h-32 flex items-center justify-center text-gray-400">
                <svg class="w-10 h-10 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 6h2v14c0 1.1.9 2 2 2h14v-2H8V6h14V4H5c-1.1 0-2 .9-2 2zm16-4H5c-1.1 0-2 .9-2 2v15c0 1.1.9 2 2 2h15c.55 0 1-.45 1-1V3c0-.55-.45-1-1-1zm-1 16H8V4h11v14z"/>
                </svg>
            </div>
        @endif
    </div>
    <div class="grow min-w-0 p-4 flex flex-col">
        @if ($book->specialization)
            <span class="text-xs font-semibold text-emerald-500 uppercase tracking-wide mb-1">{{ $book->specialization->name }}</span>
        @endif
        <h3 class="text-base font-bold text-gray-800 dark:text-gray-100 line-clamp-2">{{ $book->title }}</h3>
        @if ($book->title_arabic)
            <p class="text-lg text-gray-600 dark:text-gray-300 break-words" dir="rtl" lang="ar">{{ $book->title_arabic }}</p>
        @endif
        @if ($author)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-auto pt-2 truncate">{{ $author->name }}</p>
        @elseif ($roles)
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-auto pt-2">{{ $roles }}</p>
        @endif
    </div>
</a>
