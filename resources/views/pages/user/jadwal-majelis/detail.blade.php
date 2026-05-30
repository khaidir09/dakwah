<x-user-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">

        <div class="xl:flex">

            <!-- Left + Middle content -->
            <div class="md:flex flex-1">

                <!-- Left content -->
                <x-community.feed-left-content />

                <!-- Middle content -->
                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">

                        <!-- Blocks -->
                        <div class="space-y-4">

                            <div class="flex justify-between items-center mb-6">
                                <!-- Title -->
                                <header>
                                    <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Detail Jadwal</h1>
                                </header>

                                <div>
                                    <a class="text-sm font-medium text-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400" href="{{ route('jadwal-majelis-list') }}">&lt;- Kembali</a>
                                </div>
                            </div>

                            @if(session('success'))
                                <div class="bg-emerald-100 text-emerald-600 p-4 rounded-lg mb-4">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <!-- Schedule Info -->
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-2">{{ $schedule->nama_jadwal }}</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ $schedule->deskripsi }}</p>

                                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                                    <div>
                                        <span class="text-gray-500 block">Majelis:</span>
                                        <a href="{{ route('majelis-detail', $schedule->assembly_id) }}" class="font-medium text-emerald-500 hover:text-emerald-600">{{ $schedule->assembly->nama_majelis }}</a>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block">Penceramah:</span>
                                        <span class="font-medium text-gray-800 dark:text-gray-100">{{ $schedule->teacher ? $schedule->teacher->name : '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block">Waktu:</span>
                                        <span class="font-medium text-gray-800 dark:text-gray-100">{{ $schedule->hari }}, {{ $schedule->waktu_formatted }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 block">Status:</span>
                                        <span class="font-medium text-gray-800 dark:text-gray-100">{{ $schedule->status }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Catatan -->
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-4">Catatan Jadwal</h3>

                                @auth
                                    <!-- Form Tambah Catatan -->
                                    <form action="{{ route('jadwal-majelis.notes.store', $schedule->id) }}" method="POST" class="mb-6">
                                        @csrf
                                        <div class="mb-4">
                                            <label for="content" class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Tulis Catatan</label>
                                            <textarea id="content" name="content" rows="3" class="form-textarea w-full" required placeholder="Tuliskan catatan kajian, hikmah, atau buku harian spiritual di sini..."></textarea>
                                        </div>
                                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                                            <div class="flex items-center space-x-4">
                                                <label class="flex items-center">
                                                    <input type="radio" name="visibility" value="Private" class="form-radio text-emerald-500" checked>
                                                    <span class="text-sm ml-2 text-gray-600 dark:text-gray-400">Privat (Hanya Anda)</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="radio" name="visibility" value="Public" class="form-radio text-emerald-500">
                                                    <span class="text-sm ml-2 text-gray-600 dark:text-gray-400">Publik (Dibaca Jamaah Lain)</span>
                                                </label>
                                            </div>
                                            <button type="submit" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white w-full md:w-auto">Simpan Catatan</button>
                                        </div>
                                    </form>
                                @else
                                    <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-lg mb-6 border border-gray-100 dark:border-gray-700/60 text-center">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Silakan <a href="{{ route('login') }}" class="text-emerald-500 font-medium hover:underline">login</a> untuk menulis catatan pada jadwal ini.</p>
                                    </div>
                                @endauth

                                <!-- List Catatan -->
                                <div class="space-y-4" x-data="{ activeNote: {{ $notes->count() > 0 ? $notes->first()->id : 'null' }} }">
                                    @forelse($notes as $note)
                                        <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-lg border border-gray-100 dark:border-gray-700/60 transition-all duration-200">
                                            <!-- Accordion Header -->
                                            <div class="cursor-pointer group" @click="activeNote = activeNote === {{ $note->id }} ? null : {{ $note->id }}">
                                                
                                                <!-- BAGIAN YANG DIPERBAIKI -->
                                                <div class="flex justify-between items-start mb-2 gap-4">
                                                    
                                                    <!-- Grup Kiri: Nama, Waktu, Status, Hapus -->
                                                    <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-3 w-full">
                                                        
                                                        <!-- Nama & Waktu -->
                                                        <div class="flex items-center">
                                                            <div class="font-medium text-gray-800 dark:text-gray-100 mr-2">{{ $note->user->name }}</div>
                                                            <div class="text-xs text-gray-500">{{ $note->created_at->locale('id')->translatedFormat('d F Y') }}</div>
                                                        </div>

                                                        <!-- Badge Status & Hapus (Turun di HP, Sejajar di MD) -->
                                                        <!-- Menggunakan gap-2 flex-wrap menggantikan space-x-2 agar aman saat turun baris -->
                                                        <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
                                                            @if($note->visibility === 'Private')
                                                                <span class="text-gray-500 bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded">Privat</span>
                                                            @else
                                                                @if($note->status === 'Pending')
                                                                    <span class="text-yellow-600 bg-yellow-100 dark:bg-yellow-900 px-2 py-1 rounded">Menunggu Moderasi</span>
                                                                @elseif($note->status === 'Approved')
                                                                    <span class="text-emerald-600 bg-emerald-100 dark:bg-emerald-900 px-2 py-1 rounded">Publik</span>
                                                                @endif
                                                            @endif

                                                            @auth
                                                                @if(auth()->id() === $note->user_id)
                                                                    <form action="{{ route('jadwal-majelis.notes.destroy', $note->id) }}" method="POST" class="inline-block" @click.stop onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan ini?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="text-red-500 hover:text-red-600">Hapus</button>
                                                                    </form>
                                                                @endif
                                                            @endauth
                                                        </div>

                                                    </div>

                                                    <!-- Chevron (Selalu diam di Kanan Atas) -->
                                                    <div class="flex-shrink-0 mt-0.5">
                                                        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-transform duration-200" 
                                                            :class="{'rotate-180': activeNote === {{ $note->id }}}" 
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </div>
                                                    
                                                </div>
                                                <!-- END BAGIAN YANG DIPERBAIKI -->

                                                <!-- Snippet (visible only when collapsed) -->
                                                <div x-show="activeNote !== {{ $note->id }}" class="text-sm text-gray-500 dark:text-gray-400 truncate">
                                                    {{ \Illuminate\Support\Str::limit($note->content, 60) }}
                                                </div>
                                            </div>

                                            <!-- Accordion Content -->
                                            <div x-show="activeNote === {{ $note->id }}" x-collapse x-cloak>
                                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700/60 format lg:format-lg dark:format-invert format-blue max-w-none prose dark:prose-invert text-gray-600 dark:text-gray-400 whitespace-pre-wrap text-justify">{{ $note->content }}</div>

                                                @if($note->visibility === 'Public' && $note->status === 'Approved')
                                                    <!-- Comments Section -->
                                                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700/60">
                                                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-4">Komentar / Koreksi</h4>

                                                        <!-- List of comments -->
                                                        <div class="space-y-3 mb-4">
                                                            @forelse($note->comments as $comment)
                                                                <div class="bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700/60 text-sm">
                                                                    <div class="flex justify-between items-start mb-1">
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $comment->user->name }}</span>
                                                                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                                                                        </div>
                                                                        @auth
                                                                            @if(auth()->id() === $comment->user_id)
                                                                                <form action="{{ route('jadwal-majelis.notes.comments.destroy', $comment->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus komentar ini?');">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit" class="text-xs text-red-500 hover:text-red-600">Hapus</button>
                                                                                </form>
                                                                            @endif
                                                                        @endauth
                                                                    </div>
                                                                    <div class="text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $comment->content }}</div>
                                                                </div>
                                                            @empty
                                                                <p class="text-xs text-gray-500 italic">Belum ada komentar atau koreksi.</p>
                                                            @endforelse
                                                        </div>

                                                        <!-- Add comment form -->
                                                        @auth
                                                            <form action="{{ route('jadwal-majelis.notes.comments.store', $note->id) }}" method="POST">
                                                                @csrf
                                                                <div class="flex flex-col sm:flex-row gap-2">
                                                                    <input type="text" name="content" required placeholder="Tambahkan komentar atau koreksi..." class="form-input text-sm w-full sm:flex-1 py-2 px-3 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-emerald-500 focus:ring-emerald-500">
                                                                    <button type="submit" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white text-sm py-2 px-4 w-full sm:w-auto">Kirim</button>
                                                                </div>
                                                            </form>
                                                        @else
                                                            <p class="text-xs text-gray-500 mt-2">Silakan <a href="{{ route('login') }}" class="text-emerald-500 hover:underline">login</a> untuk menambahkan komentar.</p>
                                                        @endauth
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-6">
                                            <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada catatan untuk jadwal ini.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

            <!-- Right content -->
            <x-community.feed-right-content />

        </div>

    </div>
</x-user-layout>