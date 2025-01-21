<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncRemovePendudukRequest extends FormRequest
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
        return [
            'hapus_penduduk.*.id_pend_desa' => 'present|integer',
            'hapus_penduduk.*.foto' => 'nullable',
            'hapus_penduduk.*.desa_id' => 'present|string|exists:config,kode_desa',
        ];
    }
}
