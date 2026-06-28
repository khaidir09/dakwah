<x-user-layout>
    @section('title', 'Profil ' . $kontributor->name)

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">

            <div>
                <a href="{{ route('kontributor.index') }}" class="text-sm font-medium text-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400">&lt;- Program Kontributor</a>
            </div>

            {{-- Identitas & Badge --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                    <img class="w-24 h-24 rounded-full object-cover shrink-0" src="{{ Storage::url($kontributor->profile_photo_path) }}" alt="{{ $kontributor->name }}">
                    <div class="text-center sm:text-left">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $kontributor->name }}</h1>

                        @if($kontributor->badge_title)
                            <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                {{ $kontributor->badge_title }}
                            </span>
                        @endif

                        @if($kontributor->city || $kontributor->province)
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ collect([$kontributor->village?->name, $kontributor->city?->name, $kontributor->province?->name])->filter()->join(', ') }}
                            </div>
                        @endif

                        <div class="mt-3 flex flex-col sm:flex-row sm:items-center gap-1 text-sm">
                            <div class="text-gray-600 dark:text-gray-300">
                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ number_format($kontributor->total_khidmah_points) }}</span> Poin Khidmah
                            </div>
                            @if($kontributor->kontributor_since)
                                <div class="text-gray-500 dark:text-gray-400">
                                     Bergabung {{ $kontributor->kontributor_since->locale('id')->translatedFormat('F Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistik ringkas --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-xl shadow-sm p-5 text-white text-center">
                    <div class="text-2xl font-bold">{{ $stats['guru'] }}</div>
                    <div class="text-xs mt-1">Guru</div>
                </div>
                @if($stats['majelis'] > 0)
                    <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-xl shadow-sm p-5 text-white text-center">
                        <div class="text-2xl font-bold">{{ $stats['majelis'] }}</div>
                        <div class="text-xs mt-1">Majelis</div>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-xl shadow-sm p-5 text-white text-center">
                        <div class="text-2xl font-bold">{{ $stats['jadwal'] }}</div>
                        <div class="text-xs mt-1">Jadwal Pengajian</div>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-xl shadow-sm p-5 text-white text-center">
                        <div class="text-2xl font-bold">{{ $stats['acara'] }}</div>
                        <div class="text-xs mt-1">Acara</div>
                    </div>
                @endif
                <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-xl shadow-sm p-5 text-white text-center">
                    <div class="text-2xl font-bold">{{ $stats['amalan'] }}</div>
                    <div class="text-xs mt-1">Amalan</div>
                </div>
                <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-xl shadow-sm p-5 text-white text-center">
                    <div class="text-2xl font-bold">{{ $stats['catatan'] }}</div>
                    <div class="text-xs mt-1">Catatan Pengajian</div>
                </div>
            </div>

            {{-- Daftar kontribusi --}}
            @if($stats['majelis'] + $stats['guru'] + $stats['amalan'] + $stats['jadwal'] + $stats['acara'] + $stats['catatan'] === 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Belum ada kontribusi yang tayang publik.
                </div>
            @else
                <div class="space-y-10">

                    {{-- Kartu meniru desain halaman data publik, namun tanpa memuat gambar (diganti placeholder) agar ringan. --}}

                    @if($teachers->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Guru <span class="ml-1 inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $teachers->count() }}</span></h2>
                            <div class="grid grid-cols-12 gap-4">
                                @foreach($teachers as $teacher)
                                    <a href="{{ route('guru-detail', $teacher) }}" class="col-span-full sm:col-span-6 xl:col-span-4 bg-white dark:bg-gray-800 shadow-xs rounded-xl border border-gray-100 dark:border-gray-700/60 hover:border-emerald-300 dark:hover:border-emerald-700 transition">
                                        <div class="p-5 text-center">
                                            <div class="flex justify-center mb-3">
                                                @if($teacher->foto)
                                                    <img class="w-16 h-16 rounded-full object-cover" src="{{ Storage::url($teacher->foto) }}" width="64" height="64" loading="lazy" alt="{{ $teacher->name }}" />
                                                @else
                                                    <div class="w-16 h-16 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-full text-gray-400">
                                                        <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <h3 class="text-md font-semibold text-gray-800 dark:text-gray-100">{{ $teacher->name }}</h3>
                                            @if($teacher->tahun_lahir)
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    @if($teacher->wafat_masehi === null)
                                                        {{ $teacher->tahun_lahir }} ({{ date('Y') - $teacher->tahun_lahir }} tahun)
                                                    @else
                                                        {{ $teacher->tahun_lahir }}
                                                    @endif
                                                </div>
                                            @endif
                                            @if($teacher->village?->name)
                                                <div class="text-sm font-medium mt-2 text-gray-600 dark:text-gray-300">{{ $teacher->village->name }}</div>
                                            @endif
                                            <div class="mt-3">
                                                @if($teacher->wafat_masehi === null)
                                                    <span class="text-xs inline-flex font-medium bg-violet-500/20 text-violet-600 rounded-full px-2.5 py-1">Aktif</span>
                                                @else
                                                    <span class="text-xs inline-flex font-medium bg-red-500/20 text-red-700 rounded-full px-2.5 py-1">Wafat {{ $teacher->wafat_masehi }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($assemblies->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Majelis <span class="ml-1 inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $assemblies->count() }}</span></h2>
                            <div class="grid grid-cols-12 gap-4">
                                @foreach($assemblies as $assembly)
                                    <a href="{{ route('majelis-detail', $assembly->id) }}" class="col-span-full md:col-span-6 bg-white dark:bg-gray-800 shadow-xs rounded-xl border border-gray-100 dark:border-gray-700/60 hover:border-emerald-300 dark:hover:border-emerald-700 transition">
                                        <div class="p-5">
                                            <div class="flex justify-between items-start mb-1">
                                                <h3 class="text-lg text-gray-800 dark:text-gray-100 font-semibold">{{ $assembly->nama_majelis }}</h3>
                                                @if($assembly->tipe)
                                                    <span class="shrink-0 ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">{{ $assembly->tipe }}</span>
                                                @endif
                                            </div>
                                            <div class="font-semibold text-sm text-gray-700 dark:text-gray-300">Pengasuh : {{ $assembly->leader_name }}</div>
                                            @if($assembly->village?->name || $assembly->district?->name)
                                                <div class="flex items-center gap-2 mt-2">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M13 11V19H11V11H13Z" fill="#059669"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M6 7C6 3.68629 8.68629 1 12 1C15.3137 1 18 3.68629 18 7C18 10.3137 15.3137 13 12 13C8.68629 13 6 10.3137 6 7Z" fill="#059669"></path></svg>
                                                    <span class="text-sm font-medium text-emerald-600">{{ collect([$assembly->village?->name, $assembly->district?->name])->filter()->join(', ') }}</span>
                                                </div>
                                            @endif
                                            <div class="flex items-center gap-2 mt-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><rect x="6" width="2" height="5" fill="#059669"></rect><rect x="16" width="2" height="5" fill="#059669"></rect><path d="m20,3H4c-1.654,0-3,1.346-3,3v12c0,1.654,1.346,3,3,3h16c1.654,0,3-1.346,3-3V6c0-1.654-1.346-3-3-3Zm0,16H4c-.551,0-1-.448-1-1v-9h18v9c0,.552-.449,1-1,1Z" fill="#059669"></path></svg>
                                                <span class="text-sm font-medium text-emerald-600">{{ $assembly->schedule->count() }} Jadwal Rutinan</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($schedules->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Jadwal Pengajian <span class="ml-1 inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $schedules->count() }}</span></h2>
                            <div class="grid grid-cols-12 gap-4">
                                @foreach($schedules as $schedule)
                                    @php
                                        $accessColor = match($schedule->access) {
                                            'Umum' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                            'Ikhwan' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                            'Akhwat' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <a href="{{ route('jadwal-majelis-detail', $schedule->id) }}" class="col-span-full xl:col-span-6 bg-white dark:bg-gray-800 shadow-xs rounded-xl border border-gray-100 dark:border-gray-700/60 hover:border-emerald-300 dark:hover:border-emerald-700 transition">
                                        <div class="p-5">
                                            <div class="flex justify-between items-start gap-3">
                                                <div class="flex">
                                                    <div class="w-14 h-14 mr-4 shrink-0 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-full text-gray-400">
                                                        <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" /></svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-lg leading-snug font-semibold text-gray-800 dark:text-gray-100">{{ $schedule->teacher?->name }}</h3>
                                                        <div class="text-sm text-gray-600 dark:text-gray-300">{{ $schedule->nama_jadwal }}</div>
                                                    </div>
                                                </div>
                                                @if($schedule->access)
                                                    <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $accessColor }}">{{ $schedule->access }}</span>
                                                @endif
                                            </div>
                                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
                                                @if($schedule->assembly?->nama_majelis)
                                                    <span class="inline-flex items-center gap-1.5 text-emerald-600 font-medium">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"><path fill-rule="evenodd" clip-rule="evenodd" d="M13 11V19H11V11H13Z" fill="#059669"></path><path fill-rule="evenodd" clip-rule="evenodd" d="M6 7C6 3.68629 8.68629 1 12 1C15.3137 1 18 3.68629 18 7C18 10.3137 15.3137 13 12 13C8.68629 13 6 10.3137 6 7Z" fill="#059669"></path></svg>
                                                        {{ $schedule->assembly->nama_majelis }}
                                                    </span>
                                                @endif
                                                <span>{{ $schedule->hari }}, {{ $schedule->waktu_formatted }} WITA</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($events->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Acara <span class="ml-1 inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $events->count() }}</span></h2>
                            <div class="grid xl:grid-cols-2 gap-4">
                                @foreach($events as $event)
                                    <article class="bg-white dark:bg-gray-800 shadow-xs rounded-xl border border-gray-100 dark:border-gray-700/60 p-5 flex flex-col">
                                        <div class="grow">
                                            <div class="text-sm font-semibold text-emerald-500 uppercase mb-1">{{ \Carbon\Carbon::parse($event->date)->locale('id')->translatedFormat('D, d M Y') }}</div>
                                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $event->name }}</h3>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ collect([$event->location, $event->village?->name, $event->district?->name])->filter()->join(', ') }}</div>
                                        </div>
                                        <div class="flex justify-between items-center mt-3">
                                            @if($event->category)
                                                <span class="text-xs inline-flex items-center font-medium border border-gray-200 dark:border-gray-700/60 text-gray-600 dark:text-gray-400 rounded-full px-2.5 py-1">{{ $event->category }}</span>
                                            @endif
                                            @if($event->maps_link)
                                                <a href="{{ $event->maps_link }}" target="_blank" class="inline-flex items-center text-xs font-medium border border-emerald-500 dark:border-emerald-400 text-emerald-600 dark:text-emerald-400 rounded-full px-3 py-1 hover:bg-emerald-50 dark:hover:bg-emerald-900 transition">Maps &rarr;</a>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($wirids->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Amalan <span class="ml-1 inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $wirids->count() }}</span></h2>
                            <div class="space-y-3">
                                @foreach($wirids as $wirid)
                                    <a href="{{ route('wirid-list', ['search' => $wirid->nama]) }}" class="block bg-white dark:bg-gray-800 shadow-xs rounded-xl border border-gray-100 dark:border-gray-700/60 hover:border-emerald-300 dark:hover:border-emerald-700 transition p-5">
                                        <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">{{ $wirid->nama }}</h3>
                                        @if($wirid->jumlah || $wirid->waktu)
                                            <div class="mt-2">
                                                <span class="text-xs inline-flex font-medium bg-green-500/20 text-green-700 rounded-full px-2.5 py-1">Dibaca {{ $wirid->jumlah }} kali pada waktu {{ $wirid->waktu }}</span>
                                            </div>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($notes->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Catatan Pengajian <span class="ml-1 inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $notes->count() }}</span></h2>
                            <div class="space-y-3">
                                @foreach($notes as $note)
                                    <a href="{{ route('catatan-pengajian.detail', $note->id) }}" class="block bg-white dark:bg-gray-800 shadow-xs rounded-xl border border-gray-100 dark:border-gray-700/60 hover:border-emerald-300 dark:hover:border-emerald-700 transition p-5">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">{{ $note->schedule->nama_jadwal ?? 'Jadwal Majelis' }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $note->created_at->locale('id')->translatedFormat('d M Y') }}</span>
                                        </div>
                                        <h3 class="font-bold text-gray-800 dark:text-gray-100">{{ $note->schedule->assembly->nama_majelis ?? 'Majelis' }}</h3>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-user-layout>
