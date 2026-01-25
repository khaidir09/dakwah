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
                    @if($teacher->foto)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($teacher->foto) }}" alt="{{ $teacher->name }}" class="h-10 w-10 rounded-full object-cover shrink-0">
                    @else
                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $teacher->name }}</p>
                        @if($teacher->village_code)
                            <p class="text-xs text-gray-500">{{ $teacher->village->name }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @elseif(strlen($search) >= 2 && empty($selectedTeacherId) && count($teachers) === 0)
        <div class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg p-3">
            <p class="text-sm text-gray-500">Guru tidak ditemukan.</p>
        </div>
    @endif
</div>
