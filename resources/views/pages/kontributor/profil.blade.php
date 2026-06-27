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
                    <img class="w-24 h-24 rounded-full object-cover shrink-0" src="{{ $kontributor->profile_photo_url }}" alt="{{ $kontributor->name }}">
                    <div class="text-center sm:text-left">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $kontributor->name }}</h1>

                        @if($kontributor->badge_title)
                            <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                {{ $kontributor->badge_title }}
                            </span>
                        @endif

                        @if($kontributor->city || $kontributor->province)
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ collect([$kontributor->city?->name, $kontributor->province?->name])->filter()->join(', ') }}
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
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 text-center">
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['guru'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Guru</div>
                </div>
                @if($stats['majelis'] > 0)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 text-center">
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['majelis'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Majelis</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 text-center">
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['jadwal'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Jadwal Pengajian</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 text-center">
                        <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['acara'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Acara</div>
                    </div>
                @endif
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 text-center">
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['amalan'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Amalan</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 text-center">
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $stats['catatan'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Catatan Pengajian</div>
                </div>
            </div>

            {{-- Daftar kontribusi --}}
            @if($stats['majelis'] + $stats['guru'] + $stats['amalan'] + $stats['jadwal'] + $stats['acara'] + $stats['catatan'] === 0)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Belum ada kontribusi yang tayang publik.
                </div>
            @else
                <div class="space-y-8">

                    @if($teachers->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Guru</h2>
                            <ul class="space-y-2">
                                @foreach($teachers as $teacher)
                                    <li>
                                        <a href="{{ route('guru-detail', $teacher) }}" class="block bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 px-4 py-3 hover:border-emerald-300 dark:hover:border-emerald-700">
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $teacher->name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($assemblies->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Majelis</h2>
                            <ul class="space-y-2">
                                @foreach($assemblies as $assembly)
                                    <li>
                                        <a href="{{ route('majelis-detail', $assembly->id) }}" class="block bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 px-4 py-3 hover:border-emerald-300 dark:hover:border-emerald-700">
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $assembly->nama_majelis }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($schedules->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Jadwal Pengajian</h2>
                            <ul class="space-y-2">
                                @foreach($schedules as $schedule)
                                    <li>
                                        <a href="{{ route('jadwal-detail', $schedule->id) }}" class="block bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 px-4 py-3 hover:border-emerald-300 dark:hover:border-emerald-700">
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $schedule->title }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($events->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Acara</h2>
                            <ul class="space-y-2">
                                @foreach($events as $event)
                                    <li>
                                        <a href="{{ route('acara-detail', $event->id) }}" class="block bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 px-4 py-3 hover:border-emerald-300 dark:hover:border-emerald-700">
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $event->title }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($wirids->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Amalan</h2>
                            <ul class="space-y-2">
                                @foreach($wirids as $wirid)
                                    <li>
                                        <a href="{{ route('wirid-list', ['search' => $wirid->nama]) }}" class="block bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 px-4 py-3 hover:border-emerald-300 dark:hover:border-emerald-700">
                                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $wirid->nama }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($notes->isNotEmpty())
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Catatan Pengajian</h2>
                            <ul class="space-y-2">
                                @foreach($notes as $note)
                                    <li>
                                        <a href="{{ route('catatan-pengajian.detail', $note->id) }}" class="block bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700 px-4 py-3 hover:border-emerald-300 dark:hover:border-emerald-700">
                                            <span class="block text-sm font-medium text-gray-800 dark:text-gray-100">{{ $note->schedule->nama_jadwal ?? 'Jadwal Majelis' }}</span>
                                            <span class="block mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $note->schedule->assembly->nama_majelis ?? 'Majelis' }}</span>
                                            <span class="block mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ $note->created_at->locale('id')->translatedFormat('d M Y') }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-user-layout>
