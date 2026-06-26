<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class RecaptchaV3ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            // Override reCAPTCHA v3 configuration with database values
            $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        } catch (\Exception $e) {
            // Settings table may not exist yet during initial installation
            return;
        }
        
        // Only override if captcha is enabled and type is not builtin
        $captchaEnabled = filter_var($settings['captcha_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $captchaType = $settings['captcha_type'] ?? 'builtin';
        
        if ($captchaEnabled && $captchaType !== 'builtin') {
            $siteKey = $settings['google_recaptcha_site_key'] ?? null;
            $secretKey = $settings['google_recaptcha_secret_key'] ?? null;
            
            // Fallback to .env if database values are null or empty
            if (empty($siteKey)) {
                $siteKey = env('RECAPTCHAV3_SITEKEY', '');
            }
            
            if (empty($secretKey)) {
                $secretKey = env('RECAPTCHAV3_SECRET', '');
            }
            
            // Validate that both keys are present and not empty
            if (empty($siteKey) || empty($secretKey)) {
                // Log warning for missing keys
                Log::warning('reCAPTCHA v3 keys are not configured in database settings or .env', [
                    'site_key_set' => !empty($siteKey),
                    'secret_key_set' => !empty($secretKey),
                    'captcha_type' => $captchaType
                ]);
                
                // Don't override config if keys are missing
                return;
            }
            
            Config::set('recaptchav3.sitekey', $siteKey);
            Config::set('recaptchav3.secret', $secretKey);
            
            // Log successful configuration
            Log::info('reCAPTCHA v3 configuration loaded', [
                'source' => !empty($settings['google_recaptcha_site_key']) ? 'database' : '.env',
                'site_key_prefix' => substr($siteKey, 0, 8) . '...',
                'secret_key_prefix' => substr($secretKey, 0, 8) . '...'
            ]);
        }
    }
}