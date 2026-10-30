<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OtpToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function generateAndSend($userId, $channel, $identifier)
    {
        try {
            // Generate OTP with configurable length
            $otpLength = config('app.otp_length', 6);
            $min = pow(10, $otpLength - 1);
            $max = pow(10, $otpLength) - 1;
            $otp = random_int($min, $max);
            $hash = Hash::make($otp);
            $expires = now()->addMinutes(config('app.otp_token_expires_minutes', 5));

            // Delete any existing tokens for this user
            OtpToken::where('user_id', $userId)->delete();

            // Create new token
            OtpToken::create([
                'user_id' => $userId,
                'token_hash' => $hash,
                'channel' => $channel,
                'identifier' => $identifier,
                'expires_at' => $expires,
                'attempts' => 0,
            ]);

            // Send OTP based on channel
            if ($channel === 'email') {
                Mail::to($identifier)->send(new OtpMail($otp));
                Log::info('OTP Sent via Email', ['user_id' => $userId, 'email' => $identifier]);
            } elseif ($channel === 'telegram') {
                $this->sendTelegramOtp($identifier, $otp);
                Log::info('OTP Sent via Telegram', ['user_id' => $userId, 'chat_id' => $identifier]);
            }

            return [
                'success' => true,
                'message' => 'Kode Token berhasil dikirim',
                'channel' => $channel,
            ];

        } catch (\Exception $e) {
            Log::error('OTP Generation Failed', ['user_id' => $userId, 'error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Gagal mengirim OTP: '.$e->getMessage(),
            ];
        }
    }

    private function sendTelegramOtp($chatId, $otp)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        if (! $botToken) {
            throw new \Exception('Telegram bot token tidak dikonfigurasi');
        }

        $expiresMinutes = config('app.otp_token_expires_minutes', 5);
        $message = "🔐 *Kode Token OpenKab*\n\n";
        $message .= "Kode verifikasi Anda: *{$otp}*\n\n";
        $message .= "Kode berlaku selama {$expiresMinutes} menit.\n";
        $message .= 'Jangan bagikan kode ini kepada siapa pun!';

        $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);

        if (! $response->successful()) {
            throw new \Exception('Gagal mengirim pesan Telegram');
        }
    }

    public function verify($userId, $submittedOtp)
    {
        $token = OtpToken::where('user_id', $userId)
            ->where('expires_at', '>', now())
            ->first();

        if (! $token) {
            return [
                'success' => false,
                'message' => 'Token tidak ditemukan atau sudah kedaluwarsa',
            ];
        }

        $maxAttempts = config('app.otp_max_verification_attempts', 3);
        if ($token->attempts >= $maxAttempts) {
            return [
                'success' => false,
                'message' => 'Terlalu banyak percobaan salah. Silakan minta kode baru.',
            ];
        }

        if (Hash::check($submittedOtp, $token->token_hash)) {
            $token->delete();
            Log::info('OTP Verified Successfully', ['user_id' => $userId]);

            return [
                'success' => true,
                'message' => 'Kode Token berhasil diverifikasi',
            ];
        } else {
            $token->increment('attempts');
            $maxAttempts = config('app.otp_max_verification_attempts', 3);
            Log::warning('OTP Verification Failed', ['user_id' => $userId, 'attempts' => $token->attempts + 1]);

            return [
                'success' => false,
                'message' => 'Kode Token salah. Percobaan tersisa: '.($maxAttempts - ($token->attempts + 1)),
            ];
        }
    }

    public function cleanupExpired()
    {
        $deleted = OtpToken::where('expires_at', '<', now())->delete();
        Log::info('Expired OTP tokens cleaned up', ['deleted' => $deleted]);

        return $deleted;
    }
}
