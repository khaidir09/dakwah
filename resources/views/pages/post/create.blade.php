<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Tambah Tulisan</h1>
            </div>
             <div class="flex space-x-3">
                <a href="{{ route('posts.index') }}" class="btn bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-800 dark:text-gray-300">
                    Kembali
                </a>
            </div>
        </div>

        <div>
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white dark:bg-gray-800 p-6 shadow sm:rounded-tl-md sm:rounded-tr-md">
                    <div class="grid md:grid-cols-2 gap-6">
                        @if (session('status'))
                            <div class="px-4 py-2 rounded-lg text-sm bg-green-500 text-white relative" role="alert">
                                <span class="block sm:inline">{{ session('status') }}</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="title">Judul <span class="text-red-500">*</span></label>
                            <input id="title" class="form-input w-full" type="text" name="title" value="{{ old('title') }}" required />
                            @error('title') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                        </div>
                    

                    
                        <!-- Labels -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="labels">Label (Pisahkan dengan koma)</label>
                            <input id="labels" class="form-input w-full" type="text" name="labels" value="{{ old('labels') }}" placeholder="Contoh: Fiqih, Sejarah, Umum" />
                        </div>

                        <!-- Source -->
                        <div class="col-span-2" x-data="{ sources: {{ Js::from(old('source', [])) }}, errors: {{ Js::from($errors->messages()) }} }">
                            <label class="block text-sm font-medium mb-2">Sumber</label>
                            <template x-for="(source, index) in sources" :key="index">
                                <div class="flex gap-4 mb-2 items-start">
                                    <div class="flex-1">
                                        <input type="text" :name="`source[${index}][name]`" x-model="source.name" class="form-input w-full" placeholder="Nama Sumber (Contoh: Kitab Ihya Ulumuddin)">
                                        <div x-show="errors[`source.${index}.name`]" x-text="errors[`source.${index}.name`] ? errors[`source.${index}.name`][0] : ''" class="text-xs mt-1 text-red-500"></div>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" :name="`source[${index}][url]`" x-model="source.url" class="form-input w-full" placeholder="Link URL (Opsional)">
                                        <div x-show="errors[`source.${index}.url`]" x-text="errors[`source.${index}.url`] ? errors[`source.${index}.url`][0] : ''" class="text-xs mt-1 text-red-500"></div>
                                    </div>
                                    <button type="button" @click="sources.splice(index, 1)" class="text-red-500 hover:text-red-700 mt-2" x-show="sources.length > 0">
                                        <span class="sr-only">Hapus</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </template>
                            <button type="button" @click="sources.push({ name: '', url: '' })" class="text-sm text-blue-500 hover:text-blue-700 font-medium flex items-center mt-1">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Tambah Sumber
                            </button>
                        </div>
                        
                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="status">Status <span class="text-red-500">*</span></label>
                            <select id="status" class="form-select w-full" name="status" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            @error('status') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                        </div>

                        <!-- Cover Image -->
                        <div>
                            <label class="block text-sm font-medium mb-1" for="cover_image">Gambar Cover</label>
                            <input id="cover_image" class="form-input w-full" type="file" name="cover_image" accept="image/*" />
                            @error('cover_image') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                        </div>

                        <!-- Content -->
                        <div class="col-span-2">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="content">Isi Tulisan <span class="text-red-500">*</span></label>

                                <div class="w-full bg-neutral-secondary-medium border border-gray-200 dark:border-gray-700/60 rounded-md">
                                    <div class="p-2 border-b border-gray-200 dark:border-gray-700/60">
                                        <div class="flex flex-wrap items-center">
                                            <div class="flex items-center space-x-1 rtl:space-x-reverse flex-wrap">
                                                <button id="toggleBoldButton" data-tooltip-target="tooltip-bold" type="button" class="p-1.5 text-gray-800 dark:text-gray-300 rounded-sm cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5h4.5a3.5 3.5 0 1 1 0 7H8m0-7v7m0-7H6m2 7h6.5a3.5 3.5 0 1 1 0 7H8m0-7v7m0 0H6"/></svg>
                                                    <span class="sr-only">Bold</span>
                                                </button>
                                                <div id="tooltip-bold" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                    Toggle bold
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>

                                                <button id="toggleItalicButton" data-tooltip-target="tooltip-italic" type="button" class="p-1.5 text-gray-800 dark:text-gray-300 rounded-sm cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8.874 19 6.143-14M6 19h6.33m-.66-14H18"/></svg>
                                                    <span class="sr-only">Italic</span>
                                                </button>
                                                <div id="tooltip-italic" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                    Toggle italic
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>

                                                <button id="toggleUnderlineButton" data-tooltip-target="tooltip-underline" type="button" class="p-1.5 text-gray-800 dark:text-gray-300 rounded-sm cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M6 19h12M8 5v9a4 4 0 0 0 8 0V5M6 5h4m4 0h4"/></svg>
                                                    <span class="sr-only">Underline</span>
                                                </button>
                                                <div id="tooltip-underline" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                    Toggle underline
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>

                                                <button id="toggleStrikeButton" data-tooltip-target="tooltip-strike" type="button" class="p-1.5 text-gray-800 dark:text-gray-300 rounded-sm cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6.2V5h12v1.2M7 19h6m.2-14-1.677 6.523M9.6 19l1.029-4M5 5l6.523 6.523M19 19l-7.477-7.477"/></svg>
                                                    <span class="sr-only">Strike</span>
                                                </button>
                                                <div id="tooltip-strike" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                    Toggle strike
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>

                                                <div class="px-1">
                                                    <span class="block w-px h-4 bg-gray-300 dark:bg-gray-600"></span>
                                                </div>

                                                <button id="toggleListButton" type="button" data-tooltip-target="tooltip-list" class="p-1.5 text-gray-800 dark:text-gray-300 rounded-sm cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M9 8h10M9 12h10M9 16h10M4.99 8H5m-.02 4h.01m0 4H5"/></svg>
                                                    <span class="sr-only">Toggle list</span>
                                                </button>
                                                <div id="tooltip-list" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                    Toggle list
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>

                                                <button id="toggleOrderedListButton" type="button" data-tooltip-target="tooltip-ordered-list" class="p-1.5 text-gray-800 dark:text-gray-300 rounded-sm cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6h8m-8 6h8m-8 6h8M4 16a2 2 0 1 1 3.321 1.5L4 20h5M4 5l2-1v6m-2 0h4"/></svg>
                                                    <span class="sr-only">Toggle ordered list</span>
                                                </button>
                                                <div id="tooltip-ordered-list" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                    Toggle ordered list
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>

                                                <button id="toggleBlockquoteButton" type="button" data-tooltip-target="tooltip-blockquote-list" class="p-1.5 text-gray-800 dark:text-gray-300 rounded-sm cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V8a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1Zm0 0v2a4 4 0 0 1-4 4H5m14-6V8a1 1 0 0 0-1-1h-3a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1Zm0 0v2a4 4 0 0 1-4 4h-1"/></svg>
                                                    <span class="sr-only">Toggle blockquote</span>
                                                </button>
                                                <div id="tooltip-blockquote-list" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                    Toggle blockquote
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>

                                                <div class="px-1">
                                                    <span class="block w-px h-4 bg-gray-300 dark:bg-gray-600"></span>
                                                </div>

                                                <button id="toggleLeftAlignButton" type="button" data-tooltip-target="tooltip-left-align" class="p-1.5 text-gray-800 dark:text-gray-300 rounded-sm cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h8m-8 4h12M6 14h8m-8 4h12"/></svg>
                                                    <span class="sr-only">Align left</span>
                                                </button>
                                                <div id="tooltip-left-align" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                    Align left
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>

                                                <button id="toggleCenterAlignButton" type="button" data-tooltip-target="tooltip-center-align" class="p-1.5 text-gray-800 dark:text-gray-300 rounded-sm cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h8M6 10h12M8 14h8M6 18h12"/></svg>
                                                    <span class="sr-only">Align center</span>
                                                </button>
                                                <div id="tooltip-center-align" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                    Align center
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>

                                                <button id="toggleRightAlignButton" type="button" data-tooltip-target="tooltip-right-align" class="p-1.5 text-gray-800 dark:text-gray-300 rounded-sm cursor-pointer hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 6h-8m8 4H6m12 4h-8m8 4H6"/></svg>
                                                    <span class="sr-only">Align right</span>
                                                </button>
                                                <div id="tooltip-right-align" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                    Align right
                                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-4 py-2 bg-white dark:bg-gray-800 rounded-b-md">
                                        <div id="wysiwyg-example" class="block w-full px-0 text-sm text-gray-800 dark:text-gray-300 bg-white dark:bg-gray-800 border-0 focus:ring-0 min-h-[200px]"></div>
                                    </div>
                                </div>
                                <textarea id="content" class="hidden form-textarea w-full" name="content" rows="10">{{ old('content') }}</textarea>
                                @error('content') <div class="text-xs mt-1 text-red-500">{{ $message }}</div> @enderror
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex items-center justify-end px-4 py-3 bg-gray-50 dark:bg-gray-800 text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                    <x-button>
                        Simpan
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script type="module">
        import { Editor } from '@tiptap/core';
        import StarterKit from '@tiptap/starter-kit';
        import Underline from '@tiptap/extension-underline';
        import TextAlign from '@tiptap/extension-text-align';

        window.addEventListener('load', function() {
            if (document.getElementById("wysiwyg-example")) {
                const editor = new Editor({
                    element: document.querySelector('#wysiwyg-example'),
                    extensions: [
                        StarterKit,
                        Underline,
                        TextAlign.configure({
                            types: ['heading', 'paragraph'],
                        }),
                    ],
                    content: document.getElementById('content').value,
                    editorProps: {
                        attributes: {
                            class: 'format lg:format-lg dark:format-invert focus:outline-none format-blue max-w-none prose dark:prose-invert min-h-[200px]',
                        },
                    },
                    onUpdate: ({ editor }) => {
                        document.getElementById('content').value = editor.getHTML();
                    }
                });

                // set up custom event listeners for the buttons
                document.getElementById('toggleBoldButton').addEventListener('click', () => editor.chain().focus().toggleBold().run());
                document.getElementById('toggleItalicButton').addEventListener('click', () => editor.chain().focus().toggleItalic().run());
                document.getElementById('toggleUnderlineButton').addEventListener('click', () => editor.chain().focus().toggleUnderline().run());
                document.getElementById('toggleStrikeButton').addEventListener('click', () => editor.chain().focus().toggleStrike().run());

                document.getElementById('toggleListButton').addEventListener('click', () => editor.chain().focus().toggleBulletList().run());
                document.getElementById('toggleOrderedListButton').addEventListener('click', () => editor.chain().focus().toggleOrderedList().run());
                document.getElementById('toggleBlockquoteButton').addEventListener('click', () => editor.chain().focus().toggleBlockquote().run());

                document.getElementById('toggleLeftAlignButton').addEventListener('click', () => editor.chain().focus().setTextAlign('left').run());
                document.getElementById('toggleCenterAlignButton').addEventListener('click', () => editor.chain().focus().setTextAlign('center').run());
                document.getElementById('toggleRightAlignButton').addEventListener('click', () => editor.chain().focus().setTextAlign('right').run());
            }
        });
    </script>
    @endpush
</x-app-layout>
