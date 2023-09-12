<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        if ($this->isMethod('put')) {
            $id = ','.$this->id;
        } else {
            $id = '';
        }
        $routeCurrent = Route::currentRouteName();
        if ($routeCurrent == 'profile.update') {
            return [
                'name' => 'required|regex:/^[A-Za-z\.\']+(?:\s[A-Za-z\.\']+)*$/u|max:50',
                'email' => 'required|email|unique:users,email'.$id,
                'company' => 'nullable|string',
                'phone' => 'nullable|numeric|digits_between:10,13',
                'foto' => 'nullable|image|max:1024|mimes:png,jpg',
            ];
        }

        return [
            'name' => 'required|regex:/^[A-Za-z\.\']+(?:\s[A-Za-z\.\']+)*$/u|max:50',
            'username' => 'required|string|unique:users,username'.$id,
            'email' => 'required|email|unique:users,email'.$id,
            'password' => 'sometimes|min:8|max:32',
            'company' => 'nullable|string',
            'phone' => 'nullable|numeric|digits_between:10,13',
            'group' => ' required|exists:App\Models\Team,id',
            'foto' => 'image|max:1024|mimes:png,jpg',
        ];
    }
}
