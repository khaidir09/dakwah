<div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-100 dark:border-gray-700/60 overflow-hidden">
    <div class="p-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            Tanya AI tentang buku ini
        </h3>
    </div>

    <div class="h-96 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900/50" id="chat-messages">
        @if(empty($messages))
            <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                <p>Belum ada percakapan. Mulai dengan bertanya sesuatu tentang buku ini!</p>
            </div>
        @endif

        @foreach($messages as $msg)
            <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] rounded-lg p-3 {{ $msg['role'] === 'user' ? 'bg-indigo-500 text-white' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 shadow-sm border border-gray-100 dark:border-gray-600' }}">
                    <p class="text-sm">{{ $msg['content'] }}</p>
                </div>
            </div>
        @endforeach

        <!-- Loading State -->
        <div wire:loading wire:target="sendMessage" class="flex justify-start w-full">
             <div class="bg-gray-200 dark:bg-gray-700 rounded-lg p-3 animate-pulse">
                <div class="flex space-x-1">
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce delay-100"></div>
                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce delay-200"></div>
                </div>
             </div>
        </div>
    </div>

    <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
        <form wire:submit.prevent="sendMessage" class="flex gap-2">
            <input type="text" wire:model="input" placeholder="Tulis pertanyaan Anda..." class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500 p-2">
            <button type="submit" wire:loading.attr="disabled" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                <svg wire:loading.remove wire:target="sendMessage" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                 <svg wire:loading wire:target="sendMessage" class="animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </form>
    </div>
</div>

@script
<script>
    $wire.on('message-sent', () => {
        const chatContainer = document.getElementById('chat-messages');
        if(chatContainer) {
             chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    });
</script>
@endscript
