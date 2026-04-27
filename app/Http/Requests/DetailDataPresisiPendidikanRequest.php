<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailDataPresisiPendidikanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [        
            'judul' => 'required|string|max:255',
            'filter' => 'array',
            'filter.tipe' => 'required_with:filter|string',
            'filter.nilai' => 'required_with:filter|string',        
        ];
    }
}
