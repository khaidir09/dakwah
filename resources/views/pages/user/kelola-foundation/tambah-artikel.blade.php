<x-dashboard-layout>
    <div class="grow" x-data="{
        sections: {{ Js::from(old('sections', [])) }},
        citations: {{ Js::from(old('citations', [])) }},
        bibliography: {{ Js::from(old('bibliography', [])) }},
        addSection() {
            this.sections.push({ heading: '', content: '', order: this.sections.length + 1 });
        },
        removeSection(index) {
            this.sections.splice(index, 1);
        },
        addCitation() {
            this.citations.push({ type: 'QURAN', source_text_arabic: '', translation: '', reference: '' });
        },
        removeCitation(index) {
            this.citations.splice(index, 1);
        },
        addBibliography() {
            this.bibliography.push({ full_citation: '' });
        },
        removeBibliography(index) {
            this.bibliography.splice(index, 1);
        }
    }">
        <div class="p-6 space-y-6">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-2xl text-gray-800 dark:text-gray-100 font-bold">Tambah Artikel Ilmiah</h2>
                <a href="{{ route('kelola-artikel.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">Kembali</a>
            </div>

            <form action="{{ route('kelola-artikel.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-6 mb-6">

                    {{-- Foundation Selection --}}
                    <div>
                        <label class="block text-sm font-medium mb-2" for="foundation_id">Yayasan <span class="text-red-500">*</span></label>
                        @if($foundations->count() > 1)
                            <select id="foundation_id" class="form-select w-full @error('foundation_id') is-invalid @enderror" name="foundation_id" required>
                                <option value="">Pilih Yayasan</option>
                                @foreach($foundations as $foundation)
                                    <option value="{{ $foundation->id }}" {{ old('foundation_id') == $foundation->id ? 'selected' : '' }}>{{ $foundation->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="foundation_id" value="{{ $foundations->first()->id }}">
                            <input class="form-input w-full bg-gray-100 text-gray-500" type="text" value="{{ $foundations->first()->name }}" disabled>
                        @endif
                        @error('foundation_id')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="title">Judul <span class="text-red-500">*</span></label>
                        <input id="title" class="form-input w-full @error('title') is-invalid @enderror" type="text" name="title" value="{{ old('title') }}" required/>
                        @error('title')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="subtitle">Sub Judul</label>
                        <input id="subtitle" class="form-input w-full @error('subtitle') is-invalid @enderror" type="text" name="subtitle" value="{{ old('subtitle') }}"/>
                        @error('subtitle')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="author_name">Penulis <span class="text-red-500">*</span></label>
                        <input id="author_name" class="form-input w-full @error('author_name') is-invalid @enderror" type="text" name="author_name" value="{{ old('author_name', Auth::user()->name) }}" required/>
                        @error('author_name')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="category">Kategori <span class="text-red-500">*</span></label>
                        <input id="category" class="form-input w-full @error('category') is-invalid @enderror" type="text" name="category" value="{{ old('category') }}" placeholder="Contoh: Fiqih, Aqidah" required/>
                        @error('category')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2" for="cover_image">Gambar Sampul</label>
                        <input id="cover_image" class="form-input w-full @error('cover_image') is-invalid @enderror" type="file" name="cover_image" accept="image/*"/>
                        @error('cover_image')
                            <div class="text-xs mt-1 text-red-500">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- Sections --}}
                <div class="mb-6 border-t pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Bagian Artikel (Sections)</h3>
                        <button type="button" @click="addSection()" class="btn px-3 py-1 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded">+ Tambah Bagian</button>
                    </div>

                    <template x-for="(section, index) in sections" :key="index">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg mb-4 border border-gray-200 dark:border-gray-700 relative">
                            <button type="button" @click="removeSection(index)" class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>

                            <div class="grid gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Heading</label>
                                    <input type="text" x-model="section.heading" :name="'sections['+index+'][heading]'" class="form-input w-full" placeholder="Judul Bagian" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Konten</label>
                                    <textarea x-model="section.content" :name="'sections['+index+'][content]'" class="form-textarea w-full" rows="4" placeholder="Isi konten..." required></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Urutan</label>
                                    <input type="number" x-model="section.order" :name="'sections['+index+'][order]'" class="form-input w-24">
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="sections.length === 0" class="text-gray-500 text-sm italic text-center py-4">Belum ada bagian yang ditambahkan.</div>
                </div>

                {{-- Citations --}}
                <div class="mb-6 border-t pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Kutipan / Dalil (Citations)</h3>
                        <button type="button" @click="addCitation()" class="btn px-3 py-1 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded">+ Tambah Kutipan</button>
                    </div>

                    <template x-for="(citation, index) in citations" :key="index">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg mb-4 border border-gray-200 dark:border-gray-700 relative">
                            <button type="button" @click="removeCitation(index)" class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>

                            <div class="grid gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Tipe</label>
                                    <select x-model="citation.type" :name="'citations['+index+'][type]'" class="form-select w-full" required>
                                        <option value="QURAN">Al-Quran</option>
                                        <option value="HADITH">Hadits</option>
                                        <option value="KITAB">Kitab</option>
                                        <option value="SAINS">Sains / Umum</option>
                                    </select>
                                </div>
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Teks Asli (Arab/Latin)</label>
                                        <textarea x-model="citation.source_text_arabic" :name="'citations['+index+'][source_text_arabic]'" class="form-textarea w-full" rows="3"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Terjemahan</label>
                                        <textarea x-model="citation.translation" :name="'citations['+index+'][translation]'" class="form-textarea w-full" rows="3"></textarea>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Referensi (Surat/Ayat/Perawi/Halaman)</label>
                                    <input type="text" x-model="citation.reference" :name="'citations['+index+'][reference]'" class="form-input w-full" required>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="citations.length === 0" class="text-gray-500 text-sm italic text-center py-4">Belum ada kutipan yang ditambahkan.</div>
                </div>

                {{-- Bibliography --}}
                <div class="mb-6 border-t pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Daftar Pustaka (Bibliography)</h3>
                        <button type="button" @click="addBibliography()" class="btn px-3 py-1 text-sm bg-blue-600 text-white hover:bg-blue-700 rounded">+ Tambah Daftar Pustaka</button>
                    </div>

                    <template x-for="(bib, index) in bibliography" :key="index">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg mb-4 border border-gray-200 dark:border-gray-700 relative">
                            <button type="button" @click="removeBibliography(index)" class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>

                            <div>
                                <label class="block text-sm font-medium mb-1">Sitasi Lengkap</label>
                                <textarea x-model="bib.full_citation" :name="'bibliography['+index+'][full_citation]'" class="form-textarea w-full" rows="2" placeholder="Contoh: Penulis. (Tahun). Judul. Penerbit." required></textarea>
                            </div>
                        </div>
                    </template>
                    <div x-show="bibliography.length === 0" class="text-gray-500 text-sm italic text-center py-4">Belum ada daftar pustaka yang ditambahkan.</div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white ml-3">Simpan Artikel</button>
                </div>
            </form>
        </div>
    </div>
</x-dashboard-layout>
