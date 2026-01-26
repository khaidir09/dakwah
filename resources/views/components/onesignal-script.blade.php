@if(config('services.onesignal.app_id'))
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(function(OneSignal) {
            OneSignal.init({
                appId: "{{ config('services.onesignal.app_id') }}",
                safari_web_id: "web.onesignal.auto.{{ config('services.onesignal.app_id') }}",
                notifyButton: {
                    enable: true,
                    colors: {
                        'circle.background': '#059669',
                        'circle.foreground': 'white',
                        'badge.background': '#059669',
                        'badge.foreground': 'white',
                        'badge.border': 'white',
                        'pulse.color': 'white',
                        'dialog.button.background.hovering': '#047857',
                        'dialog.button.background.active': '#059669',
                        'dialog.button.background': '#059669',
                        'dialog.button.foreground': 'white',
                    },
                    text: {
                        'tip.state.unsubscribed': 'Berlangganan notifikasi',
                        'tip.state.subscribed': 'Anda telah berlangganan',
                        'tip.state.blocked': 'Notifikasi diblokir',
                        'message.action.subscribed': 'Terima kasih telah berlangganan!',
                        'message.action.resubscribed': 'Anda kembali berlangganan notifikasi.',
                        'message.action.unsubscribed': 'Anda berhenti berlangganan.',
                        'dialog.main.title': 'Kelola Notifikasi',
                        'dialog.main.button.subscribe': 'BERLANGGANAN',
                        'dialog.main.button.unsubscribe': 'BERHENTI',
                        'dialog.blocked.title': 'Buka Blokir Notifikasi',
                        'dialog.blocked.message': 'Ikuti petunjuk ini untuk mengizinkan notifikasi:',
                    }
                },
                allowLocalhostAsSecureOrigin: {{ app()->isLocal() ? 'true' : 'false' }},
            }).then(() => {
                @auth
                    // Associate the session user with the OneSignal user
                    if (OneSignal.User.externalId !== "{{ auth()->id() }}") {
                        OneSignal.login("{{ auth()->id() }}");
                    }
                @endauth
            });
        });
    </script>
@endif
