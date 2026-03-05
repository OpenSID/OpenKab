<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode Token OpenKab</title>
   <style nonce="{{ csp_nonce() }}" >
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 25px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
        }
        .otp-code {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            font-size: 36px;
            font-weight: bold;
            text-align: center;
            padding: 25px;
            border-radius: 12px;
            letter-spacing: 8px;
            margin: 30px 0;
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
        }
        .info-box {
            background-color: #e3f2fd;
            border-left: 5px solid #007bff;
            padding: 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 20px;
            margin: 25px 0;
            color: #856404;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 25px;
            border-top: 2px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            margin: 20px 0;
            font-weight: bold;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            text-align: center;
        }
        .stat-item {
            flex: 1;
            padding: 15px;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🔐</div>
            <h1>Kode Token OpenKab</h1>
            <p style="color: #6c757d; margin: 10px 0 0;">Sistem Autentikasi Tanpa Password atau 2FA</p>
        </div>

        <p style="font-size: 16px;">Halo,</p>

        <p>Anda telah meminta kode token untuk sistem OpenKab. Gunakan kode berikut untuk menyelesaikan proses verifikasi:</p>

        <div class="otp-code">
            {{ $otp }}
        </div>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-number">5</div>
                <div class="stat-label">Menit</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">1x</div>
                <div class="stat-label">Penggunaan</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">3</div>
                <div class="stat-label">Max Percobaan</div>
            </div>
        </div>

        <div class="info-box">
            <p><strong>📋 Informasi Penting:</strong></p>
            <ul style="margin: 10px 0;">
                <li>Kode ini berlaku selama <strong>5 menit</strong> dari waktu pengiriman</li>
                <li>Kode hanya dapat digunakan <strong>satu kali</strong></li>
                <li>Maksimal <strong>3 kali percobaan</strong> salah sebelum kode diblokir</li>                
            </ul>
        </div>

        <div class="warning-box">
            <p><strong>⚠️ Peringatan Keamanan:</strong></p>
            <p>Jangan bagikan kode ini kepada siapa pun. Tim OpenKab tidak akan pernah meminta kode OTP Anda melalui telepon, email balasan, atau cara lainnya. Jika Anda tidak meminta kode ini, mohon abaikan email ini dan segera hubungi administrator sistem.</p>
        </div>

        <p style="color: #6c757d; font-size: 14px;">
            <strong>Catatan:</strong> Setelah aktivasi berhasil, OTP akan menjadi opsi alternatif login. Anda tetap dapat menggunakan username dan password seperti biasa.
        </p>

        <div class="footer">
            <p><strong>OpenKab</strong> - Sistem Dashboard Kabupaten</p>
            <p>Email ini dikirim secara otomatis oleh sistem. Harap jangan membalas email ini.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi undang-undang.</p>
            <p style="font-size: 12px; margin-top: 15px;">
                Dikirim pada {{ now()->format('d/m/Y H:i:s') }} WIB
            </p>
        </div>
    </div>
</body>
</html>