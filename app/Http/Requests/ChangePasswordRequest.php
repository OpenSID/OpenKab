<?php

namespace App\Http\Requests;

use App\Rules\MatchOldPassword;
use App\Rules\StrongPassword;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password_old' => ['required', new MatchOldPassword],
            'password' => ['required', 'string', 'confirmed', new StrongPassword()],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password_old.required' => 'Password lama harus diisi.',
            'password.required' => 'Password baru harus diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'password_old' => 'password lama',
            'password' => 'password baru',
            'password_confirmation' => 'konfirmasi password',
        ];
    }
}
