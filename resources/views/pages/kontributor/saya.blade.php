<x-dashboard-layout>
    <div class="grow">
        <div class="p-6 space-y-6">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
            @endif

            {{-- Header --}}
            <div class="sm:flex sm:justify-between sm:items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Dashboard Kontributor</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola semua kontribusi Anda</p>
                </div>
            </div>

            {{-- XP Card --}}
            <div class="grid md:grid-cols-3 gap-4">
                <div class="col-span-2 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl p-6 text-white">
                    <p class="text-sm opacity-80">Gelar Anda</p>
                    <h3 class="text-2xl font-bold mt-1">{{ $user->badge_title }}</h3>
                    <p class="text-sm opacity-80 mt-3">Total XP Khidmah</p>
                    <p class="text-4xl font-bold">{{ number_format($user->total_khidmah_points) }} <span class="text-xl">XP</span></p>
                    @php($nextThreshold = $user->nextBadgeThreshold())
                    @if($nextThreshold)
                        <div class="mt-4">
                            <div class="flex justify-between text-xs opacity-80 mb-1">
                                <span>Menuju badge berikutnya</span>
                                <span>{{ $user->total_khidmah_points }} / {{ $nextThreshold }} XP</span>
                            </div>
                            <div class="w-full bg-white/20 rounded-full h-2">
                                <div class="bg-white rounded-full h-2" style="width: {{ min(100, round($user->total_khidmah_points / $nextThreshold * 100)) }}%"></div>
                            </div>
                        </div>
                    @else
                        <p class="text-sm opacity-80 mt-4">Anda telah mencapai gelar tertinggi! 🎉</p>
                    @endif
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-100 dark:border-gray-700">
                    <p class="text-sm text-gray-500 mb-4">Tambah Kontribusi Baru</p>
                    <div class="space-y-2">
                        <a href="{{ route('kontributor.guru.create') }}" class="flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                            + Guru
                        </a>
                        <a href="{{ route('kontributor.majelis.create') }}" class="flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                            + Majelis
                        </a>
                        <a href="{{ route('kontributor.jadwal.create') }}" class="flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                            + Jadwal Majelis
                        </a>
                        <a href="{{ route('kontributor.acara.create') }}" class="flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                            + Acara
                        </a>
                        <a href="{{ route('kontributor.amalan.create') }}" class="flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                            + Amalan/Wirid
                        </a>
                        <a href="{{ route('kelola-catatan.index') }}" class="flex items-center text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                            + Catatan Pengajian
                        </a>
                    </div>
                </div>
            </div>

            {{-- Reward Kontributor --}}
            @include('pages.kontributor._reward')

            {{-- Riwayat Kontribusi --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100">Riwayat Kontribusi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-4 py-3">Jenis</th>
                                <th class="px-4 py-3">Judul / Nama</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">XP</th>
                                <th class="px-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            {{-- Acara (paginated) --}}
                            @foreach($acara as $a)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-gray-500">Acara</td>
                                <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $a->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $a->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    @include('pages.kontributor._status-badge', ['status' => $a->status])
                                </td>
                                <td class="px-4 py-3 text-right text-gray-800 dark:text-gray-100">
                                    {{ $a->contributions()->where('user_id', $user->id)->value('points_earned') ?? 0 }}
                                </td>
                                <td class="px-4 py-3">
                                    @if(in_array($a->status, ['pending','rejected']))
                                        <a href="{{ route('kontributor.acara.edit', $a->id) }}" class="text-xs text-blue-600 hover:underline">
                                            {{ $a->status === 'rejected' ? 'Edit & Kirim Ulang' : 'Edit' }}
                                        </a>
                                    @elseif($a->status === 'approved')
                                        <a href="{{ route('kontributor.acara.edit', $a->id) }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                                    @endif
                                    @if($a->status === 'rejected' && $a->rejection_reason)
                                        <p class="text-xs text-red-500 mt-1">{{ $a->rejection_reason }}</p>
                                    @endif
                                </td>
                            </tr>
                            @endforeach

                            @foreach($semua as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 text-gray-500">{{ $item['jenis'] }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $item['label'] }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ \Carbon\Carbon::parse($item['date'])->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    @include('pages.kontributor._status-badge', ['status' => $item['status']])
                                </td>
                                <td class="px-4 py-3 text-right text-gray-800 dark:text-gray-100">{{ $item['xp'] }}</td>
                                <td class="px-4 py-3">
                                    @if(in_array($item['status'], ['pending','rejected']))
                                        <a href="{{ $item['edit_route'] }}" class="text-xs text-blue-600 hover:underline">
                                            {{ $item['status'] === 'rejected' ? 'Edit & Kirim Ulang' : 'Edit' }}
                                        </a>
                                    @elseif($item['status'] === 'approved')
                                        <a href="{{ $item['edit_route'] }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                                    @endif
                                    @if($item['status'] === 'rejected' && $item['alasan'])
                                        <p class="text-xs text-red-500 mt-1">{{ $item['alasan'] }}</p>
                                    @endif
                                </td>
                            </tr>
                            @endforeach

                            @if($semua->isEmpty() && $acara->isEmpty())
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada kontribusi.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if($acara->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                        {{ $acara->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-dashboard-layout>
