<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password OpenKab</title>
    <style nonce="{{ csp_nonce() }}">
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 10px; }
        .header { text-align: center; border-bottom: 3px solid #007bff; padding-bottom: 20px; margin-bottom: 25px; }
        .header h1 { color: #007bff; margin: 0; font-size: 22px; }
        .password { display: inline-block; font-family: monospace; font-size: 18px; background: #f1f3f5; padding: 10px 16px; border-radius: 6px; letter-spacing: 1px; }
        .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-top: 20px; border-radius: 4px; }
        .footer { margin-top: 30px; font-size: 12px; color: #6c757d; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reset Password OpenKab</h1>
        </div>
        <p>Halo {{ $nama }},</p>
        <p>Password akun OpenKab Anda telah direset oleh administrator. Gunakan password sementara berikut untuk login:</p>
        <p class="password">{{ $passwordSementara }}</p>
        <div class="warning">
            Anda wajib mengganti password ini saat login berikutnya. Jika Anda tidak merasa meminta reset, segera hubungi administrator.
        </div>
        <div class="footer">Email ini dikirim otomatis oleh OpenKab. Jangan membalas email ini.</div>
    </div>
</body>
</html>
