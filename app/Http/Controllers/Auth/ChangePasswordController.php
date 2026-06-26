<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Models\User as ModelsUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangePasswordController extends ResetPasswordController
{
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.change');
    }

    /**
     * Change user password.
     */
    public function change(ChangePasswordRequest $request)
    {
        $password = $request->get('password');
        $user = Auth::user();
        $this->changePassword($user, $password);

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.

        // return $this->sendResetResponse($request, 'password changed succesfull');
        Auth::logout();

        return redirect(route('login'))->with('success', 'Password berhasil diubah, silakan login kembali');
    }

    public function resetByAdmin(ModelsUser $user, Request $request)
    {
        $user->password = $user->email;
        $user->save();

        return $this->sendResetResponse($request, 'password user reset succesfull with new password '.$password);
    }

    /**
     * Reset the given user's password.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string                                      $password
     *
     * @return void
     */
    protected function changePassword($user, $password)
    {
        $expiryDays = config('password.expiry_days');
        $user->setPasswordWithHistory($password, 'password_change', $expiryDays);
    }
}
