<?php

namespace App\Http\Controllers;

use App\Http\Requests\OtpSetupRequest;
use App\Http\Requests\OtpVerifyRequest;
use App\Services\OtpService;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class OtpController extends Controller
{
    protected $otpService;
    protected $twoFactorService;

    public function __construct(
        OtpService $otpService,
        TwoFactorService $twoFactorService
    ) {
        $this->otpService = $otpService;
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Tampilkan halaman aktivasi OTP & 2FA
     */
    public function index()
    {
        $user = Auth::user();        
        $twoFactorStatus = $this->twoFactorService->getUserTwoFactorStatus($user);
        
        return view('admin.pengaturan.otp.index', compact('user', 'twoFactorStatus'));
    }

    public function activate()
    {
        $user = Auth::user();
        $otpConfig = [
            'expires_minutes' => config('app.otp_token_expires_minutes', 5),
            'resend_seconds' => config('app.otp_resend_decay_seconds', 30),
            'length' => config('app.otp_length', 6),
        ];                
        
        return view('admin.pengaturan.otp.activation-form', compact('user', 'otpConfig'));
    }


    /**
     * Setup konfigurasi OTP untuk user
     */
    public function setup(OtpSetupRequest $request)
    {
        $userId = Auth::id();

        // Rate limiting untuk setup dengan enhanced key (IP + User-Agent + User ID)
        $key = $this->getOtpSetupRateLimitKey($request, $userId);
        $maxAttempts = config('app.otp_setup_max_attempts', 3);
        $decaySeconds = config('app.otp_setup_decay_seconds', 300);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);
        $identifier = $request->channel === 'email' ? Auth::user()->email : Auth::user()->telegram_chat_id;
        // Simpan konfigurasi sementara di session (hanya jika session tersedia)
        if ($request->hasSession()) {
            $request->session()->put('temp_otp_config', [
                'channel' => $request->channel,
                'identifier' => $identifier,
            ]);
        }

        // Kirim OTP untuk verifikasi
        $result = $this->otpService->generateAndSend(
            Auth::id(),
            $request->channel,
            $identifier
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim untuk verifikasi aktivasi',
                'channel' => $request->channel
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Verifikasi OTP untuk aktivasi
     */
    public function verifyActivation(OtpVerifyRequest $request)
    {
        $userId = Auth::id();

        // Rate limiting untuk verifikasi dengan enhanced key
        $key = $this->getOtpVerifyRateLimitKey($request, $userId);
        $maxAttempts = config('app.otp_verify_max_attempts', 5);
        $decaySeconds = config('app.otp_verify_decay_seconds', 300);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan verifikasi. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        $tempConfig = null;
        if ($request->hasSession()) {
            $tempConfig = $request->session()->get('temp_otp_config');
        }

        // Untuk testing, jika tidak ada session, gunakan data dari request jika ada
        if (!$tempConfig && app()->environment('testing') && $request->has(['channel', 'identifier'])) {
            $tempConfig = [
                'channel' => $request->input('channel'),
                'identifier' => $request->input('identifier')
            ];
        }

        if (!$tempConfig) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi aktivasi tidak ditemukan. Silakan mulai dari awal.'
            ], 400);
        }

        $result = $this->otpService->verify(Auth::id(), $request->otp);

        if ($result['success']) {
            // Aktivasi OTP berhasil
            $user = Auth::user();
            $user->update([
                'otp_enabled' => true,
                'otp_channel' => json_encode([$tempConfig['channel']]),
                'otp_identifier' => $tempConfig['identifier'],
            ]);

            // Hapus konfigurasi sementara
            if ($request->hasSession()) {
                $request->session()->forget('temp_otp_config');
            }
            RateLimiter::clear($key);

            return response()->json([
                'success' => true,
                'message' => 'OTP berhasil diaktifkan! Anda sekarang dapat menggunakan OTP sebagai alternatif login.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Nonaktifkan OTP
     */
    public function disable(Request $request)
    {
        $user = Auth::user();
        
        $user->update([
            'otp_enabled' => false,
            'otp_channel' => null,
            'otp_identifier' => null,
        ]);

        // Hapus semua token OTP yang ada
        $user->otpTokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil dinonaktifkan'
        ]);
    }

    /**
     * Kirim ulang OTP
     */
    public function resend(Request $request)
    {
        $tempConfig = null;
        if ($request->hasSession()) {
            $tempConfig = $request->session()->get('temp_otp_config');
        }

        // Untuk testing, jika tidak ada session, gunakan data dari request jika ada
        if (!$tempConfig && app()->environment('testing') && $request->has(['channel', 'identifier'])) {
            $tempConfig = [
                'channel' => $request->input('channel'),
                'identifier' => $request->input('identifier')
            ];
        }

        if (!$tempConfig) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi aktivasi tidak ditemukan.'
            ], 400);
        }

        $userId = Auth::id();

        // Rate limiting untuk resend dengan enhanced key
        $key = $this->getOtpResendRateLimitKey($request, $userId);
        $maxAttempts = config('app.otp_resend_max_attempts', 2);
        $decaySeconds = config('app.otp_resend_decay_seconds', 30);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Tunggu ' . RateLimiter::availableIn($key) . ' detik sebelum mengirim ulang.'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        $result = $this->otpService->generateAndSend(
            Auth::id(),
            $tempConfig['channel'],
            $tempConfig['identifier']
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Nonaktifkan 2FA dari controller ini untuk konsistensi
     */
    public function disable2fa(Request $request)
    {
        $result = $this->twoFactorService->disableTwoFactor(Auth::user());

        return response()->json([
            'success' => $result,
            'message' => $result ? '2FA berhasil dinonaktifkan' : 'Gagal menonaktifkan 2FA'
        ]);
    }

    /**
     * Generate rate limit key for OTP setup.
     * Combines IP, User-Agent, and user ID.
     */
    protected function getOtpSetupRateLimitKey(Request $request, int $userId): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        
        return "otp-setup:{$userId}:{$ip}:{$userAgent}";
    }

    /**
     * Generate rate limit key for OTP verification.
     * Combines IP, User-Agent, and user ID.
     */
    protected function getOtpVerifyRateLimitKey(Request $request, int $userId): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        
        return "otp-verify:{$userId}:{$ip}:{$userAgent}";
    }

    /**
     * Generate rate limit key for OTP resend.
     * Combines IP, User-Agent, and user ID.
     */
    protected function getOtpResendRateLimitKey(Request $request, int $userId): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        
        return "otp-resend:{$userId}:{$ip}:{$userAgent}";
    }
}
