<?php

namespace App\Http\Requests\Token;

use Illuminate\Foundation\Http\FormRequest;

class RevokeTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token_id' => 'required|integer|exists:personal_access_tokens,id',
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
            'token_id.required' => 'ID Token harus diisi',
            'token_id.integer' => 'ID Token harus berupa angka',
            'token_id.exists' => 'Token tidak ditemukan',
        ];
    }
}
