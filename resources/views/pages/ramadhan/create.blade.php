<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl font-bold text-gray-800">Tambah Jadwal Ramadhan</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('ramadhan-schedules.index') }}" class="btn bg-white border-gray-200 hover:border-gray-300 text-gray-600">
                    <svg class="w-4 h-4 fill-current text-gray-500 shrink-0 mr-2" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" transform="rotate(45 8 8)" />
                    </svg>
                    <span>Batal</span>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-sm shadow-sm">
            <livewire:ramadhan.schedule-form />
        </div>
    </div>
</x-app-layout>
