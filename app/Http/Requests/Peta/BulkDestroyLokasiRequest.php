<?php

namespace App\Http\Requests\Peta;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyLokasiRequest extends FormRequest
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
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ids.required' => 'Pilih minimal satu data lokasi.',
            'ids.array' => 'Format data lokasi terpilih tidak valid.',
            'ids.min' => 'Pilih minimal satu data lokasi.',
            'ids.*.integer' => 'Id lokasi tidak valid.',
        ];
    }
}
