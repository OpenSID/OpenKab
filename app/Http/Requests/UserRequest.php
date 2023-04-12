<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            $id = "," . $this->user->id;
        } else {
            $id = "";
        }

        return [
            'name'       => 'required|regex:/^[A-Za-z\.\']+(?:\s[A-Za-z\.\']+)*$/u|max:50',
            'username'   => 'required|string|unique:users,username' . $id,
            'email'      => 'required|email|unique:users,email' . $id,
            'password'   => 'sometimes|min:8|max:32',
            'company'    => 'nullable|string',
            'phone'      => 'nullable|numeric|digits_between:10,13',
        ];
    }
}