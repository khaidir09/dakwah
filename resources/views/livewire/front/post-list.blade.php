<div>
    <!-- Search and Filter -->
    <div class="mb-6 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input wire:model.live="search" type="text" class="form-input w-full" placeholder="Cari tulisan...">
        </div>
        <div class="sm:w-1/4">
            <select wire:model.live="label" class="form-select w-full">
                <option value="">Semua Label</option>
                @foreach ($labels as $lbl)
                    <option value="{{ $lbl->slug }}">{{ $lbl->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Grid -->
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-2">
        @forelse ($posts as $post)
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden hover:shadow-md transition-shadow">
                <a href="{{ route('tulisan.detail', $post->slug) }}" class="block">
                    @if ($post->cover_image)
                        <img src="{{ Storage::url($post->cover_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-gray-400 dark:text-gray-500">No Image</span>
                        </div>
                    @endif
                </a>
                <div class="p-4">
                    <div class="mb-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $post->published_at ? $post->published_at->locale('id')->translatedFormat('d M Y') : $post->created_at->locale('id')->translatedFormat('d M Y') }}</span>
                        <span>{{ $post->user->name }}</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2 line-clamp-2">
                        <a href="{{ route('tulisan.detail', $post->slug) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            {{ $post->title }}
                        </a>
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-3 mb-4 text-justify">
                        {{ Str::limit(strip_tags($post->content), 100) }}
                    </p>
                    <div class="flex flex-wrap gap-1">
                        @foreach ($post->labels as $label)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $label->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">Belum ada tulisan.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $posts->links() }}
    </div>
</div>
