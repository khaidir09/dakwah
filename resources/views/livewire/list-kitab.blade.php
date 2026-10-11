<div>
    <!-- Filters -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input wire:model.live.debounce.300ms="search" type="text" class="form-input w-full bg-white dark:bg-gray-800" placeholder="Cari judul atau nama ulama...">
        <select wire:model.live="specialization" class="form-select w-full bg-white dark:bg-gray-800">
            <option value="">Semua Bidang Ilmu</option>
            @foreach ($specializations as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
        <select wire:model.live="language" class="form-select w-full bg-white dark:bg-gray-800">
            <option value="">Semua Bahasa</option>
            @foreach ($languages as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="teacher_status" class="form-select w-full bg-white dark:bg-gray-800">
            <option value="">Semua Ulama</option>
            <option value="wafat">Ulama yang telah wafat</option>
            <option value="hidup">Ulama yang masih hidup</option>
        </select>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse ($books as $book)
            <div wire:key="kitab-{{ $book->id }}">
                <x-kitab.card :book="$book" />
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-2">Kitab tidak ditemukan</h3>
                <p class="text-gray-500 dark:text-gray-400">Belum ada kitab yang ditambahkan atau tidak cocok dengan pencarian.</p>
            </div>
        @endforelse
    </div>

    <p class="text-xs text-gray-400 dark:text-gray-500 mt-4">Status wafat berdasarkan data wafat yang tercatat di profil guru.</p>

    <div class="mt-8">
        {{ $books->links() }}
    </div>
</div>
