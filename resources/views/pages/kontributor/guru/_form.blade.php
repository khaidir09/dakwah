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
                <label class="block text-sm font-medium mb-2">Foto Guru</label>
                @if($guru?->foto)
                    <img src="{{ asset('storage/' . $guru->foto) }}" class="w-20 h-20 object-cover rounded mb-2">
                @endif
                <input class="form-input w-full" type="file" name="foto" accept="image/*"/>
                @error('foto')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium mb-2">Biografi <span class="text-red-500">*</span></label>
            <x-wysiwyg-editor name="biografi" :value="old('biografi', $guru?->biografi)" />
            @error('biografi')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
        </div>

        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Domisili Guru</h3>
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

        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Wafat</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Isi hanya jika guru sudah wafat. <strong>Tahun hijriah wajib diisi</strong> agar tulisan masuk ke daftar Manaqib.
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Tanggal Wafat (Masehi)</label>
                    <input class="form-input w-full" type="date" name="wafat_masehi" value="{{ old('wafat_masehi', $guru?->wafat_masehi) }}"/>
                    @error('wafat_masehi')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Tanggal Hijriah</label>
                    <input class="form-input w-full" type="number" min="1" max="30" name="wafat_hijriah_day" value="{{ old('wafat_hijriah_day', $guru?->wafat_hijriah_day) }}" placeholder="1-30"/>
                    @error('wafat_hijriah_day')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Bulan Hijriah</label>
                    <select class="form-select w-full" name="wafat_hijriah_month">
                        <option value="">Pilih Bulan</option>
                        @foreach(['Muharram', 'Safar', 'Rabiul Awal', 'Rabiul Akhir', 'Jumadil Awal', 'Jumadil Akhir', 'Rajab', "Sya'ban", 'Ramadhan', 'Syawal', 'Dzulqaidah', 'Dzulhijjah'] as $i => $namaBulan)
                            <option value="{{ $i + 1 }}" {{ (int) old('wafat_hijriah_month', $guru?->wafat_hijriah_month) === $i + 1 ? 'selected' : '' }}>{{ $namaBulan }}</option>
                        @endforeach
                    </select>
                    @error('wafat_hijriah_month')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Tahun Hijriah</label>
                    <input class="form-input w-full" type="number" name="wafat_hijriah_year" value="{{ old('wafat_hijriah_year', $guru?->wafat_hijriah_year) }}" placeholder="1445"/>
                    @error('wafat_hijriah_year')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Foto Bersama Guru (Opsional)</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Foto pertemuan Anda dengan guru. Ditampilkan di halaman manaqib sebagai penguat kredibilitas tulisan Anda.
                Maksimal 8 MB (JPG, PNG, atau WebP).
            </p>

            @if($guru?->foto_bersama)
                <div class="mb-4">
                    <img src="{{ Storage::url($guru->foto_bersama) }}" alt="Foto bersama guru" class="max-w-xs h-auto rounded-lg border border-gray-200 dark:border-gray-700/60">
                    <label class="flex items-center mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <input type="checkbox" class="form-checkbox" name="hapus_foto_bersama" value="1">
                        <span class="ml-2">Hapus foto bersama ini</span>
                    </label>
                </div>
            @endif

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-2">{{ $guru?->foto_bersama ? 'Ganti Foto' : 'Unggah Foto' }}</label>
                    <input class="form-input w-full" type="file" name="foto_bersama" accept="image/jpeg,image/png,image/webp"/>
                    @error('foto_bersama')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Keterangan Foto</label>
                    <input class="form-input w-full" type="text" name="foto_bersama_caption" value="{{ old('foto_bersama_caption', $guru?->foto_bersama_caption) }}" placeholder="Bersama beliau di Sekumpul, 2004" maxlength="255"/>
                    @error('foto_bersama_caption')<div class="text-xs mt-1 text-red-500">{{ $message }}</div>@enderror
                </div>
            </div>

            @if($guru?->contribution_status === 'approved')
                <p class="text-xs text-amber-600 dark:text-amber-500 mt-3">
                    Mengganti foto atau keterangannya akan mengembalikan tulisan ini ke antrean moderasi, dan sementara tidak tampil di publik.
                </p>
            @endif
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
