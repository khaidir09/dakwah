<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
        <div class="grid md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-medium mb-2" for="nama_majelis">Nama Majelis <span class="text-red-500">*</span></label>
                <input id="nama_majelis" class="form-input w-full" type="text" name="nama_majelis" value="{{ old('nama_majelis', $majelis?->nama_majelis) }}" required/>
                @error('nama_majelis')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2" for="tipe">Tipe Majelis</label>
                <select id="tipe" class="form-select w-full" name="tipe">
                    <option value="">Pilih Tipe</option>
                    @foreach(['Majelis','Mesjid','Langgar','Musholla'] as $tipe)
                        <option value="{{ $tipe }}" {{ old('tipe', $majelis?->tipe) == $tipe ? 'selected' : '' }}>{{ $tipe }}</option>
                    @endforeach
                </select>
            </div>

            <div x-data="{ manual: {{ old('custom_leader_name', $majelis?->custom_leader_name) ? 'true' : 'false' }} }">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-medium">
                        <span x-text="manual ? 'Nama Pimpinan (Manual)' : 'Nama Guru'"></span>
                        <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center">
                        <input type="checkbox" id="manual_toggle" x-model="manual" class="form-checkbox w-4 h-4">
                        <label for="manual_toggle" class="ml-2 text-sm">Input Manual?</label>
                    </div>
                </div>
                <div x-show="!manual">
                    <select class="form-select w-full" name="teacher_id" :disabled="manual">
                        <option value="">Pilih Guru</option>
                        @foreach($teachers as $t)
                            <option value="{{ $t->id }}" {{ old('teacher_id', $majelis?->teacher_id) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="manual">
                    <input class="form-input w-full" type="text" name="custom_leader_name" value="{{ old('custom_leader_name', $majelis?->custom_leader_name) }}" :disabled="!manual" placeholder="Nama pimpinan..." />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2" for="alamat">Alamat <span class="text-red-500">*</span></label>
                <input id="alamat" class="form-input w-full" type="text" name="alamat" value="{{ old('alamat', $majelis?->alamat) }}" required/>
                @error('alamat')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2" for="maps">Link Maps</label>
                <input id="maps" class="form-input w-full" type="text" name="maps" value="{{ old('maps', $majelis?->maps) }}" placeholder="https://maps.google.com/..."/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2" for="gambar">Gambar Majelis</label>
                @if($majelis?->gambar)
                    <img src="{{ $majelis->gambar_thumb_url }}" class="w-24 h-24 object-cover rounded mb-2">
                @endif
                <input id="gambar" class="form-input w-full" type="file" name="gambar" accept="image/*"/>
                @error('gambar')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium mb-2" for="deskripsi">Deskripsi <span class="text-red-500">*</span></label>
            <textarea class="form-textarea w-full" name="deskripsi" id="deskripsi" rows="6" required>{{ old('deskripsi', $majelis?->deskripsi) }}</textarea>
            @error('deskripsi')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
        </div>

        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Domisili</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Provinsi</label>
                    <select id="province" class="form-select w-full" name="province">
                        <option value="">Pilih Provinsi</option>
                        @foreach($provinces as $code => $name)
                            <option value="{{ $code }}" {{ old('province', $majelis?->province_code) == $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Kota/Kabupaten</label>
                    <select id="city" class="form-select w-full" name="city">
                        <option value="{{ old('city', $majelis?->city_code) }}">Pilih Kota</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Kecamatan</label>
                    <select id="district" class="form-select w-full" name="district">
                        <option value="{{ old('district', $majelis?->district_code) }}">Pilih Kecamatan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Desa/Kelurahan</label>
                    <select id="village" class="form-select w-full" name="village">
                        <option value="{{ old('village', $majelis?->village_code) }}">Pilih Desa</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 sm:rounded-bl-md sm:rounded-br-md shadow">
        <x-button>Kirim Kontribusi</x-button>
    </div>
</form>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(function() {
    $('#province').on('change', function() {
        var code = $(this).val();
        $('#city').html('<option value="">Pilih Kota</option>');
        $('#district').html('<option value="">Pilih Kecamatan</option>');
        $('#village').html('<option value="">Pilih Desa</option>');
        if (code) $.getJSON('/get-cities/' + code, function(d) {
            $.each(d, function(c, n) { $('#city').append('<option value="'+c+'">'+n+'</option>'); });
        });
    });
    $('#city').on('change', function() {
        var code = $(this).val();
        $('#district').html('<option value="">Pilih Kecamatan</option>');
        $('#village').html('<option value="">Pilih Desa</option>');
        if (code) $.getJSON('/get-districts/' + code, function(d) {
            $.each(d, function(c, n) { $('#district').append('<option value="'+c+'">'+n+'</option>'); });
        });
    });
    $('#district').on('change', function() {
        var code = $(this).val();
        $('#village').html('<option value="">Pilih Desa</option>');
        if (code) $.getJSON('/get-villages/' + code, function(d) {
            $.each(d, function(c, n) { $('#village').append('<option value="'+c+'">'+n+'</option>'); });
        });
    });
});
</script>
@endpush
