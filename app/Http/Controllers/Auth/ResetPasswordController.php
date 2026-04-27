<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\PasswordHistory;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Password;

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
    protected $redirectTo = RouteServiceProvider::HOME;

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

        // Save old password to history
        if ($user->password) {
            PasswordHistory::create([
                'user_id' => $user->id,
                'password' => $user->password,
                'reason' => 'password_reset',
            ]);
        }

        // Set new password
        $user->password = $password;

        // Set expiry if configured
        if ($expiryDays) {
            $user->password_expires_at = now()->addDays($expiryDays);
        }

        // Reset force_password_reset flag
        $user->force_password_reset = false;

        $user->save();
    }
}
