// Penampil dokumen pustaka berbayar berbasis PDF.js.
// Halaman dirender ke <canvas> (bukan PDF native) sehingga tidak ada toolbar
// unduh/print bawaan browser, dan teks tidak mudah diseleksi/disalin.
import * as pdfjsLib from 'pdfjs-dist';
import workerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl;

export function initPdfViewer() {
    const root = document.getElementById('pdf-viewer');
    if (!root) {
        return;
    }

    const src = root.dataset.src;
    const pagesEl = document.getElementById('pdf-pages');
    const statusEl = document.getElementById('pdf-status');

    // Deterrent dasar terhadap penyalinan konten.
    root.addEventListener('contextmenu', (e) => e.preventDefault());

    const setStatus = (text) => {
        if (statusEl) {
            statusEl.textContent = text;
            statusEl.classList.remove('hidden');
        }
    };

    const renderPage = async (pdf, pageNumber, targetWidth) => {
        const page = await pdf.getPage(pageNumber);
        const base = page.getViewport({ scale: 1 });
        const dpr = window.devicePixelRatio || 1;
        const scale = (targetWidth / base.width) * dpr;
        const viewport = page.getViewport({ scale });

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = Math.floor(viewport.width);
        canvas.height = Math.floor(viewport.height);
        canvas.style.width = '100%';
        canvas.style.height = 'auto';
        canvas.className = 'block w-full';

        const wrapper = document.createElement('div');
        wrapper.className = 'mx-auto mb-4 bg-white shadow rounded overflow-hidden select-none';
        wrapper.style.maxWidth = `${targetWidth}px`;
        wrapper.appendChild(canvas);
        pagesEl.appendChild(wrapper);

        await page.render({ canvasContext: ctx, viewport }).promise;
    };

    setStatus('Memuat dokumen…');

    const loadingTask = pdfjsLib.getDocument({
        url: src,
        // Tandai sebagai permintaan XHR agar endpoint dokumen menolak akses langsung di tab baru.
        httpHeaders: { 'X-Requested-With': 'XMLHttpRequest' },
        withCredentials: true,
    });

    loadingTask.promise
        .then(async (pdf) => {
            if (statusEl) {
                statusEl.classList.add('hidden');
            }

            const targetWidth = Math.min(pagesEl.clientWidth || 800, 900);

            for (let n = 1; n <= pdf.numPages; n += 1) {
                // Render berurutan agar urutan halaman terjaga dan memori terkendali.
                // eslint-disable-next-line no-await-in-loop
                await renderPage(pdf, n, targetWidth);
            }
        })
        .catch(() => {
            setStatus('Gagal memuat dokumen. Pastikan Anda memiliki akses dan coba muat ulang halaman.');
        });
}
