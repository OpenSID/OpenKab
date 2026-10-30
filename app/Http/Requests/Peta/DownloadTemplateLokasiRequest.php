<?php

namespace App\Http\Requests\Peta;

use Illuminate\Foundation\Http\FormRequest;

class DownloadTemplateLokasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'jenis' => ['required'],
            'kategori' => ['required'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jenis.required' => 'Jenis lokasi wajib dipilih.',
            'kategori.required' => 'Kategori lokasi wajib dipilih.',
        ];
    }
}
