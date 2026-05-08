<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request untuk validasi parameter detail data seni budaya
 *
 * Validasi input untuk halaman detail data statistik seni budaya
 * termasuk parameter filter dan judul
 */
class DetailDataPresisiSeniBudayaRequest extends FormRequest
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
            'judul' => 'nullable|string',
            'filter' => 'nullable|array',
            'filter.tipe' => 'nullable|string',
            'filter.nilai' => 'nullable|string',
        ];
    }
}
