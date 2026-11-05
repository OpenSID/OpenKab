<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;

class CaptchaService
{
    /**
     * Validate captcha using Mews\Captcha
     */
    public function validate(string $code): bool
    {
        return captcha_check($code);
    }

    /**
     * Get captcha image URL
     */
    public function getImageUrl(): string
    {
        return captcha_src();
    }

    /**
     * Check if CAPTCHA is enabled
     */
    public function isEnabled(): bool
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return filter_var($settings['captcha_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if captcha should be shown based on failed attempts
     */
    public function shouldShow(Request $request): bool
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $enabled = filter_var($settings['captcha_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $threshold = (int) ($settings['captcha_threshold'] ?? 2);

        if (! $enabled) {
            return false;
        }

        $key = $this->getRateLimitKey($request);
        $attempts = RateLimiter::attempts($key);

        return $attempts >= $threshold;
    }

    /**
     * Get captcha configuration from database
     */
    public function getCaptchaConfig(): array
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $type = $settings['captcha_type'] ?? 'builtin';
        // jika menggunakan recaptcha v3, pastikan sitekey dan secret key terisi
        if ($type == 'google') {
            if (empty($settings['google_recaptcha_site_key']) or empty($settings['google_recaptcha_secret_key'])) {
                $type = 'builtin';
            }
        }

        return [
            'enabled' => filter_var($settings['captcha_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'type' => $type,
            'threshold' => (int) ($settings['captcha_threshold'] ?? 2),
            'google_site_key' => $settings['google_recaptcha_site_key'] ?? '',
            'google_secret_key' => $settings['google_recaptcha_secret_key'] ?? '',
        ];
    }

    /**
     * Get rate limit key for request
     */
    protected function getRateLimitKey(Request $request): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');

        return "captcha:{$ip}:{$userAgent}";
    }

    /**
     * Increment failed attempts
     */
    public function incrementFailedAttempts(Request $request): void
    {
        $key = $this->getRateLimitKey($request);
        RateLimiter::hit($key, 300);
    }

    /**
     * Reset failed attempts
     */
    public function resetFailedAttempts(Request $request): void
    {
        $key = $this->getRateLimitKey($request);
        RateLimiter::clear($key);
    }

    /**
     * Clear captcha session
     */
    public function clearCaptchaSession(): void
    {
        Session::forget(['captcha_id', 'captcha_time']);
    }
}
