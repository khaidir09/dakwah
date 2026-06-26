<x-dashboard-layout>
    <div class="grow">
        <div class="p-6 space-y-6">
            <div class="sm:flex sm:justify-between sm:items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Tambah Majelis (Kontribusi)</h2>
                <a href="{{ route('kontributor.saya') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 text-gray-800 dark:text-gray-300 mt-3 sm:mt-0">
                    Kembali
                </a>
            </div>

            @include('pages.kontributor.majelis._form', [
                'action' => route('kontributor.majelis.store'),
                'method' => 'POST',
                'majelis' => null,
            ])
        </div>
    </div>
</x-dashboard-layout>
