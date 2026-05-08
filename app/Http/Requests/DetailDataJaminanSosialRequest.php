<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailDataJaminanSosialRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'judul' => 'nullable|string',
            'filter' => 'nullable|array',
            'filter.tipe' => 'nullable|string',
            'filter.nilai' => 'nullable|string',
        ];
    }
}