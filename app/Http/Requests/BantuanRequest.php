<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BantuanRequest extends FormRequest
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
            'nama' => 'required|regex:/^[A-Za-z\.\']+(?:\s[A-Za-z\.\']+)*$/u|max:50',
            'sasaran' => 'required|integer',
            'ndesc' => 'required|string',
            'sdate' => 'required|date',
            'edate' => 'required|date',
            'asaldana' => 'required|string',
        ];
    }
}
