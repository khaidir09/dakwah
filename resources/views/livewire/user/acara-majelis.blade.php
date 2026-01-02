<div>
    <!-- Page header -->
    <div class="p-6 bg-white dark:bg-gray-800">

        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold">Acara Majelis</h2>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Add customer button -->
                <a href="{{ route('kelola-acara-majelis.create') }}" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white">
                    <svg class="fill-current shrink-0 xs:hidden" width="16" height="16" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="max-xs:sr-only">Tambah Acara Majelis</span>
                </a>                      
                
            </div>
        </div>

        @if (session()->has('message'))
        <div x-show="open" x-data="{ open: true }" role="alert">
            <div class="px-4 py-2 rounded-lg text-sm bg-green-500 text-white mb-4">
                <div class="flex w-full justify-between items-start">
                    <div class="flex">
                        <svg class="shrink-0 fill-current opacity-80 mt-[3px] mr-3" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM7 11.4L3.6 8 5 6.6l2 2 4-4L12.4 6 7 11.4z" />
                        </svg>
                        <div>{{ session('message') }}</div>
                    </div>
                    <button class="opacity-60 hover:opacity-70 ml-3 mt-[3px]" @click="open = false">
                        <div class="sr-only">Close</div>
                        <svg class="fill-current" width="16" height="16" viewBox="0 0 16 16">
                            <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-emerald-600 shadow-sm rounded-tl-xl rounded-tr-xl">
        <header class="px-5 py-4">
            <h2 class="font-semibold text-white">Semua Acara Majelis <span class="text-gray-400 font-medium">{{ $events_count }}</span></h2>
        </header>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full dark:text-gray-300">
                <!-- Table header -->
                <thead class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-100 bg-gray-50 dark:bg-gray-900/90 border-t border-b border-gray-100 dark:border-gray-700/90">
                    <tr>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-center">No.</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Nama Acara</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Waktu</div>
                        </th>
                         <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Lokasi</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Akses</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Kategori</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Poster</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Aksi</div>
                        </th>
                    </tr>
                </thead>
                <!-- Table body -->
                <tbody class="text-sm text-gray-500 dark:text-gray-100 bg-gray-50 dark:bg-gray-900/90 divide-y divide-gray-100 dark:divide-gray-700/90">
                    @php
                        $i = 1;
                    @endphp
                    <!-- Row -->
                    @foreach($events as $item)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium text-center">{{ $i++ }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="text-left font-medium">{{ $item->name }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="text-left">{{ \Carbon\Carbon::parse($item->date)->translatedFormat('d F Y H:i') }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="text-left">{{ $item->location }}</div>
                                <div class="text-xs text-gray-500">{{ $item->city?->name }}, {{ $item->province?->name }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if ($item->access === 'Umum')
                                    <div class="text-sm inline-flex font-medium bg-green-500/20 text-green-700 rounded-full text-center px-2.5 py-1">Umum</div>
                                @else
                                    <div class="text-sm inline-flex font-medium bg-amber-500/20 text-amber-700 rounded-full text-center px-2.5 py-1">Khusus</div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="text-left">{{ $item->category }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="text-left">
                                    @if ($item->image)
                                        <img class="rounded-md" src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}">
                                    @else
                                        <div class="w-16 h-10 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-md text-gray-400">
                                            <span class="text-xs">No Img</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="space-x-1 flex">
                                    <a href="{{ route('kelola-acara-majelis.edit', $item->id) }}" class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400 rounded-full">
                                        <span class="sr-only">Edit</span>
                                        <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                            <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                        </svg>
                                    </a>
                                    <button
                                        wire:click="confirmDelete({{ $item->id }})"
                                        class="text-red-500 hover:text-red-600 rounded-full"
                                        aria-controls="danger-modal">
                                        <span class="sr-only">Delete</span>
                                        <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                            <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                            <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>                    
                    @endforeach
                    <div x-data="{ deleteOpen: @entangle('confirmingDeletion').live }">
                        <div
                            class="fixed inset-0 bg-gray-900/30 z-50 transition-opacity"
                            x-show="deleteOpen"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-out duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            aria-hidden="true"
                            x-cloak
                        ></div>
                        <div
                            id="danger-modal"
                            class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                            role="dialog"
                            aria-modal="true"
                            x-show="deleteOpen"
                            x-transition:enter="transition ease-in-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-4"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in-out duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-4"
                            x-cloak
                        >
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="deleteOpen = false" @keydown.escape.window="deleteOpen = false">
                                <div class="p-5 flex space-x-4">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-gray-100 dark:bg-gray-700">
                                        <svg class="shrink-0 fill-current text-red-500" width="16" height="16" viewBox="0 0 16 16">
                                            <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="mb-2">
                                            <div class="text-lg font-semibold text-gray-800 dark:text-gray-100">Hapus data?</div>
                                        </div>
                                        <div class="text-sm mb-10 w-full">
                                            <div class="space-y-2">
                                                <p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap justify-end space-x-2">
                                            <button class="btn-sm border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300" @click="deleteOpen = false">Batal</button>
                                            
                                            <button 
                                                wire:click="deleteEvent" 
                                                wire:loading.attr="disabled"
                                                wire:target="deleteEvent"
                                                class="btn-sm bg-red-500 hover:bg-red-600 text-white"
                                            >
                                                <span wire:loading.remove wire:target="deleteEvent">Ya, Hapus</span>
                                                <span wire:loading wire:target="deleteEvent">Menghapus...</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </tbody>
            </table>

        </div>
    </div>

    <div class="mt-8">
        {{ $events->links() }}
    </div>

    </div>
</div>