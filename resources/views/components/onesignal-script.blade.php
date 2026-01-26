@if(config('services.onesignal.app_id'))
    @auth
        <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
        <script>
            window.OneSignalDeferred = window.OneSignalDeferred || [];
            OneSignalDeferred.push(function(OneSignal) {
                OneSignal.init({
                    appId: "{{ config('services.onesignal.app_id') }}",
                    safari_web_id: "web.onesignal.auto.{{ config('services.onesignal.app_id') }}",
                    notifyButton: {
                        enable: true,
                        // ... (pengaturan warna/text Anda biarkan saja)
                    },
                    // Pastikan ini true jika testing di localhost tanpa HTTPS
                    allowLocalhostAsSecureOrigin: {{ app()->isLocal() ? 'true' : 'false' }},
                });

                // Login user identity
                OneSignal.login("{{ auth()->id() }}");

                // Fungsi Sync dengan Debugging & Error Handling
                function syncOneSignalId(id) {
                    console.log("[OneSignal] Mencoba sync ID:", id);
                    if (!id) {
                        console.warn("[OneSignal] ID kosong, membatalkan sync.");
                        return;
                    }

                    fetch('{{ route('user.onesignal.update') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json' // Tambahkan ini
                        },
                        body: JSON.stringify({ one_signal_id: id })
                    })
                    .then(response => {
                        if (!response.ok) {
                            // Jika server merespon 401/419/500
                            return response.text().then(text => { throw new Error(text) });
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("[OneSignal] Berhasil disimpan ke DB:", data);
                    })
                    .catch(error => {
                        console.error("[OneSignal] Gagal menyimpan ke DB:", error);
                    });
                }

                // Cek ID saat load page
                var dbOneSignalId = "{{ auth()->user()->one_signal_id }}";
                var subscriptionId = OneSignal.User.PushSubscription.id;
                
                console.log("[OneSignal] Status Awal - DB:", dbOneSignalId, "SDK ID:", subscriptionId);

                // Jika ID SDK ada dan beda dengan DB, sync
                if (subscriptionId && subscriptionId !== dbOneSignalId) {
                    syncOneSignalId(subscriptionId);
                }

                // Event Listener: Tangkap perubahan saat user baru saja subscribe
                OneSignal.User.PushSubscription.addEventListener("change", function(event) {
                    console.log("[OneSignal] Subscription Change Event:", event);
                    if (event.current.id) {
                        syncOneSignalId(event.current.id);
                    }
                });
            });
        </script>
    @endauth
@endif