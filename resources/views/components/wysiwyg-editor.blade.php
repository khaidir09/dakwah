@props(['name', 'value' => '', 'id' => null])

@php
    $editorId = $id ?? $name;

    $tombol = [
        ['cmd' => 'bold', 'label' => 'B', 'title' => 'Tebal', 'class' => 'font-bold'],
        ['cmd' => 'italic', 'label' => 'I', 'title' => 'Miring', 'class' => 'italic'],
        ['cmd' => 'underline', 'label' => 'U', 'title' => 'Garis bawah', 'class' => 'underline'],
        ['cmd' => 'link', 'label' => 'Tautan', 'title' => 'Sisipkan tautan', 'class' => ''],
        ['cmd' => 'unlink', 'label' => 'Hapus tautan', 'title' => 'Hapus tautan', 'class' => ''],
        ['cmd' => 'bulletList', 'label' => '• Daftar', 'title' => 'Daftar berpoin', 'class' => ''],
        ['cmd' => 'orderedList', 'label' => '1. Daftar', 'title' => 'Daftar bernomor', 'class' => ''],
        ['cmd' => 'alignLeft', 'label' => 'Kiri', 'title' => 'Rata kiri', 'class' => ''],
        ['cmd' => 'alignCenter', 'label' => 'Tengah', 'title' => 'Rata tengah', 'class' => ''],
        ['cmd' => 'alignRight', 'label' => 'Kanan', 'title' => 'Rata kanan', 'class' => ''],
    ];
@endphp

<div id="{{ $editorId }}-shell" class="border border-gray-200 dark:border-gray-700/60 rounded-lg bg-white dark:bg-gray-800 overflow-hidden">
    <div id="{{ $editorId }}-toolbar" class="flex flex-wrap items-center gap-1 px-2 py-2 border-b border-gray-200 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-900/20">
        @foreach($tombol as $t)
            <button type="button" data-cmd="{{ $t['cmd'] }}" title="{{ $t['title'] }}"
                class="px-2 py-1 text-xs rounded text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 {{ $t['class'] }}">
                {{ $t['label'] }}
            </button>
        @endforeach
    </div>

    <div class="px-4 py-3">
        <div id="{{ $editorId }}-editor"></div>
    </div>
</div>

<textarea id="{{ $editorId }}" name="{{ $name }}" rows="10" class="hidden form-textarea w-full mt-2">{{ $value }}</textarea>

@push('scripts')
<script type="module">
    import { Editor } from 'https://esm.sh/@tiptap/core@2.6.6';
    import StarterKit from 'https://esm.sh/@tiptap/starter-kit@2.6.6';
    import Underline from 'https://esm.sh/@tiptap/extension-underline@2.6.6';
    import Link from 'https://esm.sh/@tiptap/extension-link@2.6.6';
    import TextAlign from 'https://esm.sh/@tiptap/extension-text-align@2.6.6';

    const editorId = @json($editorId);
    const textarea = document.getElementById(editorId);
    const holder = document.getElementById(editorId + '-editor');
    const toolbar = document.getElementById(editorId + '-toolbar');

    if (textarea && holder) {
        // Toolbar sengaja dibatasi pada format yang lolos HTML.Allowed di config/purifier.php.
        // Heading, blockquote, hr, highlight, code, gambar, dan iframe akan dibuang clean()
        // saat dirender, jadi tombolnya tidak disediakan.
        const editor = new Editor({
            element: holder,
            extensions: [
                StarterKit.configure({
                    heading: false,
                    blockquote: false,
                    horizontalRule: false,
                    code: false,
                    codeBlock: false,
                    strike: false,
                }),
                Underline,
                Link.configure({ openOnClick: false }),
                TextAlign.configure({ types: ['paragraph'] }),
            ],
            content: textarea.value,
            editorProps: {
                attributes: {
                    class: 'format lg:format-lg dark:format-invert focus:outline-none format-blue max-w-none prose dark:prose-invert min-h-[200px]',
                },
            },
            onUpdate: ({ editor }) => {
                textarea.value = editor.getHTML();
            },
        });

        const perintah = {
            bold: () => editor.chain().focus().toggleBold().run(),
            italic: () => editor.chain().focus().toggleItalic().run(),
            underline: () => editor.chain().focus().toggleUnderline().run(),
            unlink: () => editor.chain().focus().unsetLink().run(),
            bulletList: () => editor.chain().focus().toggleBulletList().run(),
            orderedList: () => editor.chain().focus().toggleOrderedList().run(),
            alignLeft: () => editor.chain().focus().setTextAlign('left').run(),
            alignCenter: () => editor.chain().focus().setTextAlign('center').run(),
            alignRight: () => editor.chain().focus().setTextAlign('right').run(),
            link: () => {
                const url = window.prompt('Alamat tautan:', 'https://');
                if (url) {
                    editor.chain().focus().toggleLink({ href: url }).run();
                }
            },
        };

        toolbar?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-cmd]');
            if (button && perintah[button.dataset.cmd]) {
                perintah[button.dataset.cmd]();
            }
        });

        window.wysiwygSiap = window.wysiwygSiap || {};
        window.wysiwygSiap[editorId] = true;
    }
</script>

<script>
    // Editor dimuat dari CDN pihak ketiga. Kalau gagal, textarea yang tersembunyi membuat
    // kontributor tidak bisa mengetik apa pun — jadi kembalikan ke textarea biasa.
    (function () {
        const editorId = @json($editorId);

        setTimeout(function () {
            if (window.wysiwygSiap && window.wysiwygSiap[editorId]) {
                return;
            }

            const textarea = document.getElementById(editorId);
            const shell = document.getElementById(editorId + '-shell');

            textarea?.classList.remove('hidden');
            shell?.classList.add('hidden');
        }, 5000);
    })();
</script>
@endpush
