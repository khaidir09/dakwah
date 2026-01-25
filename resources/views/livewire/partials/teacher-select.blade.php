<div class="relative">
    <input
        wire:model.live="search"
        type="text"
        class="form-input w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm"
        placeholder="Ketik nama guru..."
        autocomplete="off"
    >
    <input
        type="hidden"
        name="{{ $fieldName }}"
        value="{{ $selectedTeacherId }}"
    >

    @if(strlen($search) >= 2 && empty($selectedTeacherId) && count($teachers) > 0)
        <div class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto">
            @foreach($teachers as $teacher)
                <div
                    wire:click="selectTeacher({{ $teacher->id }}, '{{ addslashes($teacher->name) }}')"
                    class="p-3 cursor-pointer hover:bg-emerald-50 border-b border-gray-100 last:border-0"
                >
                    <p class="text-sm font-medium text-gray-900">{{ $teacher->name }}</p>
                    @if($teacher->domisili)
                         <p class="text-xs text-gray-500">{{ $teacher->domisili }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @elseif(strlen($search) >= 2 && empty($selectedTeacherId) && count($teachers) === 0)
        <div class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg p-3">
            <p class="text-sm text-gray-500">Guru tidak ditemukan.</p>
        </div>
    @endif
</div>
