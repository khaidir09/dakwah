<div x-data="{ deleteOpen: false, deleteAction: '' }">
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Kitab Ulama</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            <select wire:model.live="status" class="form-select bg-white dark:bg-gray-800">
                <option value="">Semua Status</option>
                <option value="active">Tayang</option>
                <option value="draft">Draf</option>
            </select>

            <!-- Search form -->
            <x-search-form placeholder="Cari kitab..." />

            <!-- Add button -->
            <a href="{{ route('admin.kitab.create') }}" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white">
                <svg class="fill-current shrink-0 xs:hidden" width="16" height="16" viewBox="0 0 16 16">
                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                </svg>
                <span class="max-xs:sr-only">Tambah Kitab</span>
            </a>

        </div>

    </div>

    @if (session()->has('message'))
        <div x-show="open" x-data="{ open: true }" role="alert">
            <div class="px-4 py-2 rounded-lg text-sm bg-green-500 text-white mb-4">
                <div class="flex w-full justify-between items-start">
                    <div>{{ session('message') }}</div>
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

    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
        <header class="px-5 py-4">
            <h2 class="font-semibold text-gray-800 dark:text-gray-100">Semua Data <span class="text-gray-400 dark:text-gray-500 font-medium">{{ $books_count }}</span></h2>
        </header>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full dark:text-gray-300">
                <thead class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900/20 border-t border-b border-gray-100 dark:border-gray-700/60">
                    <tr>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Sampul</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Judul</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Guru</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Bidang</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Status</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Hak Cipta</div></th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Aksi</div></th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100 dark:divide-gray-700/60">
                    @forelse($books as $item)
                        <tr wire:key="book-{{ $item->id }}">
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if ($item->cover_thumb_url)
                                    <img class="w-10 h-14 object-cover rounded-md" src="{{ $item->cover_thumb_url }}" alt="{{ $item->title }}">
                                @else
                                    <div class="w-10 h-14 bg-gray-100 dark:bg-gray-700 rounded-md"></div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $item->title }}</div>
                                @if ($item->title_arabic)
                                    <div class="text-gray-500" dir="rtl" lang="ar">{{ $item->title_arabic }}</div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3">
                                @foreach ($item->teachers as $teacher)
                                    <div>{{ $teacher->name }} <span class="text-xs text-gray-500">({{ $roles[$teacher->pivot->role] ?? $teacher->pivot->role }})</span></div>
                                @endforeach
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $item->specialization?->name ?? '-' }}</td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $item->is_active ? 'Tayang' : 'Draf' }}
                                </span>
                                @if ($item->is_featured)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Unggulan</span>
                                @endif
                            </td>
                            @php
                                $copyrightStatus = $item->copyrightStatus();
                                $publicDomainYear = $item->publicDomainYear();
                            @endphp
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <span @class([
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    'bg-green-100 text-green-800' => $copyrightStatus === 'domain_publik',
                                    'bg-amber-100 text-amber-800' => $copyrightStatus === 'dilindungi',
                                    'bg-gray-100 text-gray-600' => $copyrightStatus === 'belum_diketahui',
                                ])>{{ $item->copyright_status_label }}</span>
                                <div class="text-xs text-gray-500 mt-1">
                                    @if ($copyrightStatus === 'domain_publik')
                                        sejak 1 Jan {{ $publicDomainYear }}
                                    @elseif ($publicDomainYear)
                                        hingga 31 Des {{ $publicDomainYear - 1 }}
                                    @elseif ($copyrightStatus === 'dilindungi')
                                        pencipta belum tercatat wafat
                                    @else
                                        tanpa pengarang/pensyarah
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('kitab-detail', $item) }}" target="_blank" class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400">Lihat</a>
                                    <a href="{{ route('admin.kitab.edit', $item) }}" class="text-violet-500 hover:text-violet-600">Edit</a>
                                    <button type="button" class="text-red-500 hover:text-red-600" @click="deleteAction = @js(route('admin.kitab.destroy', $item)); deleteOpen = true">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-6 text-center text-gray-500">Belum ada data kitab.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">
        {{ $books->links() }}
    </div>

    <!-- Konfirmasi hapus: menghapus lewat BookController::destroy agar logika hapus (file sampul, cache) hanya ada di satu tempat. -->
    <div class="fixed inset-0 bg-gray-900/30 z-50 transition-opacity" x-show="deleteOpen" aria-hidden="true" x-cloak></div>
    <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="deleteOpen" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-auto max-w-lg w-full max-h-full p-5" @click.outside="deleteOpen = false" @keydown.escape.window="deleteOpen = false">
            <div class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Hapus kitab?</div>
            <p class="text-sm mb-8">Jika sudah terhapus, maka tidak bisa dikembalikan lagi. Data guru tidak ikut terhapus.</p>
            <form method="POST" :action="deleteAction" class="flex flex-wrap justify-end space-x-2">
                @csrf
                @method('DELETE')
                <button type="button" class="btn-sm border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300" @click="deleteOpen = false">Batal</button>
                <button type="submit" class="btn-sm bg-red-500 hover:bg-red-600 text-white">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>
