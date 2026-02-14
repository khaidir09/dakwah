<div>
    <!-- Search and Filter -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input wire:model.live="search" type="text" class="form-input w-full" placeholder="Cari artikel ilmiah...">
        </div>
        <div class="sm:w-1/4">
            <select wire:model.live="category" class="form-select w-full">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Grid -->
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-2">
        @forelse ($articles as $article)
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                <a href="{{ route('artikel.detail', $article->slug) }}" class="block">
                    @if ($article->cover_image)
                        <img src="{{ Storage::url($article->cover_image) }}" alt="{{ $article->title }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-gray-400 dark:text-gray-500">No Image</span>
                        </div>
                    @endif
                </a>
                <div class="p-4">
                    <div class="mb-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $article->published_at ? $article->published_at->locale('id')->translatedFormat('d M Y') : $article->created_at->locale('id')->translatedFormat('d M Y') }}</span>
                        <span>{{ $article->author_name }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2 line-clamp-2">
                        <a href="{{ route('artikel.detail', $article->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            {{ $article->title }}
                        </a>
                    </h3>
                    @if($article->subtitle)
                        <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-1 mb-2">{{ $article->subtitle }}</p>
                    @endif
                    <div class="flex flex-wrap gap-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $article->category }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">Belum ada artikel ilmiah.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $articles->links() }}
    </div>
</div>
