<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
        <div class="grid md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-medium mb-2">Nama Guru <span class="text-red-500">*</span></label>
                <input class="form-input w-full" type="text" name="name" value="{{ old('name', $guru?->name) }}" required/>
                @error('name')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Tahun Lahir</label>
                <input class="form-input w-full" type="number" name="tahun_lahir" value="{{ old('tahun_lahir', $guru?->tahun_lahir) }}" placeholder="1900"/>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Foto</label>
                @if($guru?->foto)
                    <img src="{{ asset('storage/' . $guru->foto) }}" class="w-20 h-20 object-cover rounded mb-2">
                @endif
                <input class="form-input w-full" type="file" name="foto" accept="image/*"/>
                @error('foto')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Link Maps</label>
                <input class="form-input w-full" type="text" name="maps" value="{{ old('maps', $guru?->maps) }}" placeholder="https://maps.google.com/..."/>
            </div>

        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium mb-2">Biografi <span class="text-red-500">*</span></label>
            <textarea class="form-textarea w-full" name="biografi" rows="8" required>{{ old('biografi', $guru?->biografi) }}</textarea>
            @error('biografi')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
        </div>

        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Domisili</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Provinsi</label>
                    <select id="province" class="form-select w-full" name="province">
                        <option value="">Pilih Provinsi</option>
                        @foreach($provinces as $code => $name)
                            <option value="{{ $code }}" {{ old('province', $guru?->province_code) == $code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Kota/Kabupaten</label>
                    <select id="city" class="form-select w-full" name="city">
                        <option value="{{ old('city', $guru?->city_code) }}">Pilih Kota</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Kecamatan</label>
                    <select id="district" class="form-select w-full" name="district">
                        <option value="{{ old('district', $guru?->district_code) }}">Pilih Kecamatan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Desa/Kelurahan</label>
                    <select id="village" class="form-select w-full" name="village">
                        <option value="{{ old('village', $guru?->village_code) }}">Pilih Desa</option>
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
        if (code) $.getJSON('/get-cities/'+code, function(d) { $.each(d, function(c,n){ $('#city').append('<option value="'+c+'">'+n+'</option>'); }); });
    });
    $('#city').on('change', function() {
        var code = $(this).val();
        $('#district').html('<option value="">Pilih Kecamatan</option>');
        $('#village').html('<option value="">Pilih Desa</option>');
        if (code) $.getJSON('/get-districts/'+code, function(d) { $.each(d, function(c,n){ $('#district').append('<option value="'+c+'">'+n+'</option>'); }); });
    });
    $('#district').on('change', function() {
        var code = $(this).val();
        $('#village').html('<option value="">Pilih Desa</option>');
        if (code) $.getJSON('/get-villages/'+code, function(d) { $.each(d, function(c,n){ $('#village').append('<option value="'+c+'">'+n+'</option>'); }); });
    });
});
</script>
@endpush
