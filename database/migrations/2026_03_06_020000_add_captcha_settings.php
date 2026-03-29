<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {        
        // Insert captcha settings using Eloquent model
        $settings = [
            [
                'key' => 'captcha_enabled',
                'name' => 'Aktifkan CAPTCHA',
                'value' => '1',
                'type' => 'dropdown',
                'attribute' => [
                    ['text' => 'Tidak Aktif', 'value' => 0],
                    ['text' => 'Aktif', 'value' => 1],
                ],
                'description' => 'Aktifkan sistem CAPTCHA untuk melindungi form login dari serangan bot',
            ],
            [
                'key' => 'captcha_type',
                'name' => 'Tipe CAPTCHA',
                'value' => 'builtin',
                'type' => 'dropdown',
                'attribute' => [
                    ['text' => 'Bawaan', 'value' => 'builtin'],
                    ['text' => 'Google reCAPTCHA v3', 'value' => 'google'],
                ],
                'description' => 'Pilih tipe CAPTCHA yang akan digunakan',
            ],
            [
                'key' => 'captcha_threshold',
                'name' => 'Ambang Batas Gagal Login',
                'value' => '2',
                'type' => 'number',
                'attribute' => json_encode(['min' => 1, 'max' => 10]),
                'description' => 'Tampilkan CAPTCHA setelah jumlah percobaan login gagal sebanyak ini',
            ],
            [
                'key' => 'google_recaptcha_site_key',
                'name' => 'Google reCAPTCHA Site Key',
                'value' => '',
                'type' => 'text',
                'attribute' => json_encode(['placeholder' => 'Masukkan Site Key dari Google reCAPTCHA']),
                'description' => 'Site Key untuk Google reCAPTCHA v3',
            ],
            [
                'key' => 'google_recaptcha_secret_key',
                'name' => 'Google reCAPTCHA Secret Key',
                'value' => '',
                'type' => 'text',
                'attribute' => json_encode(['placeholder' => 'Masukkan Secret Key dari Google reCAPTCHA']),
                'description' => 'Secret Key untuk Google reCAPTCHA v3',
            ],
            [
                'key' => 'google_recaptcha_score_threshold',
                'name' => 'Google reCAPTCHA Score Threshold',
                'value' => '0.5',
                'type' => 'number',
                'attribute' => json_encode(['min' => 0.1, 'max' => 1.0, 'step' => 0.1]),
                'description' => 'Ambang batas skor minimum untuk dianggap sebagai manusia (0.0-1.0)',
            ],
        ];

        // Use Eloquent model to create or update settings
        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove captcha settings using Eloquent model
        Setting::whereIn('key', [
            'captcha_enabled',
            'captcha_type',
            'captcha_threshold',
            'google_recaptcha_site_key',
            'google_recaptcha_secret_key',
            'google_recaptcha_score_threshold',
        ])->delete();
    }
};