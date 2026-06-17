<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForcePasswordResetRequest;
use App\Models\PasswordHistory;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordResetController extends Controller
{
    /**
     * Show the force password reset form.
     */
    public function showForm()
    {
        $user = Auth::user();

        // Only show if user actually needs to reset
        if (!$user->requiresPasswordReset()) {
            return redirect()->route('dasbor');
        }

        return view('auth.force-password-reset');
    }

    /**
     * Process the force password reset.
     */
    public function reset(ForcePasswordResetRequest $request)
    {
        $user = Auth::user();

        // Only allow if user actually needs to reset
        if (!$user->requiresPasswordReset()) {
            return redirect()->route('dasbor');
        }

        $expiryDays = config('password.expiry_days');

        // Save old password to history
        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => $user->password,
            'reason' => 'forced_reset_completed',
        ]);

        // Set new password
        $user->password = $request->password;

        // Set expiry if configured
        if ($expiryDays) {
            $user->password_expires_at = now()->addDays($expiryDays);
        }

        // Reset force_password_reset flag
        $user->force_password_reset = false;
        $user->save();

        // Redirect to intended URL or home
        $intendedUrl = session('intended_url', url(AppServiceProvider::HOME));
        session()->forget('intended_url');

        return redirect($intendedUrl)
            ->with('success', 'Password berhasil diubah. Sekarang Anda dapat melanjutkan menggunakan aplikasi.');
    }
}
