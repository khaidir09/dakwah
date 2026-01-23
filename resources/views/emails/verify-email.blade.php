@component('mail::message')
# Verifikasi Alamat Email

Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda.

Agar dapat menikmati fitur-fitur utama, maka diperlukan verifikasi email terlebih dahulu.

@component('mail::button', ['url' => $url])
Verifikasi Email
@endcomponent

Jika Anda tidak membuat akun, tidak ada tindakan lebih lanjut yang diperlukan.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
