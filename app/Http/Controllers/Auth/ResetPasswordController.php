<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = AppServiceProvider::HOME;

    /**
     * Display the password reset view.
     */
    public function showResetForm(ResetPasswordRequest $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Reset the given user's password.
     */
    public function reset(ResetPasswordRequest $request)
    {
        $password = $request->get('password');
        $email = $request->get('email');
        $token = $request->get('token');

        // Find user by email
        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $this->resetPassword($user, $password);

        return redirect($this->redirectTo)
            ->with('success', 'Password berhasil direset. Silakan login dengan password baru.');
    }

    /**
     * Reset the given user's password.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string                                      $password
     *
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $expiryDays = config('password.expiry_days');

        $user->passwordHistoryReason = 'password_reset';
        $user->password = $password;

        if ($expiryDays) {
            $user->password_expires_at = now()->addDays($expiryDays);
        }

        $user->force_password_reset = false;
        $user->save();
    }
}
