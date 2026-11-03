<?php

namespace App\Http\Requests\Peta;

use Illuminate\Foundation\Http\FormRequest;

class ImportLokasiRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
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
            'file.required' => 'File template wajib dipilih.',
            'file.mimes' => 'File harus berformat Excel (.xlsx / .xls).',
            'jenis.required' => 'Jenis lokasi wajib dipilih.',
            'kategori.required' => 'Kategori lokasi wajib dipilih.',
        ];
    }
}
