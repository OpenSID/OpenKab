<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PindahRequest extends FormRequest
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
            'nik' => 'required|regex:/[0-9]{9}/u',
            'alamat_tujuan' => 'required|string',
            'ref_pindah' => 'required|integer',
            'config_asal' => 'required|integer',
            'config_tujuan' => 'required|integer',
            'status' => 'required|integer',
            'tgl_lapor' => 'require|date',
            'tgl_peristiwa' => 'require|date',
            'catatan' => 'sometimes|string',
        ];
    }
}
