<x-dashboard-layout>
    <div class="grow">
        <div class="p-6 space-y-6">
            <div class="sm:flex sm:justify-between sm:items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Acara Kontribusi</h2>
                <a href="{{ route('kontributor.saya') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 text-gray-800 dark:text-gray-300 mt-3 sm:mt-0">Kembali</a>
            </div>
            @if($acara->rejection_reason)
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg"><strong>Alasan penolakan:</strong> {{ $acara->rejection_reason }}</div>
            @endif
            @include('pages.kontributor.acara._form', ['action' => route('kontributor.acara.update', $acara->id), 'method' => 'PUT', 'acara' => $acara])
        </div>
    </div>
</x-dashboard-layout>
