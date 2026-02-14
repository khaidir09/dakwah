<div>
    <div class="p-6 space-y-6">
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold">Kelola Artikel Ilmiah</h2>
            <a href="{{ route('kelola-artikel.create') }}" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white">
                <svg class="fill-current shrink-0 xs:hidden" width="16" height="16" viewBox="0 0 16 16">
                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                </svg>
                <span class="max-xs:hidden">Tambah Artikel</span>
            </a>
        </div>

        @if (session()->has('message'))
            <div class="mb-4 px-4 py-2 rounded-lg text-sm bg-green-500 text-white">
                {{ session('message') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl mb-8">
            <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60 flex justify-between items-center">
                <h2 class="font-semibold text-gray-800 dark:text-gray-100">Daftar Artikel <span class="text-gray-400 dark:text-gray-500 font-medium ml-1">({{ $articles_count }})</span></h2>
            </header>
            <div class="p-3">
                <div class="overflow-x-auto">
                    <table class="table-auto w-full dark:text-gray-300">
                        <thead class="text-xs font-semibold uppercase text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700/20 border-t border-b border-gray-100 dark:border-gray-700/60">
                            <tr>
                                <th class="p-2 whitespace-nowrap">
                                    <div class="font-semibold text-left">Judul</div>
                                </th>
                                <th class="p-2 whitespace-nowrap">
                                    <div class="font-semibold text-left">Yayasan</div>
                                </th>
                                <th class="p-2 whitespace-nowrap">
                                    <div class="font-semibold text-left">Penulis</div>
                                </th>
                                <th class="p-2 whitespace-nowrap">
                                    <div class="font-semibold text-center">Status</div>
                                </th>
                                <th class="p-2 whitespace-nowrap">
                                    <div class="font-semibold text-center">Tanggal Terbit</div>
                                </th>
                                <th class="p-2 whitespace-nowrap">
                                    <div class="font-semibold text-center">Aksi</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-100 dark:divide-gray-700/60">
                            @forelse($articles as $article)
                                <tr>
                                    <td class="p-2 whitespace-nowrap">
                                        <div class="font-medium text-gray-800 dark:text-gray-100">{{ $article->title }}</div>
                                        @if($article->subtitle)
                                            <div class="text-xs text-gray-500">{{ $article->subtitle }}</div>
                                        @endif
                                    </td>
                                    <td class="p-2 whitespace-nowrap">
                                        <div class="text-left">{{ $article->foundation->name ?? '-' }}</div>
                                    </td>
                                    <td class="p-2 whitespace-nowrap">
                                        <div class="text-left">{{ $article->author_name }}</div>
                                    </td>
                                    <td class="p-2 whitespace-nowrap">
                                        <div class="text-center">
                                            @if($article->status == 'PUBLISHED')
                                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Published</span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Draft</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-2 whitespace-nowrap">
                                        <div class="text-center">{{ $article->published_at ? \Carbon\Carbon::parse($article->published_at)->format('d M Y H:i') : '-' }}</div>
                                    </td>
                                    <td class="p-2 whitespace-nowrap">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="{{ route('kelola-artikel.edit', $article->id) }}" class="btn-xs bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                                                Edit
                                            </a>
                                            <button wire:click="confirmDelete({{ $article->id }})" class="btn-xs bg-red-500 text-white hover:bg-red-600">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-4 text-center text-sm text-gray-500">
                                        Belum ada artikel ilmiah.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $articles->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-confirmation-modal wire:model="confirmingDeletion">
        <x-slot name="title">
            Hapus Artikel
        </x-slot>

        <x-slot name="content">
            Apakah Anda yakin ingin menghapus artikel ini? Data yang dihapus tidak dapat dikembalikan.
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingDeletion')" wire:loading.attr="disabled">
                Batal
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="deleteArticle" wire:loading.attr="disabled">
                Hapus Artikel
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
