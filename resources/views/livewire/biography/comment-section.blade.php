<div class="mt-5 pt-3 border-t border-gray-100 dark:border-gray-700/60">
    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-3">Komentar ({{ $comments->count() }})</h3>

    @if($comments->count() > 0)
    <ul class="space-y-2 mb-3">
        @foreach($comments as $comment)
        <!-- Comment -->
        <li class="p-3 bg-gray-50 dark:bg-gray-700/30 rounded-sm">
            <div class="flex items-start space-x-3">
                <img class="rounded-full shrink-0 object-cover" src="{{ $comment->user->profile_photo_url }}" width="32" height="32" alt="{{ $comment->user->name }}" />
                <div>
                    <div class="text-xs text-gray-500">
                        <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $comment->user->name }}</span> · {{ $comment->created_at->diffForHumans() }}
                    </div>
                    <div class="text-sm text-gray-800 dark:text-gray-200 mt-1">
                        {{ $comment->body }}
                    </div>
                </div>
            </div>
        </li>
        @endforeach
    </ul>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Belum ada komentar.</p>
    @endif

    <!-- Comment form -->
    @auth
    <div class="flex items-center space-x-3 mt-3">
        <img class="rounded-full shrink-0 object-cover" src="{{ auth()->user()->profile_photo_url }}" width="32" height="32" alt="{{ auth()->user()->name }}" />
        <div class="grow">
            <form wire:submit.prevent="save">
                <label for="comment-form" class="sr-only">Tulis komentar...</label>
                <div class="relative">
                    <input
                        wire:model="body"
                        id="comment-form"
                        class="form-input w-full bg-gray-100 dark:bg-gray-700 border-transparent dark:border-transparent focus:bg-white dark:focus:bg-gray-800 placeholder-gray-500 rounded-md pr-10"
                        type="text"
                        placeholder="Tulis komentar..."
                    >
                    <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-emerald-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                            <path d="M3.478 2.404a.75.75 0 00-.926.941l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.404z" />
                        </svg>
                    </button>
                </div>
                @error('body') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </form>
        </div>
    </div>
    @else
    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-sm text-emerald-500 hover:text-emerald-600 font-medium">Masuk untuk memberi komentar</a>
    </div>
    @endauth
</div>
