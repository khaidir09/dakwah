@php
    $config = match($status) {
        'approved' => ['bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400', 'Disetujui'],
        'rejected' => ['bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400', 'Ditolak'],
        'pending'  => ['bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400', 'Menunggu'],
        default    => ['bg-gray-100 text-gray-600', 'Menunggu'],
    };
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $config[0] }}">
    {{ $config[1] }}
</span>
