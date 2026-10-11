<div class="space-y-3">
    @foreach($rows as $index => $row)
        <div wire:key="row-{{ $row['key'] }}" class="flex flex-col sm:flex-row gap-3 sm:items-start">
            <div class="flex-1">
                <livewire:partials.teacher-select
                    :key="'teacher-'.$row['key']"
                    :field-name="'teachers['.$row['key'].'][teacher_id]'"
                    :selected-id="$row['teacher_id']"
                    :include-wafat="true"
                    :only-public="true"
                />
            </div>
            <div class="sm:w-48">
                <select name="teachers[{{ $row['key'] }}][role]" wire:model="rows.{{ $index }}.role" class="form-select w-full">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if(count($rows) > 1)
                <button type="button" wire:click="removeRow({{ $index }})" class="text-red-500 hover:text-red-700 sm:mt-2 text-sm">
                    Hapus
                </button>
            @endif
        </div>
    @endforeach

    <button type="button" wire:click="addRow" class="text-sm font-medium text-violet-500 hover:text-violet-600">
        + Tambah Guru
    </button>
</div>
