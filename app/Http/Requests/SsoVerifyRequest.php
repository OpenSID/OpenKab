<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SsoVerifyRequest extends FormRequest
{
    /**
     * Otentikasi callback ditangani oleh middleware SsoCallbackAuth
     * (server-to-server), bukan sesi web.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'callback_nonce' => ['required', 'string', 'min:8'],
        ];
    }
}
