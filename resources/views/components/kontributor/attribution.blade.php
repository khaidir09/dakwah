@props(['user' => null, 'compact' => false])

@php
    $hasProfile = $user && !empty($user->username);
@endphp

@if($compact)
    {{-- Versi ringkas untuk kartu (mis. daftar amalan) --}}
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400']) }}>
        @if($user)
            <img class="w-6 h-6 rounded-full object-cover shrink-0" src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}">
            <span>
                Kontributor:
                @if($hasProfile)
                    <a href="{{ route('kontributor.profil', $user->username) }}" class="font-medium text-gray-700 dark:text-gray-300 hover:text-emerald-600 dark:hover:text-emerald-400">{{ $user->name }}</a>
                @else
                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ $user->name }}</span>
                @endif
            </span>
        @else
            <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center shrink-0 text-white">
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 16 16"><path d="M8 0 1 3v5c0 3.6 2.9 7 7 8 4.1-1 7-4.4 7-8V3L8 0Zm3.2 6.2L7.5 9.9 4.8 7.2l1-1L7.5 7.9l2.7-2.7 1 1Z"/></svg>
            </div>
            <span>Kontributor: <span class="font-medium text-gray-700 dark:text-gray-300">Admin Syaikhuna</span></span>
        @endif
    </div>
@else
    {{-- Versi penuh untuk halaman detail --}}
    <div {{ $attributes->merge(['class' => 'flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-900/40 px-4 py-3']) }}>
        @if($user)
            <img class="w-10 h-10 rounded-full object-cover shrink-0" src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}">
            <div class="min-w-0">
                <div class="text-xs text-gray-500 dark:text-gray-400">Dikontribusikan oleh</div>
                @if($hasProfile)
                    <a href="{{ route('kontributor.profil', $user->username) }}" class="text-sm font-semibold text-gray-800 dark:text-gray-100 hover:text-emerald-600 dark:hover:text-emerald-400">{{ $user->name }}</a>
                @else
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $user->name }}</span>
                @endif
                @if(!empty($user->badge_title))
                    <div>
                        <span class="inline-flex items-center mt-0.5 px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">{{ $user->badge_title }}</span>
                    </div>
                @endif
            </div>
        @else
            <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center shrink-0 text-white">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 16 16"><path d="M8 0 1 3v5c0 3.6 2.9 7 7 8 4.1-1 7-4.4 7-8V3L8 0Zm3.2 6.2L7.5 9.9 4.8 7.2l1-1L7.5 7.9l2.7-2.7 1 1Z"/></svg>
            </div>
            <div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Dikontribusikan oleh</div>
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Admin Syaikhuna</span>
            </div>
        @endif
    </div>
@endif
