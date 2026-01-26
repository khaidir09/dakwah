@if(config('services.onesignal.app_id'))
    <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
    <script>
        window.OneSignalDeferred = window.OneSignalDeferred || [];
        OneSignalDeferred.push(function(OneSignal) {
            OneSignal.init({
                appId: "{{ config('services.onesignal.app_id') }}",
                safari_web_id: "web.onesignal.auto.{{ config('services.onesignal.app_id') }}", // Default Safari ID pattern, can be customized if needed
                notifyButton: {
                    enable: true,
                },
                allowLocalhostAsSecureOrigin: {{ app()->isLocal() ? 'true' : 'false' }},
            });

            @auth
                // Associate the session user with the OneSignal user
                OneSignal.login("{{ auth()->id() }}");
            @endauth
        });
    </script>
@endif
