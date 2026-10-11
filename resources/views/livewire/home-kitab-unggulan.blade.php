<div>
    @if ($books->isNotEmpty())
        <header class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Kitab Ulama</h2>
            <a href="{{ route('kitab-list') }}" class="text-sm font-medium text-emerald-500 hover:text-emerald-600">Lihat semua -&gt;</a>
        </header>

        <div class="grid xl:grid-cols-2 gap-4 mb-8">
            @foreach ($books as $book)
                <x-kitab.card :book="$book" />
            @endforeach
        </div>
    @endif
</div>
