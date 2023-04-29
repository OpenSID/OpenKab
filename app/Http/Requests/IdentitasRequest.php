<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IdentitasRequest extends FormRequest
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
            'nama_aplikasi'  => 'required|regex:/^[A-Za-z\.\']+(?:\s[A-Za-z\.\']+)*$/u|max:50',
            'deskripsi'      => 'nullable',
            'favicon'        => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png|max:1024',
            'nama_kabupaten' => 'required|string',
            'kode_kabupaten' => 'required|string',
            'nama_provinsi'  => 'required|string',
            'kode_provinsi'  => 'required|string',
            'sebutan_kab'    => 'required|regex:/^[A-Za-z\.\']+(?:\s[A-Za-z\.\']+)*$/u|max:50',
        ];
    }
}
