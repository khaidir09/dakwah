<div 
    class="bg-white rounded-lg shadow-md h-[500px] flex flex-col" 
    x-data="{ scrollBottom() { $nextTick(() => { $refs.chatContainer.scrollTop = $refs.chatContainer.scrollHeight; }); } }"
    x-init="scrollBottom()" 
>
    <div class="p-4 border-b bg-gray-50 rounded-t-lg">
        <h3 class="font-semibold text-gray-700 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            Tanya Pustaka AI
        </h3>
    </div>

    <div 
        x-ref="chatContainer"
        class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50"
    >
        @if($messages->isEmpty())
            <div class="text-center text-gray-400 mt-10 text-sm">
                Belum ada percakapan. Mulai diskusi!
            </div>
        @else
            @foreach($messages as $msg)
                <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-lg p-3 text-sm {{ $msg->role === 'user' ? 'bg-blue-600 text-white' : 'bg-white border text-gray-800 shadow-sm' }}">
                        <p>{!! nl2br(e($msg->message)) !!}</p>
                        <span class="text-xs opacity-70 block mt-1 text-right">
                            {{ $msg->created_at->format('H:i') }}
                        </span>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="p-4 border-t bg-white rounded-b-lg">
        <form wire:submit.prevent="ask" class="flex gap-2" @submit="scrollBottom()">
            <input 
                wire:model="question" 
                type="text" 
                placeholder="Tanyakan isi buku..." 
                class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                {{ $isLoading ? 'disabled' : '' }}
            >
            <button 
                type="submit" 
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                {{ $isLoading ? 'disabled' : '' }}
            >
                Kirim
            </button>
        </form>
    </div>
</div>