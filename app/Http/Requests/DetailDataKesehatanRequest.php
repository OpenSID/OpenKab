<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailDataKesehatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul' => 'nullable|string',
            'filter' => 'nullable|array',
            'filter.tipe' => 'nullable|string',
            'filter.nilai' => 'nullable|string',
            'tipe' => 'nullable|string',
        ];
    }
}