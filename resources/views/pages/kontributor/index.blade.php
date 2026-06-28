<x-user-layout>
    @section('title', 'Program Kontributor Syaikhuna')
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Hero Section -->
        <div class="relative bg-emerald-900 overflow-hidden">
            <div class="absolute inset-0 opacity-30 bg-[url('https://www.transparenttextures.com/patterns/arabesque.png')]"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
                <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4 font-serif">
                    Program Kontributor Syaikhuna
                </h1>
                <p class="text-xl md:text-2xl text-emerald-100 max-w-3xl mx-auto font-light">
                    Ikut berperan dalam memperkaya data majelis, guru, jadwal, acara, dan amalan di Syaikhuna.
                Setiap kontribusi yang disetujui menghasilkan poin <em>Khidmah</em> dan gelar kehormatan.
                </p>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-20">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                    {{ session('error') }}
                    @if(session('missing_fields'))
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach(session('missing_fields') as $field)
                                <li>{{ $field }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('pengaturan-akun') }}" class="mt-2 inline-block text-sm underline text-green-700">Lengkapi profil sekarang →</a>
                    @endif
                </div>
            @endif
            @if(session('info'))
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg">{{ session('info') }}</div>
            @endif

            {{-- Manfaat & Badge --}}
            <div class="grid md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 text-center border border-gray-100 dark:border-gray-700">
                    <div class="text-3xl mb-2">🌿</div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-1">Jamaah Aktif</h3>
                    <p class="text-sm text-gray-500">0 – 100 XP</p>
                    <p class="text-xs text-gray-400 mt-2">Gelar awal setiap Kontributor</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 text-center border border-gray-100 dark:border-gray-700">
                    <div class="text-3xl mb-2">📚</div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-1">Penuntut Ilmu</h3>
                    <p class="text-sm text-gray-500">101 – 500 XP</p>
                    <p class="text-xs text-gray-400 mt-2">Kontributor yang tekun berbagi</p>
                </div>
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl shadow-sm p-6 text-center border border-emerald-200 dark:border-emerald-700">
                    <div class="text-3xl mb-2">⭐</div>
                    <h3 class="font-semibold text-emerald-800 dark:text-emerald-300 mb-1">Khadam Syaikhuna</h3>
                    <p class="text-sm text-emerald-600 dark:text-emerald-400">≥ {{ number_format($rewardSetting->min_xp) }} XP</p>
                    <p class="text-xs text-emerald-500 mt-2">Gelar tertinggi pengabdi komunitas</p>
                    @if($rewardSetting->is_active)
                        <span class="inline-flex items-center gap-1 mt-3 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            🎁 Reward Rp {{ number_format($rewardSetting->amount, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Reward Khadam Syaikhuna --}}
            @if($rewardSetting->is_active)
            <div class="bg-gradient-to-br from-emerald-600 to-emerald-800 rounded-2xl shadow-sm p-8 text-white text-center">
                <div class="text-4xl mb-3">🎁</div>
                <h2 class="text-2xl md:text-3xl font-bold font-serif mb-3">Reward Rp {{ number_format($rewardSetting->amount, 0, ',', '.') }} untuk Khadam Syaikhuna</h2>
                <p class="text-emerald-100 max-w-2xl mx-auto">
                    Sebagai bentuk apresiasi, setiap kontributor yang mencapai gelar <span class="font-semibold text-white">Khadam Syaikhuna</span>
                    (≥ {{ number_format($rewardSetting->min_xp) }} XP) berhak mengklaim reward uang tunai sebesar
                    <span class="font-semibold text-white">Rp {{ number_format($rewardSetting->amount, 0, ',', '.') }}</span>,
                    ditransfer ke akun e-wallet Anda.
                </p>
                <p class="text-xs text-emerald-200/90 mt-4 max-w-2xl mx-auto">
                    Reward diberikan <span class="font-semibold">sekali</span> per kontributor dan dapat diklaim melalui Dasbor Akun pada menu
                    <em>Dashboard Kontributor</em> setelah Anda mencapai gelar Khadam Syaikhuna.
                </p>
            </div>
            @endif

            {{-- XP per Kontribusi --}}
            @if($xpSettings->isNotEmpty())
            <div>
                <div class="text-center mb-8">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-gray-100 font-serif">Poin XP per Kontribusi</h2>
                    <p class="text-gray-500 dark:text-gray-400 mt-2 max-w-2xl mx-auto">
                        Setiap jenis data yang Anda tambahkan bernilai poin <em>Khidmah</em> (XP) yang berbeda. Berikut perolehannya:
                    </p>
                </div>

                @php
                    $xpIcons = [
                        'majelis' => '🕌',
                        'guru' => '🧑‍🏫',
                        'jadwal' => '🗓️',
                        'acara' => '📅',
                        'amalan' => '📿',
                        'catatan_pengajian' => '📝',
                    ];
                @endphp

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($xpSettings as $setting)
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-100 dark:border-gray-700 flex items-center gap-4">
                        <div class="text-3xl shrink-0">{{ $xpIcons[$setting->contribution_type] ?? '⭐' }}</div>
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $setting->label }}</p>
                            <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400">+{{ number_format($setting->points) }} XP</p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Catatan: XP didapat setelah disetujui admin --}}
                <div class="mt-6 flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z" />
                    </svg>
                    <p class="text-sm text-amber-800 dark:text-amber-300">
                        <span class="font-semibold">Catatan:</span> Poin XP hanya diberikan setelah kontribusi Anda
                        <span class="font-semibold">disetujui (diverifikasi) oleh admin</span>. Selama kontribusi masih menunggu moderasi,
                        XP belum ditambahkan. Jika kontribusi ditolak, XP tidak diberikan.
                    </p>
                </div>
            </div>
            @endif

            {{-- Tombol Daftar --}}
            <div class="text-center mb-14">
                @guest
                    <a href="{{ route('login') }}" class="btn bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-lg font-semibold">
                        Masuk untuk Mendaftar
                    </a>
                @endguest
                @auth
                    @if(Auth::user()->hasRole('Kontributor'))
                        <a href="{{ route('kontributor.saya') }}" class="btn bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-lg font-semibold">
                            Kelola Kontribusi Saya →
                        </a>
                    @else
                        <form action="{{ route('kontributor.daftar') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3 rounded-lg font-semibold">
                                Daftar Jadi Kontributor
                            </button>
                        </form>
                    @endif
                @endauth
            </div>

            {{-- Leaderboard --}}
            @if($leaderboard->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Top 10 Kontributor</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Gelar</th>
                                <th class="px-4 py-3 text-right">Total XP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($leaderboard as $i => $kontributor)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 font-medium text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium">
                                    @if(!empty($kontributor->username))
                                        <a href="{{ route('kontributor.profil', $kontributor->username) }}" class="text-gray-800 dark:text-gray-100 hover:text-emerald-600 dark:hover:text-emerald-400">
                                            {{ $kontributor->name }}
                                        </a>
                                    @else
                                        <span class="text-gray-800 dark:text-gray-100">{{ $kontributor->name }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                        {{ $kontributor->badge_title }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-100">
                                    {{ number_format($kontributor->total_khidmah_points) }} XP
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-user-layout>
