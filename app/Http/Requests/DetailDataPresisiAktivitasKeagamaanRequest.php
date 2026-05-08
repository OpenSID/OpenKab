<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailDataPresisiAktivitasKeagamaanRequest extends FormRequest
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
            'judul' => 'nullable|string|max:255',
            'filter' => 'nullable|array',
            'filter.tipe' => 'required_with:filter|string|in:agama_id,frekwensi_mengikuti_kegiatan_setahun',
            'filter.nilai' => 'required_with:filter|string',
        ];
    }
}
