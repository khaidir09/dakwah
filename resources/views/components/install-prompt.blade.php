<div x-data="installPrompt()"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
     style="display: none;"
     class="fixed bottom-0 inset-x-0 pb-2 sm:pb-5 z-50">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
        <div class="p-3 rounded-lg bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="w-0 flex-1 flex items-center">
                    <span class="flex p-2 rounded-lg bg-blue-600">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </span>
                    <p class="ml-3 font-medium text-gray-900 dark:text-white truncate">
                        <span class="md:hidden">Install aplikasi {{ config('app.name', 'Syaikhuna') }}!</span>
                        <span class="hidden md:inline">Install aplikasi {{ config('app.name', 'Syaikhuna') }} untuk pengalaman yang lebih baik.</span>
                    </p>
                </div>
                <div class="order-3 mt-2 flex-shrink-0 w-full sm:order-2 sm:mt-0 sm:w-auto flex gap-2">
                    <button @click="install" class="w-1/2 sm:w-auto flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Install
                    </button>
                    <button @click="dismiss" type="button" class="w-1/2 sm:w-auto flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Nanti
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('installPrompt', () => ({
            show: false,
            deferredPrompt: null,
            isIOS: false,
            isStandalone: false,

            init() {
                this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

                if (this.isStandalone) {
                    return;
                }

                if (localStorage.getItem('installPromptDismissed') === 'true') {
                    return;
                }

                const userAgent = window.navigator.userAgent.toLowerCase();
                this.isIOS = /iphone|ipad|ipod/.test(userAgent);
                const isAndroid = /android/.test(userAgent);
                const isMobile = this.isIOS || isAndroid;

                if (!isMobile) {
                    return;
                }

                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    if (isAndroid) {
                        this.show = true;
                    }
                });

                if (this.isIOS) {
                    setTimeout(() => {
                        this.show = true;
                    }, 2000);
                }
            },

            async install() {
                if (this.deferredPrompt) {
                    this.deferredPrompt.prompt();
                    const { outcome } = await this.deferredPrompt.userChoice;
                    if (outcome === 'accepted') {
                        this.show = false;
                        localStorage.setItem('installPromptDismissed', 'true');
                    }
                    this.deferredPrompt = null;
                } else if (this.isIOS) {
                    alert('Untuk menginstall, ketuk ikon Share di browser Anda lalu pilih "Add to Home Screen" atau "Tambah ke Layar Utama".');
                    this.show = false;
                    localStorage.setItem('installPromptDismissed', 'true');
                }
            },

            dismiss() {
                this.show = false;
                localStorage.setItem('installPromptDismissed', 'true');
            }
        }));
    });
</script>
