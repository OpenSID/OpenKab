<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForcePasswordResetRequest;
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

        $user->passwordHistoryReason = 'forced_reset_completed';
        $user->password = $request->password;

        if ($expiryDays) {
            $user->password_expires_at = now()->addDays($expiryDays);
        }

        $user->force_password_reset = false;
        $user->save();

        // Redirect to intended URL or home
        $intendedUrl = session('intended_url', url(AppServiceProvider::HOME));
        session()->forget('intended_url');

        return redirect($intendedUrl)
            ->with('success', 'Password berhasil diubah. Sekarang Anda dapat melanjutkan menggunakan aplikasi.');
    }
}
