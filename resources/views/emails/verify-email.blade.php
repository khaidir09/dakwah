<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Alamat Email</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #374151;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
            margin-bottom: 40px;
        }
        .header {
            background-color: #047857; /* Emerald-700 */
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .message {
            margin-bottom: 30px;
            font-size: 16px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background-color: #059669; /* Emerald-600 */
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
        }
        .button:hover {
            background-color: #047857;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .note {
            font-size: 14px;
            color: #6b7280;
            margin-top: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .link-break {
            word-break: break-all;
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Verifikasi Alamat Email</h1>
        </div>
        <div class="content">
            <p class="message">Halo!</p>
            <p class="message">Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda.</p>

            <p class="message"><strong>Agar dapat menikmati fitur-fitur utama, maka diperlukan verifikasi email terlebih dahulu.</strong></p>

            <div class="button-container">
                <a href="{{ $url }}" class="button">Verifikasi Email</a>
            </div>

            <p class="message">Jika Anda tidak membuat akun, tidak ada tindakan lebih lanjut yang diperlukan.</p>

            <div class="note">
                <p>Jika Anda mengalami masalah saat mengklik tombol "Verifikasi Email", salin dan tempel URL di bawah ini ke browser web Anda:</p>
                <p><a href="{{ $url }}" class="link-break">{{ $url }}</a></p>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
