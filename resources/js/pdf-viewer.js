// Penampil dokumen pustaka berbayar berbasis PDF.js.
// Halaman dirender ke <canvas> (bukan PDF native) sehingga tidak ada toolbar
// unduh/print bawaan browser, dan teks tidak mudah diseleksi/disalin.
// Fitur: navigasi halaman + daftar isi, zoom/fit, mode scroll & per-halaman,
// serta lazy-render dengan windowing agar PDF besar tetap ringan.
import * as pdfjsLib from 'pdfjs-dist';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl;

const ZOOM_STEP = 0.2;
const ZOOM_MIN = 0.5;
const ZOOM_MAX = 3;
const RENDER_WINDOW = 2; // render halaman aktif ± N, sisanya dibongkar untuk hemat memori

export function initPdfViewer() {
    const root = document.getElementById('pdf-viewer');
    if (!root) {
        return;
    }

    const els = {
        scroll: document.getElementById('pdf-scroll'),
        pages: document.getElementById('pdf-pages'),
        status: document.getElementById('pdf-status'),
        prev: document.getElementById('pdf-prev'),
        next: document.getElementById('pdf-next'),
        pageInput: document.getElementById('pdf-page-input'),
        pageCount: document.getElementById('pdf-page-count'),
        zoomIn: document.getElementById('pdf-zoom-in'),
        zoomOut: document.getElementById('pdf-zoom-out'),
        zoomLabel: document.getElementById('pdf-zoom-label'),
        fit: document.getElementById('pdf-fit'),
        mode: document.getElementById('pdf-mode'),
        tocToggle: document.getElementById('pdf-toc-toggle'),
        toc: document.getElementById('pdf-toc'),
        tocList: document.getElementById('pdf-toc-list'),
    };

    const state = {
        pdf: null,
        numPages: 0,
        current: 1,
        aspect: 1.4142, // tinggi/lebar, default A4 sampai halaman pertama termuat
        fitMode: 'width', // 'width' | 'page'
        zoom: 1,
        paged: false,
        pageEls: [], // { el, rendered, rendering, task }
        observer: null,
        suppressScroll: false,
    };

    root.addEventListener('contextmenu', (e) => e.preventDefault());

    const setStatus = (text) => {
        if (!els.status) return;
        els.status.textContent = text;
        els.status.classList.remove('hidden');
    };

    const availableWidth = () => Math.max((els.scroll.clientWidth || 800) - 32, 240);

    const renderWidth = () => {
        if (state.fitMode === 'page') {
            const h = Math.max((els.scroll.clientHeight || 600) - 32, 240);
            return Math.min((h / state.aspect) * state.zoom, availableWidth() * 1.5);
        }
        return availableWidth() * state.zoom;
    };

    const updateZoomLabel = () => {
        els.zoomLabel.textContent = `${Math.round(state.zoom * 100)}%`;
    };

    // --- Render satu halaman ke canvas ---
    const renderPage = async (pageNum) => {
        const slot = state.pageEls[pageNum - 1];
        if (!slot || slot.rendered || slot.rendering) return;
        slot.rendering = true;

        try {
            const page = await state.pdf.getPage(pageNum);
            const base = page.getViewport({ scale: 1 });
            const dpr = window.devicePixelRatio || 1;
            const width = renderWidth();
            const viewport = page.getViewport({ scale: (width / base.width) * dpr });

            const canvas = document.createElement('canvas');
            canvas.width = Math.floor(viewport.width);
            canvas.height = Math.floor(viewport.height);
            canvas.style.width = '100%';
            canvas.style.height = 'auto';
            canvas.className = 'block w-full';

            slot.task = page.render({ canvasContext: canvas.getContext('2d'), viewport });
            await slot.task.promise;

            slot.el.innerHTML = '';
            slot.el.appendChild(canvas);
            slot.rendered = true;
        } catch (e) {
            // RenderingCancelledException saat zoom/relayout — abaikan.
        } finally {
            slot.rendering = false;
        }
    };

    const unloadPage = (pageNum) => {
        const slot = state.pageEls[pageNum - 1];
        if (!slot || !slot.rendered) return;
        if (slot.task) {
            try { slot.task.cancel(); } catch (e) { /* noop */ }
        }
        slot.el.innerHTML = '';
        slot.rendered = false;
    };

    // Render halaman di sekitar halaman aktif; bongkar yang jauh.
    const refreshWindow = () => {
        for (let n = 1; n <= state.numPages; n += 1) {
            const near = Math.abs(n - state.current) <= RENDER_WINDOW;
            if (near) {
                renderPage(n);
            } else {
                unloadPage(n);
            }
        }
    };

    // --- Tata letak placeholder sesuai ukuran render saat ini ---
    const layoutPlaceholders = () => {
        const width = renderWidth();
        const height = width * state.aspect;
        state.pageEls.forEach((slot) => {
            slot.el.style.width = `${Math.round(width)}px`;
            if (!slot.rendered) {
                slot.el.style.height = `${Math.round(height)}px`;
            } else {
                slot.el.style.height = '';
            }
        });
    };

    const relayout = () => {
        // Bongkar semua agar dirender ulang pada skala baru.
        for (let n = 1; n <= state.numPages; n += 1) unloadPage(n);
        layoutPlaceholders();
        scrollToPage(state.current, false);
        refreshWindow();
        updateZoomLabel();
    };

    // --- Navigasi ---
    const setCurrent = (n) => {
        const clamped = Math.min(Math.max(n, 1), state.numPages || 1);
        state.current = clamped;
        els.pageInput.value = String(clamped);
        els.prev.disabled = clamped <= 1;
        els.next.disabled = clamped >= state.numPages;

        if (state.paged) {
            state.pageEls.forEach((slot, i) => {
                slot.el.classList.toggle('is-current', i === clamped - 1);
            });
        }
        refreshWindow();
    };

    const scrollToPage = (n, smooth = true) => {
        const slot = state.pageEls[n - 1];
        if (!slot) return;
        if (state.paged) return; // mode per-halaman tidak memakai scroll
        state.suppressScroll = true;
        els.scroll.scrollTo({ top: slot.el.offsetTop - 12, behavior: smooth ? 'smooth' : 'auto' });
        window.setTimeout(() => { state.suppressScroll = false; }, smooth ? 400 : 0);
    };

    const goToPage = (n) => {
        setCurrent(n);
        scrollToPage(state.current);
    };

    // Deteksi halaman aktif saat scroll (mode scroll).
    const onScroll = () => {
        if (state.suppressScroll || state.paged) return;
        const mid = els.scroll.scrollTop + els.scroll.clientHeight / 2;
        let found = 1;
        for (let i = 0; i < state.pageEls.length; i += 1) {
            if (state.pageEls[i].el.offsetTop <= mid) found = i + 1;
            else break;
        }
        if (found !== state.current) setCurrent(found);
    };

    // --- Mode baca ---
    const applyMode = () => {
        if (state.paged) {
            root.classList.add('is-paged');
            state.fitMode = 'page';
            state.zoom = 1;
            state.pageEls.forEach((slot, i) => slot.el.classList.toggle('is-current', i === state.current - 1));
        } else {
            root.classList.remove('is-paged');
            state.fitMode = 'width';
            state.zoom = 1;
            state.pageEls.forEach((slot) => slot.el.classList.remove('is-current'));
        }
        relayout();
        if (!state.paged) scrollToPage(state.current, false);
    };

    // --- Daftar isi ---
    const buildToc = async () => {
        let outline = null;
        try { outline = await state.pdf.getOutline(); } catch (e) { outline = null; }
        if (!outline || outline.length === 0) return;

        const resolvePage = async (dest) => {
            try {
                const explicit = typeof dest === 'string' ? await state.pdf.getDestination(dest) : dest;
                if (!explicit) return null;
                const index = await state.pdf.getPageIndex(explicit[0]);
                return index + 1;
            } catch (e) {
                return null;
            }
        };

        const addItems = (items, depth) => {
            items.forEach((item) => {
                const li = document.createElement('li');
                const a = document.createElement('button');
                a.type = 'button';
                a.textContent = item.title || '(tanpa judul)';
                a.className = 'block w-full text-left px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700/60 text-gray-600 dark:text-gray-300 truncate';
                a.style.paddingLeft = `${0.5 + depth * 0.75}rem`;
                a.addEventListener('click', async () => {
                    const p = await resolvePage(item.dest);
                    if (p) goToPage(p);
                });
                li.appendChild(a);
                els.tocList.appendChild(li);
                if (item.items && item.items.length) addItems(item.items, depth + 1);
            });
        };

        addItems(outline, 0);
        els.tocToggle.classList.remove('hidden');
    };

    // --- Event toolbar ---
    els.prev.addEventListener('click', () => goToPage(state.current - 1));
    els.next.addEventListener('click', () => goToPage(state.current + 1));
    els.pageInput.addEventListener('change', () => {
        const n = parseInt(els.pageInput.value, 10);
        if (!Number.isNaN(n)) goToPage(n);
    });
    els.zoomIn.addEventListener('click', () => {
        state.zoom = Math.min(state.zoom + ZOOM_STEP, ZOOM_MAX);
        relayout();
    });
    els.zoomOut.addEventListener('click', () => {
        state.zoom = Math.max(state.zoom - ZOOM_STEP, ZOOM_MIN);
        relayout();
    });
    els.fit.addEventListener('click', () => {
        state.fitMode = state.fitMode === 'width' ? 'page' : 'width';
        state.zoom = 1;
        relayout();
    });
    els.mode.addEventListener('click', () => {
        state.paged = !state.paged;
        applyMode();
    });
    els.tocToggle.addEventListener('click', () => els.toc.classList.toggle('hidden'));

    els.scroll.addEventListener('scroll', onScroll, { passive: true });

    // Keyboard & swipe untuk mode per-halaman / navigasi cepat.
    root.tabIndex = 0;
    root.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' || e.key === 'PageDown') { goToPage(state.current + 1); }
        else if (e.key === 'ArrowLeft' || e.key === 'PageUp') { goToPage(state.current - 1); }
    });
    let touchX = null;
    els.scroll.addEventListener('touchstart', (e) => { touchX = e.changedTouches[0].clientX; }, { passive: true });
    els.scroll.addEventListener('touchend', (e) => {
        if (touchX === null || !state.paged) return;
        const dx = e.changedTouches[0].clientX - touchX;
        if (Math.abs(dx) > 60) goToPage(state.current + (dx < 0 ? 1 : -1));
        touchX = null;
    }, { passive: true });

    window.addEventListener('resize', () => {
        window.clearTimeout(state.resizeTimer);
        state.resizeTimer = window.setTimeout(relayout, 200);
    });

    // --- Muat dokumen ---
    setStatus('Memuat dokumen…');
    const loadingTask = pdfjsLib.getDocument({
        url: root.dataset.src,
        // Tandai sebagai permintaan XHR agar endpoint dokumen menolak akses langsung di tab baru.
        httpHeaders: { 'X-Requested-With': 'XMLHttpRequest' },
        withCredentials: true,
    });

    loadingTask.promise
        .then(async (pdf) => {
            state.pdf = pdf;
            state.numPages = pdf.numPages;
            els.pageCount.textContent = String(pdf.numPages);
            els.pageInput.max = String(pdf.numPages);

            const first = await pdf.getPage(1);
            const v = first.getViewport({ scale: 1 });
            state.aspect = v.height / v.width;

            els.status.classList.add('hidden');

            // Buat placeholder + observer lazy-render.
            const width = renderWidth();
            const height = width * state.aspect;
            const io = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const n = Number(entry.target.dataset.page);
                        renderPage(n);
                    }
                });
            }, { root: els.scroll, rootMargin: '600px 0px' });
            state.observer = io;

            for (let n = 1; n <= pdf.numPages; n += 1) {
                const el = document.createElement('div');
                el.className = 'pdf-page mx-auto mb-4 bg-white shadow rounded overflow-hidden select-none';
                el.dataset.page = String(n);
                el.style.width = `${Math.round(width)}px`;
                el.style.height = `${Math.round(height)}px`;
                els.pages.appendChild(el);
                state.pageEls.push({ el, rendered: false, rendering: false, task: null });
                io.observe(el);
            }

            setCurrent(1);
            await renderPage(1);
            buildToc();
        })
        .catch(() => {
            setStatus('Gagal memuat dokumen. Pastikan Anda memiliki akses dan coba muat ulang halaman.');
        });
}
