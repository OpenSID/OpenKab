<?php

namespace App\Http\Controllers\Auth;

use App\Models\Base\User;
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
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            // 'email' => 'required|email',
            'password' => 'required|confirmed|min:8'
        ];
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());
        $password = $request->get('password');
        $user = Auth::user();
        $this->changePassword($user, $password);

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.

        return $this->sendResetResponse($request, 'password changed succesfull');
    }

    public function resetByAdmin(ModelsUser  $user ,Request $request)
    {
        $password = $user->email;
        $this->setUserPassword($user, $password);
        $user->save();

        return $this->sendResetResponse($request, 'password user reset succesfull with new password '. $password);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function changePassword($user, $password)
    {
        $this->setUserPassword($user, $password);
        $user->save();
    }

}
