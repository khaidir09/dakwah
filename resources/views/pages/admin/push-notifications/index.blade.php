<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Kirim Notifikasi</h1>
            <p class="text-sm text-gray-500 mt-1">Kirim push notification OneSignal ke semua pelanggan atau ke pengguna tertentu. Notifikasi yang sudah terkirim tidak dapat ditarik kembali.</p>
        </div>

        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('message') }}</div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
        @endif

        <form action="{{ route('admin.push-notifications.send') }}" method="POST" class="max-w-xl mb-10"
              x-data="{ target: @js(old('target', 'all')) }"
              @submit="if (target === 'all' && !confirm('Kirim notifikasi ini ke SEMUA pelanggan?')) $event.preventDefault()">
            @csrf

            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <div class="p-6 space-y-6">

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-800 dark:text-gray-100 mb-1">Judul</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" maxlength="100"
                               class="form-input w-full @error('title') border-red-500 @enderror" required />
                        @error('title')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-800 dark:text-gray-100 mb-1">Pesan</label>
                        <textarea id="message" name="message" rows="3" maxlength="300"
                                  class="form-textarea w-full @error('message') border-red-500 @enderror" required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-800 dark:text-gray-100 mb-1">Tautan (opsional)</label>
                        <input type="url" id="url" name="url" value="{{ old('url') }}" placeholder="{{ url('/') }}"
                               class="form-input w-full @error('url') border-red-500 @enderror" />
                        <p class="text-xs text-gray-400 mt-1">Halaman yang dibuka saat notifikasi diketuk.</p>
                        @error('url')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-800 dark:text-gray-100 mb-2">Penerima</span>
                        <div class="flex flex-wrap gap-6">
                            <label class="flex items-center">
                                <input type="radio" name="target" value="all" class="form-radio" x-model="target" />
                                <span class="text-sm ml-2">Semua pelanggan</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="target" value="users" class="form-radio" x-model="target" />
                                <span class="text-sm ml-2">Pengguna tertentu</span>
                            </label>
                        </div>
                        @error('target')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div x-show="target === 'users'" x-cloak>
                        <livewire:forms.user-select :initial-users="$oldUsers" />
                        <p class="text-xs text-gray-400 mt-1">Hanya pengguna yang pernah login dan mengizinkan notifikasi di perangkatnya yang akan menerima.</p>
                        @error('user_ids')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                        @error('user_ids.*')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="btn bg-primary-500 hover:bg-primary-600 text-white">
                        Kirim Notifikasi
                    </button>
                </div>
            </div>
        </form>

        <h2 class="text-lg text-gray-800 dark:text-gray-100 font-semibold mb-4">Riwayat Pengiriman</h2>

        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-4 py-3">Notifikasi</th>
                            <th class="px-4 py-3">Penerima</th>
                            <th class="px-4 py-3">Pengirim</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($logs as $log)
                        <tr class="align-top">
                            <td class="px-4 py-4 max-w-md">
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $log->title }}</div>
                                <div class="text-gray-600 dark:text-gray-300">{{ $log->message }}</div>
                                @if($log->url)
                                    <a href="{{ $log->url }}" target="_blank" rel="noopener" class="text-xs text-violet-500 break-all">{{ $log->url }}</a>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">
                                {{ $log->target === \App\Models\PushNotificationLog::TARGET_ALL ? 'Semua pelanggan' : count($log->user_ids ?? []).' pengguna' }}
                                @if($log->recipients !== null)
                                    <p class="text-xs text-gray-500 mt-1">{{ $log->recipients }} perangkat</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300">{{ $log->sender?->name ?? '—' }}</td>
                            <td class="px-4 py-4 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-4">
                                @if($log->status === \App\Models\PushNotificationLog::STATUS_SENT)
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">Terkirim</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Gagal</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400">Belum ada notifikasi yang dikirim.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
                <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
