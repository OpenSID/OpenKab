<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SsoDesaConfigRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $desaId = $this->route('sso_config')?->id;

        return [
            'desa_id' => [
                'required',
                'string',
                'regex:/^\d{10,13}$/',
                'unique:desa_sso_configs,desa_id,'.$desaId,
            ],
            'opensid_url' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $scheme = parse_url($value, PHP_URL_SCHEME);
                    $host = parse_url($value, PHP_URL_HOST);

                    if (! in_array($scheme, ['http', 'https'], true) || ! $host) {
                        $fail('URL OpenSID tidak valid.');

                        return;
                    }

                    if ($scheme === 'http' && ! app()->environment(['local', 'testing'])) {
                        $fail('URL OpenSID wajib menggunakan HTTPS.');
                    }
                },
            ],
            'enabled' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'enabled' => $this->boolean('enabled'),
        ]);
    }
}
